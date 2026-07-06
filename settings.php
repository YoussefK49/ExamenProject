<?php
require_once 'db.php';
require_once 'auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = getCurrentUserId();
    $likesPublic = isset($_POST['likes_public']) ? 1 : 0;
    $accountPublic = isset($_POST['account_public']) ? 1 : 0;
    
    global $mysqli;
    $stmt = $mysqli->prepare("UPDATE users SET likes_public = ?, account_public = ? WHERE id = ?");
    $stmt->bind_param('iii', $likesPublic, $accountPublic, $userId);
    $stmt->execute();
    $stmt->close();
    
    // Update session
    $_SESSION['likes_public'] = $likesPublic;
    $_SESSION['account_public'] = $accountPublic;
}

// Get current user data for display
$user = getUser(getCurrentUserId());
if ($user) {
    $_SESSION['email'] = $user['email'] ?? '';
    $_SESSION['likes_public'] = $user['likes_public'] ?? 1;
    $_SESSION['account_public'] = $user['account_public'] ?? 1;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Settings - Instant</title>
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
      <button class="nav-icon" aria-label="Nieuwe post" onclick="document.getElementById('postModal').style.display='flex'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          <line x1="12" y1="8" x2="12" y2="16"/>
          <line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
      </button>
      <button class="nav-icon profile-btn" aria-label="Profiel" onclick="window.location.href='profile.php'">
        <div class="profile-avatar-small" style="--avatar-color: <?php echo getAvatarColor($_SESSION['username']); ?>;">
          <?php echo htmlspecialchars(strtoupper(mb_substr($_SESSION['username'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
        </div>
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
      <a href="logout.php" class="hamburger-menu-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Uitloggen
      </a>
    </div>
  </div>

  <main class="main-container">
    <h1>Settings</h1>
    <div class="settings-section">
      <h2>Account</h2>
      <p>Gebruikersnaam: <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></p>
      <p>Email: <?php echo htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
      <a href="logout.php" class="btn-primary">Uitloggen</a>
    </div>
    <div class="settings-section">
      <h2>Privacy</h2>
      <form method="post">
        <div class="form-group">
          <label>
            <input type="checkbox" name="likes_public" value="1" checked>
            Likes tonen aan anderen
          </label>
        </div>
        <div class="form-group">
          <label>
            <input type="checkbox" name="account_public" value="1" checked>
            Openbaar account
          </label>
        </div>
        <button type="submit" class="btn-primary">Opslaan</button>
      </form>
    </div>
    <div class="settings-section">
      <h2>Weergave</h2>
      <button id="themeToggleSettings" class="btn-secondary">Uiterlijk wisselen</button>
    </div>
  </main>

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

    document.getElementById('themeToggleSettings').onclick = function() {
      const nextTheme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
      document.body.dataset.theme = nextTheme;
      localStorage.setItem('instant-theme', nextTheme);
    }
  </script>

  <style>
    .main-container {
      max-width: 470px;
      margin: 30px auto;
      padding: 0 20px;
    }
    .settings-section {
      background: var(--border);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
    }
    .settings-section h2 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 12px;
    }
    .settings-section p {
      color: var(--muted);
      margin-bottom: 16px;
    }
    .form-group {
      margin-bottom: 16px;
    }
    .form-group label {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--text);
      font-weight: 500;
      cursor: pointer;
    }
    .form-group input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
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
