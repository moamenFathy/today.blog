<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/config.php';
?>

<div class="navbar bg-base-100 shadow-lg sticky top-0 z-50">
  <div class="navbar-start">
  </div>
  <div class="navbar-center">
    <a class="btn btn-ghost text-xl" href="<?= asset('index.php') ?>">Today.Blog</a>
  </div>
  <div class="navbar-end gap-2">
    <?php if (!isset($_SESSION["first_name"])): ?>
      <a href="<?= asset('pages/login.php') ?>" class="btn btn-primary btn-sm">
        Login
      </a>
    <?php endif; ?>
    <?php if (isset($_SESSION['first_name'])): ?>
      <span class="flex items-center gap-3">
        <div class="avatar">
          <div class="w-12 rounded-full">
            <img src="<?= asset('uploads_users/' . $_SESSION['user_img']) ?>" />
          </div>
        </div>
        <span class="hidden lg:block">
          <?php echo $_SESSION['first_name'] . " " . $_SESSION['last_name'] ?>
        </span>
      </span>
    <?php endif; ?>
  </div>
</div>