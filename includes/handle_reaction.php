<?php
require_once __DIR__ . '/connection.php';

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'error' => 'Invalid method']);
  exit();
}

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'not_logged_in']);
  exit();
}

$conn = connectDb();

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$action  = $_POST['action'] ?? '';
$user_id = (int)$_SESSION['user_id'];

if ($post_id <= 0 || !in_array($action, ['like','dislike'], true)) {
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit();
}

// Ensure supporting structures exist
// 1) dislikes column on posts (noop if already exists)
$colRes = @mysqli_query($conn, "SHOW COLUMNS FROM posts LIKE 'dislikes'");
if ($colRes && mysqli_num_rows($colRes) === 0) {
  @mysqli_query($conn, "ALTER TABLE posts ADD COLUMN dislikes INT DEFAULT 0");
}

// 2) post_reactions table with unique (post_id, user_id)
$createTableSql = "CREATE TABLE IF NOT EXISTS post_reactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  reaction ENUM('like','dislike') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_post_user (post_id, user_id),
  KEY idx_post (post_id),
  KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
@mysqli_query($conn, $createTableSql);

mysqli_begin_transaction($conn);
try {
  // Get current reaction for this user/post
  $selSql = sprintf(
    "SELECT reaction FROM post_reactions WHERE post_id=%d AND user_id=%d FOR UPDATE",
    $post_id,
    $user_id
  );
  $curRes = mysqli_query($conn, $selSql);
  $current = $curRes && mysqli_num_rows($curRes) ? mysqli_fetch_assoc($curRes)['reaction'] : null;

  if ($current === null) {
    // No reaction yet -> insert new and increment counter
    $insSql = sprintf(
      "INSERT INTO post_reactions (post_id, user_id, reaction) VALUES (%d, %d, '%s')",
      $post_id,
      $user_id,
      mysqli_real_escape_string($conn, $action)
    );
    if (!mysqli_query($conn, $insSql)) {
      throw new Exception('Insert reaction failed');
    }
    $col = $action === 'like' ? 'likes' : 'dislikes';
    if (!mysqli_query($conn, "UPDATE posts SET $col = $col + 1 WHERE id = $post_id")) {
      throw new Exception('Increment counter failed');
    }
    $newReaction = $action;
  } elseif ($current === $action) {
    // Same reaction clicked again -> do nothing (idempotent)
    $newReaction = $current;
  } else {
    // Switching reaction -> update and adjust counters
    $updSql = sprintf(
      "UPDATE post_reactions SET reaction='%s' WHERE post_id=%d AND user_id=%d",
      mysqli_real_escape_string($conn, $action),
      $post_id,
      $user_id
    );
    if (!mysqli_query($conn, $updSql)) {
      throw new Exception('Update reaction failed');
    }
    if ($action === 'like') {
      // dislike -> like
      if (!mysqli_query($conn, "UPDATE posts SET likes = likes + 1, dislikes = GREATEST(dislikes - 1, 0) WHERE id = $post_id")) {
        throw new Exception('Counter switch failed');
      }
    } else {
      // like -> dislike
      if (!mysqli_query($conn, "UPDATE posts SET dislikes = dislikes + 1, likes = GREATEST(likes - 1, 0) WHERE id = $post_id")) {
        throw new Exception('Counter switch failed');
      }
    }
    $newReaction = $action;
  }

  // Read back counts
  $countRes = mysqli_query($conn, "SELECT likes, dislikes FROM posts WHERE id = $post_id FOR UPDATE");
  $row = $countRes ? mysqli_fetch_assoc($countRes) : ['likes' => 0, 'dislikes' => 0];

  mysqli_commit($conn);

  echo json_encode([
    'success' => true,
    'likes' => (int)$row['likes'],
    'dislikes' => (int)$row['dislikes'],
    'currentUserReaction' => $newReaction,
  ]);
} catch (Throwable $e) {
  mysqli_rollback($conn);
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'server_error']);
}

mysqli_close($conn);
