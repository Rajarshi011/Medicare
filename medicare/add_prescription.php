
<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
if ($_SESSION['user']['role']!=='doctor') { header("Location: index.php"); exit(); }

$doctor_id = $_SESSION['user']['id'];
$appointment_id = (int)($_GET['appointment_id'] ?? 0);

// Validate appointment
$stmt = $conn->prepare("SELECT * FROM appointments WHERE id=? AND doctor_id=? AND status IN ('approved','completed')");
$stmt->bind_param("ii", $appointment_id, $doctor_id);
$stmt->execute();
$ap = $stmt->get_result()->fetch_assoc();
if(!$ap){ header("Location: dashboard_doctor.php"); exit(); }

$msg = "";
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $text = trim($_POST['prescription_text'] ?? '');
    if ($text) {
        $stmt2 = $conn->prepare("INSERT INTO prescriptions (appointment_id, doctor_id, patient_id, prescription_text) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("iiis", $appointment_id, $doctor_id, $ap['patient_id'], $text);
        if ($stmt2->execute()) {
            $msg = "Prescription saved.";
        } else {
            $msg = "Error: " . $conn->error;
        }
    } else {
        $msg = "Prescription text required.";
    }
}
?>
<?php include 'includes/header.php'; ?>
<h2>Add Prescription (Appointment #<?php echo (int)$appointment_id; ?>)</h2>
<?php if($msg): ?><div class="alert"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<form method="post" class="card">
  <label>Prescription Text
    <textarea name="prescription_text" rows="8" required placeholder="Diagnosis, medicines (name, dosage), advice, follow-up date..."></textarea>
  </label>
  <button class="btn" type="submit">Save</button>
</form>
<?php include 'includes/footer.php'; ?>
