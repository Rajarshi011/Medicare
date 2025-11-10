<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

// Only patients can purchase subscriptions
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: index.php");
    exit();
}

$msg = "";

// Handle activation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $plan_id = (int)$_POST['plan_id'];
    $user_id = (int)$_SESSION['user']['id'];

    // Fetch plan details
    $planStmt = $conn->prepare("SELECT id, plan_name, price, duration_days, max_consultations FROM subscriptions WHERE id=?");
    $planStmt->bind_param("i", $plan_id);
    $planStmt->execute();
    $plan = $planStmt->get_result()->fetch_assoc();

    if ($plan) {
        // Deactivate any previous active subscriptions for this user
        $conn->query("UPDATE user_subscriptions SET active=0 WHERE user_id={$user_id} AND active=1");

        // Compute subscription window
        $start = date('Y-m-d');
        $end   = date('Y-m-d', strtotime($start . " + {$plan['duration_days']} days"));
        $remaining = is_null($plan['max_consultations']) ? null : (int)$plan['max_consultations'];

        // Insert the new subscription; choose bind types based on NULL handling
        if (is_null($remaining)) {
            $stmt = $conn->prepare("
                INSERT INTO user_subscriptions
                    (user_id, subscription_id, start_date, end_date, remaining_consultations, active)
                VALUES
                    (?, ?, ?, ?, NULL, 1)
            ");
            // 4 vars -> iiss
            $stmt->bind_param("iiss", $user_id, $plan_id, $start, $end);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO user_subscriptions
                    (user_id, subscription_id, start_date, end_date, remaining_consultations, active)
                VALUES
                    (?, ?, ?, ?, ?, 1)
            ");
            // 5 vars -> iissi
            $stmt->bind_param("iissi", $user_id, $plan_id, $start, $end, $remaining);
        }

        if ($stmt->execute()) {
            // Optional: redirect to avoid resubmission on refresh
            header("Location: subscription.php?ok=1");
            exit();
        } else {
            $msg = "Error activating subscription: " . htmlspecialchars($conn->error);
        }
    } else {
        $msg = "Invalid plan selected.";
    }
}

// Fetch plans for display
$plans = $conn->query("SELECT id, plan_name, price, duration_days, max_consultations FROM subscriptions ORDER BY price ASC");

// Success flash via query param
if (isset($_GET['ok'])) {
    $msg = "Subscription activated!";
}
?>
<?php include 'includes/header.php'; ?>

<h2>Subscriptions</h2>
<?php if ($msg): ?>
  <div class="alert"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="grid">
  <?php while ($p = $plans->fetch_assoc()): ?>
    <div class="card">
      <h3><?php echo htmlspecialchars($p['plan_name']); ?></h3>
      <p>₹<?php echo htmlspecialchars($p['price']); ?> / <?php echo (int)$p['duration_days']; ?> days</p>
      <p>Consultations: <?php echo is_null($p['max_consultations']) ? 'Unlimited' : (int)$p['max_consultations']; ?></p>
      <form method="post">
        <input type="hidden" name="plan_id" value="<?php echo (int)$p['id']; ?>">
        <button class="btn" type="submit">Activate</button>
      </form>
    </div>
  <?php endwhile; ?>
</div>

<?php include 'includes/footer.php'; ?>
