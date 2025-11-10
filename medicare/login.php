
<?php
require_once 'includes/db.php';

session_start();

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND status='active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            if ($user['role']==='patient') header("Location: dashboard_patient.php");
            elseif ($user['role']==='doctor') header("Location: dashboard_doctor.php");
            else header("Location: admin_panel.php");
            exit();
        }
    }
    $msg = "Invalid credentials.";
    }
    

?>
<?php include 'includes/header.php'; ?>
<h2>Login</h2>
<?php if($msg): ?><div class="alert"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<form method="post" class="card">
  <label>Email
    <input type="email" name="email" required />
  </label>
  <label>Password
    <input type="password" name="password" required />
  </label>
  <button class="btn" type="submit">Login</button>
</form>
<div class="muted">Demo accounts:<br>Patient: raj@medicare.test / Test@123</div>
<?php include 'includes/footer.php'; ?>
