<!DOCTYPE html>
<html data-theme="forest" lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Today Blog</title>
  <link rel="stylesheet" href="../public/output.css?v=<?php echo filemtime('../public/output.css'); ?>">
</head>
<body>
<?php
require "../includes/connection.php";
require "../includes/queries.php";
include "../includes/navbar.php";

$conn = connectDb();


if (isset($_SESSION["first_name"])) {
  header("Location: index.php");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $users = selectFromTable($conn, "users", "email", "'$email'");

    if (!empty($users)) {
      $user = $users[0]; // Get first user from array
      
      if (password_verify($password, $user['password'])) {
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['email'] = $user['email'];
        header("Location: index.php");
        exit();
      } else {
        $error = "Wrong password";
      }
    } else {
      $error = "User not found";
    }
  }
}
?>

<!-- === Hero Section === -->
<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content text-center">
    <div class="max-w-md w-full">
      <h1 class="text-5xl font-bold mb-4">Welcome Back</h1>
      <p class="mb-8">
        Login to your account and continue your blogging journey with Today.Blog.
      </p>
      
      <!-- Login Card -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-8">
          
          <?php if (isset($error)): ?>
            <div class="alert alert-error mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span><?php echo $error; ?></span>
            </div>
          <?php endif; ?>
          
          <form method="POST">
            
            <!-- Email -->
            <div class="flex flex-col gap-1">
              <label class="label">
                <span class="label-text">Email:</span>
              </label>
              <input 
                type="email" 
                name="email" 
                placeholder="email@example.com" 
                class="input input-bordered mx-auto" 
                required 
              />
            </div>
            
            <!-- Password -->
            <div class="flex flex-col gap-1 mt-4">
              <label class="label">
                <span class="label-text">Password:</span>
              </label>
              <input 
                type="password" 
                name="password" 
                placeholder="Enter password" 
                class="input input-bordered mx-auto" 
                required 
              />
              <label class="label">
                <span class="label-text-alt mt-3">
                  <a href="#" class="link link-hover">Forgot password?</a>
                </span>
              </label>
            </div>
            
            <!-- Submit Button -->
            <div class="form-control mt-6">
              <button type="submit" name="login" class="btn btn-primary">
                Login
              </button>
            </div>
          </form>
          
          <div class="divider">OR</div>
          
          <p class="text-center text-sm">
            Don't have an account? 
            <a href="register.php" class="link link-primary">Register here</a>
          </p>
        </div>
      </div>
      
    </div>
  </div>
</div>

</body>
</html>