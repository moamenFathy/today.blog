<!DOCTYPE html>
<html data-theme="forest" lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Today Blog</title>
  <link rel="stylesheet" href="../public/output.css?v=<?php echo filemtime('../public/output.css'); ?>">
</head>

<body>
  <?php
  require "../includes/connection.php";
  include "../includes/navbar.php";
  include "../includes/queries.php";
  include "../includes/helpers.php";

  if (isset($_SESSION['first_name'])) {
    header("Location: index.php");
    exit();
  }

  $conn = connectDb();
  $error = "";

  if (isset($_POST['register'])) {
    $userImg = "";

    if (isset($_FILES['user_img']) && $_FILES['user_img']['error'] === UPLOAD_ERR_OK) {
      $fileName = $_FILES['user_img']['name'];

      if (!checkIsImg($fileName)) {
        $error = "Please upload a valid image file (jpg, jpeg, png, gif, webp, avif)";
      } else {
        $uploadDir = "../uploads_users/";
        if (!file_exists($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }

        $newFileName = uniqid() . "_" . basename($fileName);
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['user_img']['tmp_name'], $targetPath)) {
          $userImg = $newFileName;
        } else {
          $error = "Failed to upload image";
        }
      }
    }
    if (empty($error)) {
      if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Password do not match";
      } else {

        $user = [
          "first_name" => $_POST['first_name'],
          "last_name" => $_POST['last_name'],
          "email" => $_POST['email'],
          "password" => password_hash($_POST['password'], PASSWORD_DEFAULT),
          "user_img" => $userImg,
          "followers" => 0,
          "posts_number" => 0
        ];

        $userId = insertIntoTable($conn, "users", $user);

        if ($userId) {
          header("Location: login.php");
          exit();
        }
      }
    }
  }


  // echo checkIsImg("alskdfj.alskdfjlkasdfj.aklsdfjlaskdjf.aka");
  ?>

  <!-- === Hero Section === -->
  <div class="hero bg-base-200 min-h-screen">
    <div class="hero-content text-center">
      <div class="max-w-md w-full">
        <h1 class="text-5xl font-bold mb-4">Create Account</h1>
        <p class="mb-8">
          Join our blogging community today. Share your stories, connect with readers,
          and start your journey with Today.Blog.
        </p>

        <!-- Registration Card -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body p-8 drop-shadow-2xl">
            <form method="POST" action="" enctype="multipart/form-data">

              <!-- First Name -->
              <div class="flex flex-col gap-1">
                <label class="label">
                  <span class="label-text">First Name:</span>
                </label>
                <input
                  type="text"
                  name="first_name"
                  placeholder="John"
                  class="input input-bordered mx-auto"
                  required />
              </div>

              <!-- Last Name -->
              <div class="flex flex-col gap-1 mt-4">
                <label class="label">
                  <span class="label-text">Last Name:</span>
                </label>
                <input
                  type="text"
                  name="last_name"
                  placeholder="Doe"
                  class="input input-bordered mx-auto"
                  required />
              </div>

              <!-- Email -->
              <div class="flex flex-col gap-1 mt-4">
                <label class="label">
                  <span class="label-text">Email:</span>
                </label>
                <input
                  type="email"
                  name="email"
                  placeholder="email@example.com"
                  class="input input-bordered mx-auto"
                  required />
              </div>

              <!-- Image Input -->
              <fieldset class="fieldset mt-4">
                <label class="label">
                  <span class="label-text">Pick profile image:</span>
                </label>
                <input type="file" name="user_img" class="file-input mx-auto" />
              </fieldset>

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
                  minlength="8" />
              </div>

              <!-- Confirm Password -->
              <div class="flex flex-col gap-1 mt-4">
                <label class="label">
                  <span class="label-text">Confirm Password:</span>
                </label>
                <input
                  type="password"
                  name="confirm_password"
                  placeholder="Confirm password"
                  class="input input-bordered mx-auto"
                  required
                  minlength="8" />
              </div>

              <?php if ($error): ?>
                <div role="alert" class="alert alert-error mt-6">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span><?php echo $error ?></span>
                </div>
              <?php endif; ?>

              <!-- Submit Button -->
              <div class="form-control mt-6">
                <button type="submit" name="register" class="btn btn-primary">
                  Create Account
                </button>
              </div>
            </form>

            <div class="divider">OR</div>

            <p class="text-center text-sm">
              Already have an account?
              <a href="login.php" class="link link-primary">Login here</a>
            </p>
          </div>
        </div>

      </div>
    </div>
  </div>

</body>

</html>