<?php
session_start();
require_once 'db.php';

// Login handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'login' && !empty($_POST['username']) && !empty($_POST['password'])) {
        $user = loginUser($_POST['username'], $_POST['password']);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $loginError = 'Ongeldige gebruikersnaam of wachtwoord';
        }
    }

    if ($action === 'register' && !empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        if (registerUser($_POST['username'], $_POST['email'], $_POST['password'])) {
            $user = loginUser($_POST['username'], $_POST['password']);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: index.php');
                exit;
            }
        } else {
            $registerError = 'Gebruikersnaam of e-mail bestaat al';
        }
    }

    if ($action === 'logout') {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    // Actions die login vereisen
    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

        if ($action === 'like' && $postId > 0) {
            addLike($postId, $userId);
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

        if ($action === 'create_story') {
            addStory($userId);
            header('Location: index.php');
            exit;
        }
    }
}

$posts = getPosts(10);
$stories = getStories(10);
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
    <div class="navbar-search">
      <input type="text" placeholder="Zoek..." class="search-input">
      <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
    </div>
    <div class="navbar-icons">
      <button class="nav-icon" aria-label="Home">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
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
      <?php if (isLoggedIn()): ?>
      <button class="nav-icon profile-btn" aria-label="Profiel">
        <div class="profile-avatar-small"><?php echo htmlspecialchars(substr($_SESSION['username'], 0, 1), ENT_QUOTES, 'UTF-8'); ?></div>
      </button>
      <form method="post" style="display:inline;">
        <input type="hidden" name="action" value="logout" />
        <button class="nav-icon" aria-label="Uitloggen" title="Uitloggen">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </button>
      </form>
      <?php else: ?>
      <button class="nav-icon" onclick="document.getElementById('loginModal').style.display='flex'" aria-label="Inloggen">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
      </button>
      <?php endif; ?>
    </div>
  </header>

  <main class="main-container">
    <div class="stories-section">
      <?php if (isLoggedIn()): ?>
      <div class="story-item story-add" onclick="document.getElementById('storyModal').style.display='flex'">
        <div class="story-ring">
          <div class="story-avatar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="5" x2="12" y2="19"/>
              <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
          </div>
        </div>
        <span>Nieuw</span>
      </div>
      <?php endif; ?>
      <?php foreach ($stories as $story): ?>
      <div class="story-item">
        <div class="story-ring">
          <div class="story-avatar"><?php echo htmlspecialchars(substr($story['username'], 0, 1), ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <span><?php echo htmlspecialchars($story['username'], ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="feed-section">
      <?php foreach ($posts as $post): ?>
      <article class="post-card">
        <div class="post-header">
          <div class="post-user">
            <div class="post-avatar"><?php echo htmlspecialchars(substr($post['username'], 0, 1), ENT_QUOTES, 'UTF-8'); ?></div>
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
              <button class="action-btn like-button" type="submit" aria-label="Like">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
              </button>
            </form>
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
        <div class="comment-form">
          <input type="text" placeholder="Log in om te reageren..." disabled />
        </div>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>
  </main>

  <nav class="mobile-nav">
    <button class="mobile-nav-btn active">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
    </button>
    <?php if (isLoggedIn()): ?>
    <button class="mobile-nav-btn" onclick="document.getElementById('postModal').style.display='flex'">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
    </button>
    <?php endif; ?>
    <button class="mobile-nav-btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
    </button>
    <?php if (isLoggedIn()): ?>
    <button class="mobile-nav-btn">
      <div class="mobile-avatar"><?php echo htmlspecialchars(substr($_SESSION['username'], 0, 1), ENT_QUOTES, 'UTF-8'); ?></div>
    </button>
    <form method="post" style="display:inline;">
      <input type="hidden" name="action" value="logout" />
      <button class="mobile-nav-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
      </button>
    </form>
    <?php else: ?>
    <button class="mobile-nav-btn" onclick="document.getElementById('loginModal').style.display='flex'">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
    </button>
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

  <!-- Story Modal -->
  <div id="storyModal" class="modal" style="display: none;">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Nieuwe Story</h2>
        <button class="modal-close" onclick="document.getElementById('storyModal').style.display='none'">×</button>
      </div>
      <form method="post" class="modal-form">
        <input type="hidden" name="action" value="create_story" />
        <p>Story maken (24 uur zichtbaar)</p>
        <button type="submit" class="modal-submit">Story Plaatsen</button>
      </form>
    </div>
  </div>

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

  <!-- Login Modal -->
  <div id="loginModal" class="modal" style="display: none;">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Inloggen</h2>
        <button class="modal-close" onclick="document.getElementById('loginModal').style.display='none'">×</button>
      </div>
      <form method="post" class="modal-form">
        <input type="hidden" name="action" value="login" />
        <?php if (isset($loginError)): ?>
        <p style="color: #ed4956;"><?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Gebruikersnaam" required />
        <input type="password" name="password" placeholder="Wachtwoord" required />
        <button type="submit" class="modal-submit">Inloggen</button>
        <p style="text-align: center; margin-top: 16px;">
          Nog geen account? <a href="#" onclick="document.getElementById('loginModal').style.display='none'; document.getElementById('registerModal').style.display='flex'">Registreren</a>
        </p>
      </form>
    </div>
  </div>

  <!-- Register Modal -->
  <div id="registerModal" class="modal" style="display: none;">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Registreren</h2>
        <button class="modal-close" onclick="document.getElementById('registerModal').style.display='none'">×</button>
      </div>
      <form method="post" class="modal-form">
        <input type="hidden" name="action" value="register" />
        <?php if (isset($registerError)): ?>
        <p style="color: #ed4956;"><?php echo htmlspecialchars($registerError, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Gebruikersnaam" required />
        <input type="email" name="email" placeholder="E-mail" required />
        <input type="password" name="password" placeholder="Wachtwoord" required />
        <button type="submit" class="modal-submit">Registreren</button>
        <p style="text-align: center; margin-top: 16px;">
          Al een account? <a href="#" onclick="document.getElementById('registerModal').style.display='none'; document.getElementById('loginModal').style.display='flex'">Inloggen</a>
        </p>
      </form>
    </div>
  </div>

  <script>
    // Modal click outside to close
    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    }
  </script>
</body>
</html>
