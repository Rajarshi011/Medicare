
<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
if ($_SESSION['user']['role']!=='patient') { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user']['id'];
$res = $conn->query("SELECT p.*, u.name AS doctor_name FROM prescriptions p JOIN users u ON p.doctor_id=u.id WHERE p.patient_id={$user_id} ORDER BY p.id DESC");
?>
<?php include 'includes/header.php'; ?>
<h2>My Prescriptions</h2>
<table class="table">
  <tr><th>ID</th><th>Doctor</th><th>Issued At</th><th>Prescription</th></tr>
  <?php while($r = $res->fetch_assoc()): ?>
    <tr>
      <td><?php echo (int)$r['id']; ?></td>
      <td><?php echo htmlspecialchars($r['doctor_name']); ?></td>
      <td><?php echo htmlspecialchars($r['created_at']); ?></td>
      <td><pre class="pre"><?php echo htmlspecialchars($r['prescription_text']); ?></pre></td>
    </tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>
