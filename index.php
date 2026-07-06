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

    if ($action === 'follow' && !empty($_POST['following_id'])) {
        $followingId = (int) $_POST['following_id'];
        followUser($userId, $followingId);
        header('Location: index.php');
        exit;
    }

    if ($action === 'unfollow' && !empty($_POST['following_id'])) {
        $followingId = (int) $_POST['following_id'];
        unfollowUser($userId, $followingId);
        header('Location: index.php');
        exit;
    }
}

$posts = getPosts(10);
$stories = getStories(10);
$userId = getCurrentUserId();
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
  <header class="navbar">
    <div class="navbar-brand">
      <svg class="logo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
      </svg>
      <span class="brand-text">Instant</span>
    </div>
    <form action="search.php" method="get" class="navbar-search">
      <input type="search" name="q" placeholder="Zoek accounts..." class="search-input" autocomplete="off">
      <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
    </form>
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
      <button class="nav-icon profile-btn" aria-label="Profiel" onclick="window.location.href='profile.php'">
        <div class="profile-avatar-small"></div>
      </button>
      <button id="menuToggle" class="nav-icon" aria-label="Menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
    </div>
  </header>

  <!-- Hamburger Menu -->
  <div id="hamburgerMenu" class="hamburger-menu" style="display: none;">
    <div class="hamburger-menu-content">
      <button class="hamburger-menu-close" onclick="document.getElementById('hamburgerMenu').style.display='none'">×</button>
      <a href="settings.php" class="hamburger-menu-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="3"/>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
        </svg>
        Settings
      </a>
      <button id="themeToggleMenu" class="hamburger-menu-item">
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
        Uiterlijk wisselen
      </button>
      <?php if (isLoggedIn()): ?>
      <a href="logout.php" class="hamburger-menu-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Uitloggen
      </a>
      <?php endif; ?>
    </div>
  </div>

  <main class="main-container">
    <div class="feed-section">
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
        <a href="logout.php" class="switch-btn">Uitloggen</a>
        <?php else: ?>
        <div class="sidebar-avatar"></div>
        <div class="sidebar-info">
          <span class="sidebar-username">Niet ingelogd</span>
          <span class="sidebar-fullname">Log in om te beginnen</span>
        </div>
        <a href="login.php" class="switch-btn">Inloggen</a>
        <?php endif; ?>
      </div>
      <div class="sidebar-suggestions">
        <div class="suggestions-header">
          <span>Suggesties voor jou</span>
          <a href="#">Alles zien</a>
        </div>
        <div class="suggestion-item">
          <div class="suggestion-avatar">N</div>
          <div class="suggestion-info">
            <span class="suggestion-username">nieuwe_user</span>
            <span class="suggestion-reason">Nieuw op Instant</span>
          </div>
          <?php if (isLoggedIn()): ?>
          <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="follow" />
            <input type="hidden" name="following_id" value="2" />
            <button type="submit" class="follow-btn">Volgen</button>
          </form>
          <?php else: ?>
          <button class="follow-btn" onclick="window.location.href='login.php'">Volgen</button>
          <?php endif; ?>
        </div>
        <div class="suggestion-item">
          <div class="suggestion-avatar">A</div>
          <div class="suggestion-info">
            <span class="suggestion-username">anna_design</span>
            <span class="suggestion-reason">Gevolgd door peter</span>
          </div>
          <?php if (isLoggedIn()): ?>
          <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="follow" />
            <input type="hidden" name="following_id" value="3" />
            <button type="submit" class="follow-btn">Volgen</button>
          </form>
          <?php else: ?>
          <button class="follow-btn" onclick="window.location.href='login.php'">Volgen</button>
          <?php endif; ?>
        </div>
        <div class="suggestion-item">
          <div class="suggestion-avatar">T</div>
          <div class="suggestion-info">
            <span class="suggestion-username">tech_daily</span>
            <span class="suggestion-reason">Populair</span>
          </div>
          <?php if (isLoggedIn()): ?>
          <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="follow" />
            <input type="hidden" name="following_id" value="4" />
            <button type="submit" class="follow-btn">Volgen</button>
          </form>
          <?php else: ?>
          <button class="follow-btn" onclick="window.location.href='login.php'">Volgen</button>
          <?php endif; ?>
        </div>
      </div>
      <div class="sidebar-footer">
        <p>Over · Help · Pers · API · Vacatures · Privacy · Voorwaarden · Locaties · Taal</p>
        <p>© 2024 INSTANT</p>
      </div>
    </aside>
  </main>

  <nav class="mobile-nav">
    <button class="mobile-nav-btn active">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
    </button>
    <button class="mobile-nav-btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </button>
    <button class="mobile-nav-btn" <?php echo isLoggedIn() ? "onclick=\"document.getElementById('postModal').style.display='flex'\"" : "onclick=\"window.location.href='login.php'\""; ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
    </button>
    <button class="mobile-nav-btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
    </button>
    <?php if (isLoggedIn()): ?>
    <button class="mobile-nav-btn" onclick="window.location.href='profile.php'">
      <div class="mobile-avatar"></div>
    </button>
    <?php else: ?>
    <a href="login.php" class="mobile-nav-btn" style="text-decoration: none; color: var(--text); font-size: 12px; display: flex; align-items: center; justify-content: center;">Inloggen</a>
    <?php endif; ?>
  </nav>

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

  <script>
    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    }

    document.getElementById('menuToggle').onclick = function() {
      document.getElementById('hamburgerMenu').style.display = 'flex';
    }

    document.getElementById('themeToggleMenu').onclick = function() {
      document.getElementById('hamburgerMenu').style.display = 'none';
      const nextTheme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
      document.body.dataset.theme = nextTheme;
      localStorage.setItem('instant-theme', nextTheme);
    }
  </script>

  <style>
    .hamburger-menu {
      position: fixed;
      top: 0;
      right: 0;
      width: 280px;
      height: 100vh;
      background: var(--bg);
      box-shadow: -2px 0 10px rgba(0,0,0,0.1);
      z-index: 1000;
      flex-direction: column;
    }
    .hamburger-menu-content {
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .hamburger-menu-close {
      align-self: flex-end;
      background: none;
      border: none;
      font-size: 32px;
      cursor: pointer;
      color: var(--text);
      padding: 0;
      width: 40px;
      height: 40px;
    }
    .hamburger-menu-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 16px;
      border-radius: 8px;
      text-decoration: none;
      color: var(--text);
      font-weight: 500;
      background: var(--border);
      border: none;
      cursor: pointer;
      font-size: 16px;
    }
    .hamburger-menu-item:hover {
      background: var(--muted);
    }
    .hamburger-menu-item svg {
      width: 20px;
      height: 20px;
    }
  </style>
</body>
</html>
