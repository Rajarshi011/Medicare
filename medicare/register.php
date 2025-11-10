
<?php
require_once 'includes/db.php';
session_start();

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'patient';
    $specialization = trim($_POST['specialization'] ?? '');
    $license_no = trim($_POST['license_no'] ?? '');

    if(!$name || !$email || !$password) {
        $msg = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param("ssss", $name, $email, $hash, $role);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            if ($role === 'doctor') {
                $verified = 0;
                $stmt2 = $conn->prepare("INSERT INTO doctors (user_id, specialization, license_no, verified) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("issi", $user_id, $specialization, $license_no, $verified);
                $stmt2->execute();
            }
            $msg = "Registration successful! You can now login.";
        } else {
            $msg = "Error: " . $conn->error;
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<h2>Register</h2>
<?php if($msg): ?><div class="alert"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<form method="post" class="card">
  <label>Name
    <input type="text" name="name" required />
  </label>
  <label>Email
    <input type="email" name="email" required />
  </label>
  <label>Password
    <input type="password" name="password" required />
  </label>
  <label>Role
    <select name="role" id="roleSelect" onchange="toggleDoctorFields()">
      <option value="patient">Patient</option>
      <option value="doctor">Doctor</option>
    </select>
  </label>
  <div id="doctorFields" style="display:none;">
    <label>Specialization
      <input type="text" name="specialization" />
    </label>
    <label>License No
      <input type="text" name="license_no" />
    </label>
    <small>Admin will verify your profile before you can consult.</small>
  </div>
  <button class="btn" type="submit">Register</button>
</form>
<script>
function toggleDoctorFields(){
  const role = document.getElementById('roleSelect').value;
  document.getElementById('doctorFields').style.display = role==='doctor' ? 'block':'none';
}
</script>
<?php include 'includes/footer.php'; ?>
