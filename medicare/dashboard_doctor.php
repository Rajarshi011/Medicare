
<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
if ($_SESSION['user']['role']!=='doctor') { header("Location: index.php"); exit(); }

$doctor_id = $_SESSION['user']['id'];
// Check verified
$doc = $conn->query("SELECT * FROM doctors WHERE user_id={$doctor_id}")->fetch_assoc();
$verified = $doc && (int)$doc['verified'] === 1;
?>
<?php include 'includes/header.php'; ?>
<h2>Doctor Dashboard</h2>
<?php if(!$verified): ?>
  <div class="alert">Your profile is pending admin verification. You can view appointments but cannot approve until verified.</div>
<?php endif; ?>
<h3>Incoming Appointments</h3>
<table class="table">
  <tr><th>ID</th><th>Patient</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr>
  <?php
  $res = $conn->query("SELECT a.*, u.name AS patient_name FROM appointments a JOIN users u ON a.patient_id=u.id WHERE a.doctor_id={$doctor_id} ORDER BY a.id DESC");
  while($r = $res->fetch_assoc()): ?>
    <tr>
      <td><?php echo (int)$r['id']; ?></td>
      <td><?php echo htmlspecialchars($r['patient_name']); ?></td>
      <td><?php echo htmlspecialchars($r['date']); ?></td>
      <td><?php echo htmlspecialchars($r['time']); ?></td>
      <td><?php echo htmlspecialchars($r['status']); ?></td>
      <td>
        <?php if($verified): ?>
          <?php if($r['status']==='pending'): ?>
            <a class="btn sm" href="doctor_action.php?id=<?php echo (int)$r['id']; ?>&act=approve">Approve</a>
            <a class="btn sm outline" href="doctor_action.php?id=<?php echo (int)$r['id']; ?>&act=reject">Reject</a>
          <?php elseif($r['status']==='approved'): ?>
            <a class="btn sm" href="add_prescription.php?appointment_id=<?php echo (int)$r['id']; ?>">Add Prescription</a>
            <a class="btn sm outline" href="doctor_action.php?id=<?php echo (int)$r['id']; ?>&act=complete">Mark Completed</a>
          <?php endif; ?>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>
