
<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
if ($_SESSION['user']['role']!=='patient') { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user']['id'];

// Subscription status
$sub = $conn->prepare("SELECT us.*, s.plan_name FROM user_subscriptions us JOIN subscriptions s ON us.subscription_id=s.id WHERE us.user_id=? AND us.active=1 AND us.end_date>=CURDATE() ORDER BY us.id DESC LIMIT 1");
$sub->bind_param("i", $user_id);
$sub->execute();
$subRes = $sub->get_result();
$currentSub = $subRes->fetch_assoc();

// Appointments count
$stmt = $conn->prepare("SELECT COUNT(*) c FROM appointments WHERE patient_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$apCount = $stmt->get_result()->fetch_assoc()['c'];
?>
<?php include 'includes/header.php'; ?>
<h2>Patient Dashboard</h2>
<div class="grid">
  <div class="card">
    <h3>Subscription</h3>
    <?php if($currentSub): ?>
      <p><strong><?php echo htmlspecialchars($currentSub['plan_name']); ?></strong></p>
      <p>Valid till: <?php echo htmlspecialchars($currentSub['end_date']); ?></p>
      <p>Remaining consults: <?php echo is_null($currentSub['remaining_consultations']) ? 'Unlimited' : (int)$currentSub['remaining_consultations']; ?></p>
      <a class="btn" href="subscription.php">Manage</a>
    <?php else: ?>
      <p>No active subscription.</p>
      <a class="btn" href="subscription.php">View Plans</a>
    <?php endif; ?>
  </div>
  <div class="card">
    <h3>Appointments</h3>
    <p>Total booked: <?php echo (int)$apCount; ?></p>
    <a class="btn" href="book_appointment.php">Book New</a>
    <a class="btn outline" href="view_appointments.php">View</a>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
