<?php
require_once 'db.php';
require_once 'auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];
    $userId = getCurrentUserId();
    $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

    if ($action === 'like' && $postId > 0) {
        toggleLike($postId, $userId);
        header('Location: likes.php');
        exit;
    }

    if ($action === 'comment' && $postId > 0 && !empty(trim($_POST['comment_text']))) {
        addComment($postId, $userId, trim($_POST['comment_text']));
        header('Location: likes.php');
        exit;
    }
}

$userId = getCurrentUserId();
$likedPosts = getLikedPosts($userId, 20);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Geliked - Instant</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="script.js" defer></script>
</head>
<body data-theme="light">
  <?php include 'nav.php'; ?>

  <main class="main-container">
    <div class="feed-section">
      <h2 class="section-title">Geliked</h2>
      <?php if (empty($likedPosts)): ?>
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <p>Je hebt nog geen posts geliked</p>
        <a href="index.php">Ontdek posts</a>
      </div>
      <?php else: ?>
      <?php foreach ($likedPosts as $post): ?>
      <article class="post-card">
        <div class="post-header">
          <div class="post-user">
            <div class="post-avatar"></div>
            <div class="post-user-info">
              <span class="post-username"><?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="post-location"><?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
            </div>
          </div>
          <button class="post-more">•••</button>
        </div>
        <div class="post-image image-gradient"></div>
        <div class="post-actions">
          <div class="post-actions-left">
            <form method="post" class="like-form">
              <input type="hidden" name="action" value="like" />
              <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>" />
              <button class="action-btn like-button liked" type="submit" aria-label="Like">
                <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
              </button>
            </form>
            <button class="action-btn comment-btn" data-target="comment_text_<?php echo (int)$post['id']; ?>" aria-label="Reactie">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
              </svg>
            </button>
            <button class="action-btn share-btn" aria-label="Delen">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="22" y1="2" x2="11" y2="13"/>
                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
              </svg>
            </button>
          </div>
          <button class="action-btn save-btn" aria-label="Opslaan">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
            </svg>
          </button>
        </div>
        <div class="post-info">
          <p class="post-likes"><?php echo (int)$post['like_count']; ?> vind-ik-leuks</p>
          <p class="post-caption">
            <span class="caption-username"><?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php echo htmlspecialchars($post['caption'], ENT_QUOTES, 'UTF-8'); ?>
          </p>
          <p class="post-comments"><?php echo (int)$post['comment_count']; ?> reacties</p>
          <p class="post-time">Geliked op <?php echo date('d M Y', strtotime($post['liked_at'])); ?></p>
        </div>
        <form method="post" class="comment-form">
          <input type="hidden" name="action" value="comment" />
          <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>" />
          <input id="comment_text_<?php echo (int)$post['id']; ?>" type="text" name="comment_text" placeholder="Reactie toevoegen..." required />
          <button type="submit">Plaatsen</button>
        </form>
      </article>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

  <!-- Post Modal -->
  <div id="postModal" class="modal" style="display: none;">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Nieuwe Post</h2>
        <button class="modal-close" onclick="document.getElementById('postModal').style.display='none'">×</button>
      </div>
      <form method="post" class="modal-form">
        <input type="hidden" name="action" value="create_post" />
        <textarea name="caption" placeholder="Wat wil je delen?" required></textarea>
        <button type="submit" class="modal-submit">Post Plaatsen</button>
      </form>
    </div>
  </div>

</body>
</html>
