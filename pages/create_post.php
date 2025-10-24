<?php
require "../includes/connection.php";
include "../includes/queries.php";
include "../includes/helpers.php";
include "../includes/navbar.php";

// Require login
if (!isset($_SESSION['first_name'])) {
  header("Location: login.php");
  exit();
}

$conn = connectDb();

$error = "";
$success = "";
$hasDislikes = false;
$colRes = @mysqli_query($conn, "SHOW COLUMNS FROM posts LIKE 'dislikes'");
if ($colRes && mysqli_num_rows($colRes) > 0) {
  $hasDislikes = true;
}

if (isset($_POST['create_post'])) {
  $title = trim($_POST['title'] ?? "");
  $content = trim($_POST['content'] ?? "");
  $user_id = $_SESSION['user_id'];
  $imgName = "";

  // Basic validation
  if ($title === '' || strlen($title) < 3) {
    $error = "Title must be at least 3 characters.";
  } elseif ($content === '' || strlen($content) < 10) {
    $error = "Content must be at least 10 characters.";
  }

  // Image (optional)
  if (!$error && isset($_FILES['img']) && $_FILES['img']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
      $fileName = $_FILES['img']['name'];
      if (!checkIsImg($fileName)) {
        $error = "Please upload a valid image (jpg, jpeg, png, gif, webp, avif).";
      } else {
        $uploadDir = "../uploads_posts/";
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid('post_', true) . "." . $ext;
        $target = $uploadDir . $newFileName;
        if (move_uploaded_file($_FILES['img']['tmp_name'], $target)) {
          $imgName = $newFileName;
        } else {
          $error = "Failed to upload image.";
        }
      }
    } else {
      $error = "Upload error. Please try again.";
    }
  }

  if (!$error) {
    $now = date('Y-m-d H:i:s');
    $postData = [
      'user_id' => $user_id,
      'title' => $title,
      'content' => $content,
      'img' => $imgName,
      'likes' => 0,
      'post_release' => $now,
    ];
    if ($hasDislikes) {
      $postData['dislikes'] = 0;
    }

    $newId = insertIntoTable($conn, 'posts', $postData);
    if ($newId) {
      $success = "Post created successfully!";
      // Redirect to read page
      header("Location: read_more.php?id=" . $newId);
      exit();
    } else {
      $error = "Could not create post.";
    }
  }
}
?>

<!DOCTYPE html>
<html data-theme="forest" lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Post - Today.Blog</title>
  <link rel="stylesheet" href="../public/output.css?v=<?php echo filemtime('../public/output.css'); ?>" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    .image-preview {
      max-height: 320px;
    }
  </style>
</head>

<body>
  <section class="container mx-auto max-w-4xl px-4 py-10">
    <h1 class="text-4xl font-bold mb-6">Create a new post</h1>

    <?php if ($error): ?>
      <div class="alert alert-error mb-6">
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <div class="card bg-base-100 shadow-xl">
      <div class="card-body p-8 rounded-3xl">
        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-6">
          <div>
            <label class="label"><span class="label-text">Title</span></label>
            <input type="text" name="title" class="input input-bordered w-full" placeholder="Enter a catchy title" required minlength="3" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" />
          </div>

          <div>
            <label class="label"><span class="label-text">Content</span></label>
            <textarea name="content" rows="8" class="textarea textarea-bordered w-full" placeholder="Write your story..." required minlength="10"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
            <div class="label">
              <span class="label-text-alt">Supports line breaks. No HTML needed.</span>
            </div>
          </div>

          <div>
            <label class="label"><span class="label-text">Cover Image</span></label>
            <input type="file" name="img" id="img" accept="image/*" class="file-input file-input-bordered w-full" required />
            <div class="mt-4 hidden" id="preview-wrap">
              <img id="preview" class="rounded-2xl image-preview" alt="Preview" />
            </div>
          </div>

          <div class="flex items-center justify-end gap-3">
            <a href="../index.php" class="btn btn-ghost">Cancel</a>
            <button type="submit" name="create_post" class="btn btn-primary">Publish</button>
          </div>
        </form>
      </div>
    </div>
  </section>

  <?php include "../includes/footer.php"; ?>

  <script>
    // Image preview
    const input = document.getElementById('img');
    const preview = document.getElementById('preview');
    const wrap = document.getElementById('preview-wrap');
    if (input) {
      input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) {
          wrap.classList.add('hidden');
          return;
        }
        const url = URL.createObjectURL(file);
        preview.src = url;
        wrap.classList.remove('hidden');
      });
    }
  </script>
</body>

</html>