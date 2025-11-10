
<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Medicare XAMPP</title>
  <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
<header class="topbar">
  <div class="container">
    <h1>Medicare</h1>
    <nav>
      <a href="index.php">Home</a>
      <?php if(isset($_SESSION['user'])): ?>
        <?php if($_SESSION['user']['role']==='patient'): ?>
          <a href="dashboard_patient.php">Patient Dashboard</a>
          <a href="subscription.php">Subscriptions</a>
          <a href="book_appointment.php">Book</a>
          <a href="view_appointments.php">Appointments</a>
          <a href="prescriptions.php">Prescriptions</a>
        <?php elseif($_SESSION['user']['role']==='doctor'): ?>
          <a href="dashboard_doctor.php">Doctor Dashboard</a>
        <?php elseif($_SESSION['user']['role']==='admin'): ?>
          <a href="admin_panel.php">Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
