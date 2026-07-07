<?php
require_once 'db.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    requireLogin();
    $action = $_POST['action'];
    $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $userId = getCurrentUserId();

    if ($action === 'like' && $postId > 0) {
        toggleLike($postId, $userId);
        header('Location: index.php');
        exit;
    }

    if ($action === 'comment' && $postId > 0 && !empty(trim($_POST['comment_text']))) {
        addComment($postId, $userId, trim($_POST['comment_text']));
        header('Location: index.php');
        exit;
    }

    if ($action === 'create_post' && !empty(trim($_POST['caption']))) {
        createPost($userId, trim($_POST['caption']));
        header('Location: index.php');
        exit;
    }

}

$posts = getPosts(10);
$stories = getStories(10);
$userId = getCurrentUserId();
$suggestedUsers = isLoggedIn() ? getSuggestedUsers($userId, 3) : [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Instant</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="script.js" defer></script>
</head>
<body data-theme="light">
  <?php include 'nav.php'; ?>
  <main class="main-container">
    <div class="feed-section">
      <h2 class="section-title">Voor jou</h2>
      <?php foreach ($posts as $post): ?>
      <article class="post-card">
        <div class="post-header">
          <div class="post-user">
            <div class="post-avatar" style="--avatar-color: <?php echo getAvatarColor($post['username']); ?>;">
              <?php echo htmlspecialchars(strtoupper(mb_substr($post['username'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
            </div>
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
            <?php if (isLoggedIn()): ?>
            <form method="post" class="like-form">
              <input type="hidden" name="action" value="like" />
              <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>" />
              <button class="action-btn like-button <?php echo isLiked($post['id'], $userId) ? 'liked' : ''; ?>" type="submit" aria-label="Like">
                <svg viewBox="0 0 24 24" fill="<?php echo isLiked($post['id'], $userId) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
              </button>
            </form>
            <?php else: ?>
            <button class="action-btn like-button" onclick="window.location.href='login.php'" aria-label="Like">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
              </svg>
            </button>
            <?php endif; ?>
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
          <p class="post-time"><?php echo time_elapsed_string($post['created_at']); ?></p>
        </div>
        <?php if (isLoggedIn()): ?>
        <form method="post" class="comment-form">
          <input type="hidden" name="action" value="comment" />
          <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>" />
          <input id="comment_text_<?php echo (int)$post['id']; ?>" type="text" name="comment_text" placeholder="Reactie toevoegen..." required />
          <button type="submit">Plaatsen</button>
        </form>
        <?php else: ?>
        <form class="comment-form" onsubmit="window.location.href='login.php'; return false;">
          <input id="comment_text_<?php echo (int)$post['id']; ?>" type="text" name="comment_text" placeholder="Reactie toevoegen..." readonly />
          <button type="submit">Plaatsen</button>
        </form>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>

    <aside class="sidebar">
      <div class="sidebar-profile">
        <?php if (isLoggedIn()): ?>
        <div class="sidebar-avatar"></div>
        <div class="sidebar-info">
          <span class="sidebar-username"><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="sidebar-fullname"><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <?php else: ?>
        <div class="sidebar-avatar"></div>
        <div class="sidebar-info">
          <span class="sidebar-username">Niet ingelogd</span>
          <span class="sidebar-fullname">Log in om te beginnen</span>
        </div>
        <a href="login.php" class="switch-btn">Inloggen</a>
        <?php endif; ?>
      </div>
<<<<<<< Updated upstream
      <div class="sidebar-suggestions">
        <div class="suggestions-header">
          <span>Suggesties voor jou</span>
          <a href="#">Alles zien</a>
        </div>
        <?php if (empty($suggestedUsers)): ?>
        <div class="suggestion-item">
          <div class="suggestion-info">
            <span class="suggestion-username">Geen suggesties</span>
            <span class="suggestion-reason">Volg meer mensen om suggesties te zien</span>
          </div>
        </div>
        <?php else: ?>
        <?php foreach ($suggestedUsers as $suggestedUser): ?>
        <?php
          $initial = strtoupper(mb_substr($suggestedUser['username'], 0, 1));
          $postCount = (int) $suggestedUser['post_count'];
        ?>
        <div class="suggestion-item">
          <div class="suggestion-avatar" style="--avatar-color: <?php echo getAvatarColor($suggestedUser['username']); ?>;"><?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="suggestion-info">
            <span class="suggestion-username"><?php echo htmlspecialchars($suggestedUser['username'], ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="suggestion-reason"><?php echo $postCount; ?> posts</span>
          </div>
          <?php if (isLoggedIn()): ?>
          <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="follow" />
            <input type="hidden" name="following_id" value="<?php echo (int) $suggestedUser['id']; ?>" />
            <button type="submit" class="follow-btn">Volgen</button>
          </form>
          <?php else: ?>
          <button class="follow-btn" onclick="window.location.href='login.php'">Volgen</button>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
=======
>>>>>>> Stashed changes
      <div class="sidebar-footer">
        <p>Over · Help · Pers · API · Vacatures · Privacy · Voorwaarden · Locaties · Taal</p>
        <p>© 2026 INSTANT</p>
      </div>
    </aside>
  </main>

  <?php
  function time_elapsed_string($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Net';
    if ($diff < 3600) return floor($diff / 60) . ' minuten geleden';
    if ($diff < 86400) return floor($diff / 3600) . ' uur geleden';
    if ($diff < 604800) return floor($diff / 86400) . ' dagen geleden';
    return date('d M Y', $time);
  }
  ?>

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
