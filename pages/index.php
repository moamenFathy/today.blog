<!DOCTYPE html>
<html data-theme="forest" lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Today.Blog</title>
  <link rel="stylesheet" href="../public/output.css?v=<?php echo filemtime('../public/output.css'); ?>">
</head>

<body>
<?php
include "../includes/navbar.php";
include "../includes/connection.php";
include "../includes/queries.php";


$conn = connectDb();

$posts = selectFromTable($conn, "posts");

?>

<!-- === Hero Section start === -->
<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content text-center">
    <div class="max-w-md">
      <?php if (isset($_SESSION['first_name'])): ?>
        <h1 class="text-5xl font-bold">Hello <?= $_SESSION['first_name'] ?></h1>
        <?php else: ?>
          <h1 class="text-5xl font-bold">Hello There</h1>
      <?php endif ?>
        <p class="py-6">
        Provident cupiditate voluptatem et in. Quaerat fugiat ut assumenda excepturi exercitationem
        quasi. In deleniti eaque aut repudiandae et a id nisi.
      </p>
      <button class="btn btn-primary">
        <a href="./register.php">
          Get Started
        </a>
      </button>
    </div>
  </div>
</div>
<!-- === Hero Section start === -->
 <div class="flex gap-5">
   <?php foreach ($posts as $post): ?>
    <div class="card">
      <h1><?= $post['title'] ?></h1>
      <div class="card-body">
        <?= $post['content'] ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php
include "../includes/footer.php";
?>

</body>
</html>