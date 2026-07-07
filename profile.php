<?php
require_once 'db.php';
require_once 'auth.php';

$currentUserId = isLoggedIn() ? getCurrentUserId() : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    requireLogin();
    $action = $_POST['action'];
    $userId = getCurrentUserId();
    $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

    if ($action === 'like' && $postId > 0) {
        toggleLike($postId, $userId);
        header('Location: profile.php');
        exit;
    }

    if ($action === 'comment' && $postId > 0 && !empty(trim($_POST['comment_text']))) {
        addComment($postId, $userId, trim($_POST['comment_text']));
        header('Location: profile.php');
        exit;
    }

    if ($action === 'create_post' && !empty(trim($_POST['caption']))) {
        createPost($userId, trim($_POST['caption']));
        header('Location: profile.php');
        exit;
    }

    if ($action === 'follow' && !empty($_POST['following_id'])) {
        $followingId = (int) $_POST['following_id'];
        followUser($userId, $followingId);
        header('Location: profile.php?user_id=' . $followingId);
        exit;
    }

    if ($action === 'unfollow' && !empty($_POST['following_id'])) {
        $followingId = (int) $_POST['following_id'];
        unfollowUser($userId, $followingId);
        header('Location: profile.php?user_id=' . $followingId);
        exit;
    }
}

$user = null;
$posts = [];
$stories = [];
$isOwnProfile = false;
$viewUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

