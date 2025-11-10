
<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
if ($_SESSION['user']['role']!=='patient') { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user']['id'];
$res = $conn->query("SELECT a.*, u.name AS doctor_name FROM appointments a JOIN users u ON a.doctor_id=u.id WHERE a.patient_id={$user_id} ORDER BY a.id DESC");
?>
<?php include 'includes/header.php'; ?>
<h2>My Appointments</h2>
<table class="table">
  <tr><th>ID</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr>
  <?php while($r = $res->fetch_assoc()): ?>
    <tr>
      <td><?php echo (int)$r['id']; ?></td>
      <td><?php echo htmlspecialchars($r['doctor_name']); ?></td>
      <td><?php echo htmlspecialchars($r['date']); ?></td>
      <td><?php echo htmlspecialchars($r['time']); ?></td>
      <td><?php echo htmlspecialchars($r['status']); ?></td>
    </tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>
