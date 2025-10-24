<!DOCTYPE html>
<html data-theme="forest" lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Today.Blog</title>
  <link rel="stylesheet" href="./public/output.css?v=<?php echo filemtime('./public/output.css'); ?>">
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-..."
    crossorigin="anonymous"
    referrerpolicy="no-referrer" />
</head>

<body>
  <?php
  include "./includes/config.php";
  include "./includes/navbar.php";
  include "./includes/connection.php";
  include "./includes/queries.php";

  $conn = connectDb();

  $query = "
    SELECT 
      posts.*, 
      users.first_name, 
      users.user_img AS user_img
    FROM posts
    INNER JOIN users ON posts.user_id = users.id
    ORDER BY post_release DESC
  ";

  $result = mysqli_query($conn, $query);
  $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
  // Stats for hero
  $postsCount = is_array($posts) ? count($posts) : 0;
  $authorsRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users"));
  $authorsCount = (int)($authorsRow['c'] ?? 0);
  ?>

  <!-- === Hero Section start === -->
  <section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-primary/15 via-base-200 to-base-200"></div>
    <div class="absolute -top-24 -left-24 h-72 w-72 bg-primary/20 blur-3xl rounded-full"></div>
    <div class="absolute -bottom-24 -right-24 h-72 w-72 bg-secondary/20 blur-3xl rounded-full"></div>

    <div class="hero min-h-[70vh]">
      <div class="hero-content text-center">
        <div class="max-w-3xl">
          <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-base-100/60 px-3 py-1 shadow-sm">
            <span class="badge badge-primary badge-sm"></span>
            <span class="text-sm opacity-80">Share your thoughts with the world</span>
          </div>
          <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight">
            <?php if (isset($_SESSION['first_name'])): ?>
              Welcome back, <span class="text-primary"><?= htmlspecialchars($_SESSION['first_name']) ?></span>
            <?php else: ?>
              Your daily dose of <span class="text-primary">stories</span> and ideas
            <?php endif; ?>
          </h1>
          <p class="mt-4 text-base md:text-lg opacity-80">
            Discover fresh posts from our community of creators, or start writing your own today.
          </p>

          <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
            <?php if (isset($_SESSION['first_name'])): ?>
              <a href="<?= asset('pages/create_post.php') ?>" class="btn btn-primary btn-wide">Start writing</a>
            <?php else: ?>
              <a href="<?= asset('pages/register.php') ?>" class="btn btn-primary btn-wide">Get started</a>
            <?php endif; ?>
            <a href="#discover" class="btn btn-ghost">Explore posts</a>
          </div>

          <div class="mt-6 flex items-center justify-center gap-6 text-sm">
            <span class="opacity-80"><strong class="text-primary"><?= $postsCount ?></strong> posts</span>
            <span class="opacity-80"><strong class="text-secondary"><?= $authorsCount ?></strong> authors</span>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- === Hero Section End === -->

  <h1 id="discover" class="text-center text-5xl font-bold mt-6 text-green-600">Discover The New</h1>
  <div class="grid justify-items-center sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-6 my-8">
    <?php foreach ($posts as $post): ?>
      <div class="card bg-base-100 w-80 lg:w-96 shadow-sm">
        <figure>
          <img
            src="<?= asset('uploads_posts/' . $post['img']) ?>"
            alt="Shoes" />
        </figure>
        <div class="card-body flex justify-between rounded-b-xl">
          <div class="flex gap-4">
            <?php
            $authorImg = (!empty($post['user_img']) && file_exists("./uploads_users/" . $post['user_img']))
              ? ("./uploads_users/" . $post['user_img'])
              : "./uploads_users/default.jpg";
            ?>
            <div class="avatar">
              <div class="w-12 rounded-full">
                <img src="<?php echo $authorImg; ?>" alt="Author avatar" />
              </div>
            </div>
            <h2 class="card-title"><?php echo $post['title'] ?></h2>
          </div>
          <pre class="line-clamp-3"><?php echo $post['content'] ?></pre>
          <div class="card-actions justify-between items-center">
            <div>
              <span><?php echo $post['first_name'] ?></span> • <?php echo $post['likes'] ?> <i class="fa-regular fa-heart"></i> • <?php echo date("d/m/y", strtotime($post['post_release'])) ?>
            </div>
            <button class="btn btn-primary"><a href="<?= isset($_SESSION['first_name']) ? asset('pages/read_more.php?id=' . $post['id']) : asset('pages/login.php') ?>">Read More</a></button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="fab fab-flower">
    <!-- a focusable div with tabindex is necessary to work on all browsers. role="button" is necessary for accessibility -->
    <div tabindex="0" role="button" class="btn btn-soft btn-primary btn-circle btn-lg fixed right-4">
      <svg
        aria-label="New"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 16 16"
        fill="currentColor"
        class="size-6">
        <path
          d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
      </svg>
    </div>

    <!-- Main Action button replaces the original button when FAB is open -->
    <button class="fab-main-action btn btn-circle btn-lg btn-primary">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <!-- buttons that show up when FAB is open -->
    <button class="btn btn-circle btn-lg">
  <a href="<?= asset('pages/create_post.php') ?>">
        <svg
          aria-label="New post"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 16 16"
          fill="currentColor"
          class="size-6">
          <path
            fill-rule="evenodd"
            d="M11.013 2.513a1.75 1.75 0 0 1 2.475 2.474L6.226 12.25a2.751 2.751 0 0 1-.892.596l-2.047.848a.75.75 0 0 1-.98-.98l.848-2.047a2.75 2.75 0 0 1 .596-.892l7.262-7.261Z"
            clip-rule="evenodd" />
        </svg>
      </a>
    </button>

    <button class="btn btn-circle btn-lg">
  <a href="<?= asset('pages/logout.php') ?>">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
      </a>
    </button>
  </div>

  <?php
  include "./includes/footer.php";
  ?>
</body>

</html>