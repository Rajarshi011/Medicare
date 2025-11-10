
<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
require_role('admin');

$msg = "";

// Verify doctor
if (isset($_GET['verify'])) {
    $uid = (int)$_GET['verify'];
    $conn->query("UPDATE doctors SET verified=1 WHERE user_id={$uid}");
    $msg = "Doctor verified.";
}

// Add new plan
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['plan_name'])) {
    $name = trim($_POST['plan_name']);
    $price = (float)$_POST['price'];
    $duration = (int)$_POST['duration_days'];
    $maxc = $_POST['max_consultations'] === '' ? NULL : (int)$_POST['max_consultations'];

    if ($name && $duration > 0) {
        if (is_null($maxc)) {
            $stmt = $conn->prepare("INSERT INTO subscriptions (plan_name, price, duration_days, max_consultations) VALUES (?, ?, ?, NULL)");
            $stmt->bind_param("sdi", $name, $price, $duration);
        } else {
            $stmt = $conn->prepare("INSERT INTO subscriptions (plan_name, price, duration_days, max_consultations) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdii", $name, $price, $duration, $maxc);
        }
        $stmt->execute();
        $msg = "Plan added.";
    } else {
        $msg = "Invalid plan data.";
    }
}

$pendingDocs = $conn->query("SELECT u.id, u.name, u.email, d.specialization, d.license_no FROM users u JOIN doctors d ON u.id=d.user_id WHERE d.verified=0 ORDER BY u.id DESC");
$plans = $conn->query("SELECT * FROM subscriptions ORDER BY id DESC");
?>
<?php include 'includes/header.php'; ?>
<h2>Admin Panel</h2>
<?php if($msg): ?><div class="alert"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

<h3>Pending Doctor Verifications</h3>
<table class="table">
  <tr><th>Name</th><th>Email</th><th>Specialization</th><th>License</th><th>Action</th></tr>
  <?php while($d = $pendingDocs->fetch_assoc()): ?>
    <tr>
      <td><?php echo htmlspecialchars($d['name']); ?></td>
      <td><?php echo htmlspecialchars($d['email']); ?></td>
      <td><?php echo htmlspecialchars($d['specialization']); ?></td>
      <td><?php echo htmlspecialchars($d['license_no']); ?></td>
      <td><a class="btn sm" href="?verify=<?php echo (int)$d['id']; ?>">Verify</a></td>
    </tr>
  <?php endwhile; ?>
</table>

<h3>Create Subscription Plan</h3>
<form method="post" class="card">
  <label>Plan Name
    <input type="text" name="plan_name" required />
  </label>
  <label>Price (₹)
    <input type="number" step="0.01" name="price" value="0" required />
  </label>
  <label>Duration (days)
    <input type="number" name="duration_days" value="30" required />
  </label>
  <label>Max Consultations (empty for unlimited)
    <input type="number" name="max_consultations" />
  </label>
  <button class="btn" type="submit">Add Plan</button>
</form>

<h3>Existing Plans</h3>
<table class="table">
  <tr><th>ID</th><th>Name</th><th>Price</th><th>Duration</th><th>Max Consults</th></tr>
  <?php while($p = $plans->fetch_assoc()): ?>
    <tr>
      <td><?php echo (int)$p['id']; ?></td>
      <td><?php echo htmlspecialchars($p['plan_name']); ?></td>
      <td>₹<?php echo htmlspecialchars($p['price']); ?></td>
      <td><?php echo (int)$p['duration_days']; ?></td>
      <td><?php echo is_null($p['max_consultations']) ? 'Unlimited' : (int)$p['max_consultations']; ?></td>
    </tr>
  <?php endwhile; ?>
</table>

<?php include 'includes/footer.php'; ?>