if ($viewUserId > 0) {
    $user = getUser($viewUserId);
    if ($user) {
        $posts = getPostsByUser($viewUserId, 20);
        $isOwnProfile = isLoggedIn() && getCurrentUserId() === $viewUserId;
    }
} elseif (isLoggedIn()) {
    $userId = getCurrentUserId();
    $user = getUser($userId);
    $posts = getPostsByUser($userId, 20);
    $stories = getStoriesByUser($userId, 10);
    $isOwnProfile = true;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profiel - Instant</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="script.js" defer></script>
</head>
<body data-theme="light">
  <?php include 'nav.php'; ?>
  <main class="main-container">
    <?php if ($viewUserId > 0 && !$user): ?>
    <div class="empty-state">
      <p>Account niet gevonden.</p>
      <a href="search.php" class="btn-primary" style="margin-top: 16px; display: inline-block;">Terug naar zoeken</a>
    </div>
    <?php elseif (!$user && !isLoggedIn()): ?>
    <div class="profile-guest">
      <div class="profile-guest-content">
        <div class="profile-guest-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <h1>Welkom bij Instant</h1>
        <p>Maak een account aan om te posten, stories te plaatsen en meer.</p>
        <div class="profile-guest-buttons">
          <a href="register.php" class="btn-primary">Registreren</a>
          <a href="login.php" class="btn-secondary">Inloggen</a>
        </div>
      </div>
    </div>
    <?php elseif ($user): ?>
    <div class="profile-header">
      <div class="profile-avatar-large" style="--avatar-color: <?php echo getAvatarColor($user['username']); ?>;"><?php echo htmlspecialchars(strtoupper(mb_substr($user['username'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="profile-info">
        <h1><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo count($posts); ?> posts · <?php echo getFollowersCount($user['id']); ?> volgers · <?php echo getFollowingCount($user['id']); ?> volgend</p>
        <?php if (!$isOwnProfile && isLoggedIn()): ?>
        <div style="margin-top: 8px; text-align: right;">
        <?php if (isFollowing($currentUserId, $user['id'])): ?>
        <form method="post" style="display: inline;">
          <input type="hidden" name="action" value="unfollow" />
          <input type="hidden" name="following_id" value="<?php echo (int)$user['id']; ?>" />
          <button type="submit" class="btn-primary" style="background: #6b7280; padding: 8px 16px; font-size: 14px;">Ontvolgen</button>
        </form>
        <?php else: ?>
        <form method="post" style="display: inline;">
          <input type="hidden" name="action" value="follow" />
          <input type="hidden" name="following_id" value="<?php echo (int)$user['id']; ?>" />
          <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 14px;">Volgen</button>
        </form>
        <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="profile-bio-edit">
      <button class="btn-secondary" onclick="document.getElementById('bioModal').style.display='flex'">Bio bewerken</button>
      <a href="logout.php" class="btn-secondary logout-btn">Uitloggen</a>
    </div>

    <div class="profile-tabs">
      <button class="tab-btn active" onclick="showTab('posts', this)">Posts</button>
      <button class="tab-btn" onclick="showTab('liked', this)">Geliked</button>
    </div>

    <div class="profile-content">
      <div id="posts-tab">
      <?php if (empty($posts)): ?>
      <div class="empty-state">
        <p><?php echo $isOwnProfile ? 'Nog geen posts. Plaats je eerste post!' : 'Deze gebruiker heeft nog geen posts.'; ?></p>
      </div>
      <?php else: ?>
      <div class="profile-posts">
        <?php foreach ($posts as $post): ?>
        <article class="post-card">
          <div class="post-header">
            <div class="post-user">
              <div class="post-avatar"></div>
              <div class="post-user-info">
                <span class="post-username"><?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="post-location"><?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
              </div>
            </div>
          </div>
          <div class="post-image image-gradient"></div>
          <div class="post-actions">
            <div class="post-actions-left">
              <?php $isPostLiked = isLoggedIn() && isLiked($post['id'], $currentUserId); ?>
              <?php if (isLoggedIn()): ?>
              <form method="post" class="like-form">
                <input type="hidden" name="action" value="like" />
                <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>" />
                <button class="action-btn like-button <?php echo $isPostLiked ? 'liked' : ''; ?>" type="submit" aria-label="Like">
                  <svg viewBox="0 0 24 24" fill="<?php echo $isPostLiked ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                  </svg>
                </button>
              </form>
              <?php else: ?>
              <button class="action-btn like-button" type="button" onclick="window.location.href='login.php'" aria-label="Like">
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
            </div>
          </div>
          <div class="post-info">
            <p class="post-likes"><?php echo (int)$post['like_count']; ?> vind-ik-leuks</p>
            <p class="post-caption">
              <span class="caption-username"><?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?></span>
              <?php echo htmlspecialchars($post['caption'], ENT_QUOTES, 'UTF-8'); ?>
            </p>
            <p class="post-comments"><?php echo (int)$post['comment_count']; ?> reacties</p>
          </div>
      <?php if ($isOwnProfile): ?>
      <form method="post" class="comment-form" style="margin-top: 16px;">
        <input type="hidden" name="action" value="comment" />
        <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>" />
        <input id="comment_text_<?php echo (int)$post['id']; ?>" type="text" name="comment_text" placeholder="Reactie toevoegen..." required />
        <button type="submit">Plaatsen</button>
      </form>
      <?php endif; ?>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      </div>
      <div id="liked-tab" style="display: none;">
        <?php
        $likedPosts = getLikedPosts($user['id'], 20);
        if (empty($likedPosts)):
        ?>
        <div class="empty-state">
          <p><?php echo $isOwnProfile ? 'Je hebt nog geen posts geliked' : 'Deze gebruiker heeft nog geen posts geliked'; ?></p>
        </div>
        <?php else: ?>
        <div class="profile-posts">
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
            </div>
            <div class="post-image image-gradient"></div>
            <div class="post-actions">
              <div class="post-actions-left">
                <?php $isPostLiked = isLoggedIn() && isLiked($post['id'], $currentUserId); ?>
                <?php if (isLoggedIn()): ?>
                <form method="post" class="like-form">
                  <input type="hidden" name="action" value="like" />
                  <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>" />
                  <button class="action-btn like-button <?php echo $isPostLiked ? 'liked' : ''; ?>" type="submit" aria-label="Like">
                    <svg viewBox="0 0 24 24" fill="<?php echo $isPostLiked ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                  </button>
                </form>
                <?php else: ?>
                <button class="action-btn like-button" type="button" onclick="window.location.href='login.php'" aria-label="Like">
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
              </div>
            </div>
            <div class="post-info">
              <p class="post-likes"><?php echo (int)$post['like_count']; ?> vind-ik-leuks</p>
              <p class="post-caption">
                <span class="caption-username"><?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php echo htmlspecialchars($post['caption'], ENT_QUOTES, 'UTF-8'); ?>
              </p>
              <p class="post-comments"><?php echo (int)$post['comment_count']; ?> reacties</p>
            </div>
            <form method="post" class="comment-form" style="margin-top: 16px;">
              <input type="hidden" name="action" value="comment" />
              <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>" />
              <input id="comment_text_<?php echo (int)$post['id']; ?>" type="text" name="comment_text" placeholder="Reactie toevoegen..." required />
              <button type="submit">Plaatsen</button>
            </form>
          </article>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
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

  <script>
    function showTab(tabName) {
      document.getElementById('posts-tab').style.display = tabName === 'posts' ? 'block' : 'none';
      document.getElementById('liked-tab').style.display = tabName === 'liked' ? 'block' : 'none';
      
      var tabs = document.querySelectorAll('.tab-btn');
      tabs.forEach(function(tab) {
        tab.classList.remove('active');
      });
      event.target.classList.add('active');
    }
  </script>

  <style>
    .profile-guest {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 60vh;
    }
    .profile-guest-content {
      text-align: center;
      max-width: 400px;
    }
    .profile-guest-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 24px;
      color: var(--muted);
    }
    .profile-guest-icon svg {
      width: 100%;
      height: 100%;
    }
    .profile-guest h1 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 12px;
    }
    .profile-guest p {
      color: var(--muted);
      margin-bottom: 32px;
    }
    .profile-guest-buttons {
      display: flex;
      gap: 12px;
      justify-content: center;
    }
    .btn-primary, .btn-secondary {
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
    }
    .btn-primary {
      background: var(--brand);
      color: white;
    }
    .btn-secondary {
      background: var(--border);
      color: var(--text);
    }
    .profile-header {
      display: flex;
      align-items: center;
      gap: 24px;
      padding: 40px 0;
      border-bottom: 1px solid var(--border);
      margin-bottom: 24px;
    }
    .profile-avatar-large {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: var(--avatar-gradient);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 48px;
      box-shadow: var(--shadow-lg);
    }
    .profile-info h1 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 8px;
      background: var(--gradient-1);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .profile-info p {
      color: var(--muted);
    }
    .profile-bio {
      margin-top: 12px;
      color: var(--text);
      font-size: 15px;
      line-height: 1.6;
      padding: 12px 16px;
      background: var(--bg);
      border-radius: 12px;
      border: 1px solid var(--border);
    }
    .profile-bio-edit {
      margin-bottom: 24px;
      display: flex;
      gap: 12px;
    }
    .profile-bio-edit button,
    .profile-bio-edit a {
      padding: 10px 20px;
      border-radius: 10px;
      transition: all 0.2s ease;
    }
    .profile-bio-edit button:hover,
    .profile-bio-edit a:hover {
      transform: translateY(-1px);
      box-shadow: var(--shadow);
    }
    .logout-btn {
      background: #ef4444;
      color: white;
    }
    .logout-btn:hover {
      background: #dc2626;
    }
    .profile-tabs {
      display: flex;
      gap: 24px;
      margin-bottom: 24px;
      border-bottom: 1px solid var(--border);
    }
    .tab-btn {
      padding: 16px 0;
      background: none;
      border: none;
      font-weight: 600;
      color: var(--muted);
      cursor: pointer;
      border-bottom: 2px solid transparent;
    }
    .tab-btn.active {
      color: var(--text);
      border-bottom-color: var(--text);
    }
    .profile-posts {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--muted);
    }
    .main-container {
      max-width: 470px;
      margin: 30px auto;
      padding: 0 20px;
    }
  </style>
</body>
</html>
