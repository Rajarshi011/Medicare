
<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
if ($_SESSION['user']['role']!=='patient') { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user']['id'];
$msg = "";

// Check active subscription
$sub = $conn->prepare("SELECT * FROM user_subscriptions WHERE user_id=? AND active=1 AND end_date>=CURDATE() ORDER BY id DESC LIMIT 1");
$sub->bind_param("i", $user_id);
$sub->execute();
$activeSub = $sub->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $doctor_id = (int)($_POST['doctor_id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';

    if (!$activeSub) {
        $msg = "You need an active subscription to book.";
    } else {
        // Check remaining consultations if limited
        if (!is_null($activeSub['remaining_consultations']) && $activeSub['remaining_consultations'] <= 0) {
            $msg = "No remaining consultations. Please upgrade/renew.";
        } else {
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, date, time) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $user_id, $doctor_id, $date, $time);
            if ($stmt->execute()) {
                // Decrease remaining if not unlimited
                if (!is_null($activeSub['remaining_consultations'])) {
                    $conn->query("UPDATE user_subscriptions SET remaining_consultations = remaining_consultations - 1 WHERE id={$activeSub['id']}");
                }
                $msg = "Appointment requested. Waiting for doctor approval.";
            } else {
                $msg = "Error: " . $conn->error;
            }
        }
    }
}

// Doctors (verified only)
$docs = $conn->query("SELECT u.id, u.name, d.specialization FROM users u JOIN doctors d ON u.id=d.user_id WHERE u.role='doctor' AND d.verified=1 ORDER BY u.name");
?>
<?php include 'includes/header.php'; ?>
<h2>Book Appointment</h2>
<?php if($msg): ?><div class="alert"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<?php if(!$activeSub): ?>
  <div class="alert">No active subscription. <a href="subscription.php">Get one</a>.</div>
<?php endif; ?>
<form method="post" class="card">
  <label>Doctor
    <select name="doctor_id" required>
      <option value="">Select doctor</option>
      <?php while($d = $docs->fetch_assoc()): ?>
        <option value="<?php echo (int)$d['id']; ?>">
          <?php echo htmlspecialchars($d['name']." (".$d['specialization'].")"); ?>
        </option>
      <?php endwhile; ?>
    </select>
  </label>
  <label>Date
    <input type="date" name="date" required />
  </label>
  <label>Time
    <input type="time" name="time" required />
  </label>
  <button class="btn" type="submit">Book</button>
</form>
<?php include 'includes/footer.php'; ?>
