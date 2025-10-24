<?php
require "connection.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'error' => 'Invalid method']);
  exit();
}

$conn = connectDb();

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$action  = $_POST['action'] ?? '';

if ($post_id <= 0 || !in_array($action, ['like','dislike'], true)) {
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit();
}

// Ensure dislikes column exists (no-op if already there)
$colRes = @mysqli_query($conn, "SHOW COLUMNS FROM posts LIKE 'dislikes'");
if ($colRes && mysqli_num_rows($colRes) === 0) {
  @mysqli_query($conn, "ALTER TABLE posts ADD COLUMN dislikes INT DEFAULT 0");
}

$column = $action === 'like' ? 'likes' : 'dislikes';

// Increment the counter
$update = mysqli_query($conn, "UPDATE posts SET $column = $column + 1 WHERE id = $post_id");

if (!$update) {
  echo json_encode(['success' => false, 'error' => 'Database error']);
  exit();
}

// Fetch updated counts
$res = mysqli_query($conn, "SELECT likes, dislikes FROM posts WHERE id = $post_id");
$row = $res ? mysqli_fetch_assoc($res) : null;

echo json_encode([
  'success' => true,
  'likes' => (int)($row['likes'] ?? 0),
  'dislikes' => (int)($row['dislikes'] ?? 0)
]);

mysqli_close($conn);
?>
