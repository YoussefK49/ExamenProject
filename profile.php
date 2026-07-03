<?php
require_once 'db.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    requireLogin();
    $action = $_POST['action'];
    $userId = getCurrentUserId();

    if ($action === 'create_post' && !empty(trim($_POST['caption']))) {
        createPost($userId, trim($_POST['caption']));
        header('Location: profile.php');
        exit;
    }

    if ($action === 'update_bio') {
        updateBio($userId, trim($_POST['bio']));
        header('Location: profile.php');
        exit;
    }
}

$user = null;
$posts = [];

if (isLoggedIn()) {
    $userId = getCurrentUserId();
    $user = getUser($userId);
    $posts = getPostsByUser($userId, 20);
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
  <header class="navbar">
    <div class="navbar-brand">
      <svg class="logo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
      </svg>
      <span class="brand-text">Instant</span>
    </div>
    <div class="navbar-search">
      <input type="text" placeholder="Zoek..." class="search-input">
      <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
    </div>
    <div class="navbar-icons">
      <a href="index.php" class="nav-icon" aria-label="Home">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
        </svg>
      </a>
      <button class="nav-icon" aria-label="Berichten">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
      </button>
      <?php if (isLoggedIn()): ?>
      <button class="nav-icon" aria-label="Nieuwe post" onclick="document.getElementById('postModal').style.display='flex'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          <line x1="12" y1="8" x2="12" y2="16"/>
          <line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
      </button>
      <?php endif; ?>
      <button class="nav-icon profile-btn" aria-label="Profiel">
        <div class="profile-avatar-small"></div>
      </button>
      <button id="themeToggle" class="nav-icon" aria-label="Wissel thema">
        <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"/>
          <line x1="12" y1="1" x2="12" y2="3"/>
          <line x1="12" y1="21" x2="12" y2="23"/>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
          <line x1="1" y1="12" x2="3" y2="12"/>
          <line x1="21" y1="12" x2="23" y2="12"/>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
        </svg>
      </button>
    </div>
  </header>

  <main class="main-container">
    <?php if (!isLoggedIn()): ?>
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
    <?php else: ?>
    <div class="profile-header">
      <div class="profile-avatar-large"></div>
      <div class="profile-info">
        <h1><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo count($posts); ?> posts</p>
        <?php if (!empty($user['bio'])): ?>
        <p class="profile-bio"><?php echo htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="profile-bio-edit">
      <button class="btn-secondary" onclick="document.getElementById('bioModal').style.display='flex'">Bio bewerken</button>
    </div>

    <div class="profile-content">
      <?php if (empty($posts)): ?>
      <div class="empty-state">
        <p>Nog geen posts. Plaats je eerste post!</p>
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
              <form method="post" class="like-form">
                <input type="hidden" name="action" value="like" />
                <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>" />
                <button class="action-btn like-button" type="submit" aria-label="Like">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                  </svg>
                </button>
              </form>
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
          <form method="post" class="comment-form">
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

  <!-- Bio Modal -->
  <div id="bioModal" class="modal" style="display: none;">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Bio Bewerken</h2>
        <button class="modal-close" onclick="document.getElementById('bioModal').style.display='none'">×</button>
      </div>
      <form method="post" class="modal-form">
        <input type="hidden" name="action" value="update_bio" />
        <textarea name="bio" placeholder="Vertel iets over jezelf..." maxlength="150"><?php echo isset($user['bio']) ? htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
        <button type="submit" class="modal-submit">Opslaan</button>
      </form>
    </div>
  </div>

  <script>
    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
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
      background: var(--story-gradient);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 48px;
    }
    .profile-info h1 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .profile-info p {
      color: var(--muted);
    }
    .profile-bio {
      margin-top: 8px;
      color: var(--text);
      font-size: 14px;
    }
    .profile-bio-edit {
      margin-bottom: 24px;
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
