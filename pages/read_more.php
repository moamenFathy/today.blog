<?php
require "../includes/connection.php";
require_once "../includes/config.php";
include "../includes/queries.php";

$conn = connectDb();

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Get post with author information
$query = "
  SELECT 
    posts.*, 
    users.first_name, 
    users.last_name,
    users.user_img
  FROM posts
  INNER JOIN users ON posts.user_id = users.id
  WHERE posts.id = '$id'
";

$result = mysqli_query($conn, $query);
$post = mysqli_fetch_assoc($result);

if (!$post) {
  header("Location: index.php");
  exit();
}

// Determine current user's reaction (if logged in)
$isLoggedIn = isset($_SESSION['user_id']);
$currentReaction = null;
if ($isLoggedIn) {
  $uid = (int)$_SESSION['user_id'];
  // Ensure table may exist; safe select even if missing
  $check = @mysqli_query($conn, "SHOW TABLES LIKE 'post_reactions'");
  if ($check && mysqli_num_rows($check) > 0) {
    $rres = mysqli_query($conn, "SELECT reaction FROM post_reactions WHERE post_id=" . (int)$post['id'] . " AND user_id=" . $uid . " LIMIT 1");
    if ($rres && mysqli_num_rows($rres)) {
      $row = mysqli_fetch_assoc($rres);
      $currentReaction = $row['reaction'];
    }
  }
}
?>

<!DOCTYPE html>
<html data-theme="forest" lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($post['title']) ?> - Today.Blog</title>
  <link rel="stylesheet" href="../public/output.css?v=<?php echo filemtime('../public/output.css'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
  <?php include "../includes/navbar.php"; ?>

  <!-- Article Container -->
  <article class="container mx-auto max-w-4xl px-4 py-8">

    <!-- Title -->
    <h1 class="text-4xl md:text-5xl font-bold text-center mb-6 leading-tight">
      <?php echo htmlspecialchars($post['title']) ?>
    </h1>

    <!-- Post Meta Information -->
    <div class="flex items-center justify-center gap-4 mb-8 text-base-content/70">
      <span class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <?php echo date("F j, Y", strtotime($post['post_release'])) ?>
      </span>
      <span class="flex items-center gap-2">
        <i class="fa-regular fa-heart"></i>
        <?php echo $post['likes'] ?> likes
      </span>
    </div>

    <!-- Featured Image -->
    <figure class="mb-8 rounded-2xl overflow-hidden shadow-xl">
      <img
        class="w-full h-auto object-cover"
        src="<?php echo "../uploads_posts/" . $post['img'] ?>"
        alt="<?php echo htmlspecialchars($post['title']) ?>">
    </figure>

    <!-- Content -->
    <div class="prose prose-lg max-w-none">
      <div class="text-base-content leading-relaxed text-lg break-words whitespace-normal">
        <?php echo nl2br($post['content']) ?>
      </div>
    </div>

    <!-- Like / Dislike Section (per-user, server-enforced) -->
    <div class="card bg-base-200 shadow-lg mt-8">
      <div class="card-body">
        <h3 class="card-title text-xl mb-4 justify-center">Did you enjoy this post?</h3>
        <div class="flex items-center justify-center gap-8">
          <!-- Like button -->
          <?php $likeActive = ($currentReaction === 'like'); ?>
          <button id="like-btn" type="button" class="btn <?php echo $likeActive ? 'btn-success' : 'btn-outline btn-success'; ?> btn-lg gap-3" aria-label="Like this post">
            <i class="fa-solid fa-thumbs-up text-2xl"></i>
            <div class="flex flex-col items-start">
              <span class="text-sm opacity-70">Like</span>
              <span id="like-count" class="text-xl font-bold"><?php echo (int)($post['likes'] ?? 0); ?></span>
            </div>
          </button>

          <!-- Dislike button -->
          <?php $dislikeActive = ($currentReaction === 'dislike'); ?>
          <button id="dislike-btn" type="button" class="btn <?php echo $dislikeActive ? 'btn-error' : 'btn-outline btn-error'; ?> btn-lg gap-3" aria-label="Dislike this post">
            <i class="fa-solid fa-thumbs-down text-2xl"></i>
            <div class="flex flex-col items-start">
              <span class="text-sm opacity-70">Dislike</span>
              <span id="dislike-count" class="text-xl font-bold"><?php echo isset($post['dislikes']) ? (int)$post['dislikes'] : 0; ?></span>
            </div>
          </button>
        </div>
        <?php if (!$isLoggedIn): ?>
          <p class="text-center text-sm opacity-70 mt-3">Please <a class="link link-primary" href="<?= asset('pages/login.php') ?>">login</a> to like or dislike.</p>
        <?php else: ?>
          <p class="text-center text-sm opacity-70 mt-3">One reaction per account. Switching moves your vote.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Author Section -->
    <div class="divider my-8"></div>
    <div class="card bg-base-200 shadow-lg mt-8">
      <div class="card-body">
        <h3 class="card-title text-2xl mb-4">About the Author</h3>
        <div class="flex items-center gap-4">
          <?php
          $authorImg = (!empty($post['user_img']) && file_exists("../uploads_users/" . $post['user_img']))
            ? ("../uploads_users/" . $post['user_img'])
            : "../uploads_users/default.jpg";
          ?>
          <div class="avatar">
            <div class="w-16 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
              <img src="<?php echo $authorImg; ?>" alt="Author avatar" />
            </div>
          </div>
          <div>
            <p class="font-semibold text-xl"><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></p>
            <p class="text-base-content/70">Blogger & Content Creator</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Back Button -->
    <div class="mt-8 text-center">
      <a href="index.php" class="btn btn-primary btn-wide">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Home
      </a>
    </div>

  </article>

  <?php include "../includes/footer.php"; ?>
  <script>
    (function() {
      const postId = <?php echo (int)$post['id']; ?>;
      const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
      const likeBtn = document.getElementById('like-btn');
      const dislikeBtn = document.getElementById('dislike-btn');
      const likeCountEl = document.getElementById('like-count');
      const dislikeCountEl = document.getElementById('dislike-count');

      function setActive(btn, active, base) {
        // base: 'success' or 'error'
        const activeClass = base === 'success' ? 'btn-success' : 'btn-error';
        const outlineClass = 'btn-outline';
        btn.classList.remove('btn-success', 'btn-error', 'btn-outline');
        if (active) {
          btn.classList.add(activeClass);
        } else {
          btn.classList.add(outlineClass, activeClass);
        }
      }

      function handleResponse(data) {
        likeCountEl.textContent = data.likes;
        dislikeCountEl.textContent = data.dislikes;
        const r = data.currentUserReaction;
        setActive(likeBtn, r === 'like', 'success');
        setActive(dislikeBtn, r === 'dislike', 'error');
      }

      async function react(action) {
        if (!isLoggedIn) {
          window.location.href = '<?= asset('pages/login.php') ?>';
          return;
        }
        try {
          const res = await fetch('../includes/handle_reaction.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `post_id=${encodeURIComponent(postId)}&action=${encodeURIComponent(action)}`
          });
          const data = await res.json();
          if (res.status === 401) {
            window.location.href = '<?= asset('pages/login.php') ?>';
            return;
          }
          if (data && data.success) {
            handleResponse(data);
          }
        } catch (e) {
          // no-op
        }
      }

      likeBtn?.addEventListener('click', () => react('like'));
      dislikeBtn?.addEventListener('click', () => react('dislike'));

      if (!isLoggedIn) {
        likeBtn.disabled = true;
        dislikeBtn.disabled = true;
      }
    })();
  </script>
</body>

</html>