
<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
if ($_SESSION['user']['role']!=='doctor') { header("Location: index.php"); exit(); }

$doctor_id = $_SESSION['user']['id'];
$id = (int)($_GET['id'] ?? 0);
$act = $_GET['act'] ?? '';

// Verify ownership
$stmt = $conn->prepare("SELECT * FROM appointments WHERE id=? AND doctor_id=?");
$stmt->bind_param("ii", $id, $doctor_id);
$stmt->execute();
$ap = $stmt->get_result()->fetch_assoc();
if(!$ap){ header("Location: dashboard_doctor.php"); exit(); }

$status = null;
if ($act==='approve') $status='approved';
elseif ($act==='reject') $status='rejected';
elseif ($act==='complete') $status='completed';

if ($status) {
  $stmt2 = $conn->prepare("UPDATE appointments SET status=? WHERE id=?");
  $stmt2->bind_param("si", $status, $id);
  $stmt2->execute();
}
header("Location: dashboard_doctor.php"); exit();
