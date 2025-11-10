
<?php include 'includes/header.php'; ?>
<section class="hero">
  <h2>Medical Consultation + Subscription </h2>
  <p>Simple, XAMPP-ready platform for patients and doctors.</p>
  <?php if(!isset($_SESSION['user'])): ?>
  <div class="cta">
    <a class="btn" href="register.php">Create Account</a>
    <a class="btn outline" href="login.php">Login</a>
  </div>
  <?php else: ?>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</p>
  <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>
