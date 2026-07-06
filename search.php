<?php
require_once 'db.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    requireLogin();
    $action = $_POST['action'];
    $userId = getCurrentUserId();
    $redirectQuery = isset($_GET['q']) ? '?q=' . urlencode($_GET['q']) : '';

    if ($action === 'follow' && !empty($_POST['following_id'])) {
        followUser($userId, (int) $_POST['following_id']);
        header('Location: search.php' . $redirectQuery);
        exit;
    }

    if ($action === 'unfollow' && !empty($_POST['following_id'])) {
        unfollowUser($userId, (int) $_POST['following_id']);
        header('Location: search.php' . $redirectQuery);
        exit;
    }
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = $query !== '' ? searchUsers($query) : [];
$currentUserId = getCurrentUserId();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Zoeken - Instant</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="script.js" defer></script>
</head>
<body data-theme="light">
  <header class="navbar">
    <a href="index.php" class="navbar-brand-link">
      <div class="navbar-brand">
        <svg class="logo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
          <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
          <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
        </svg>
        <span class="brand-text">Instant</span>
      </div>
    </a>
    <form action="search.php" method="get" class="navbar-search">
      <input type="search" name="q" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Zoek accounts..." class="search-input" autocomplete="off">
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
      <button class="nav-icon" aria-label="Nieuwe post" <?php echo isLoggedIn() ? "onclick=\"window.location.href='index.php'\"" : "onclick=\"window.location.href='login.php'\""; ?>>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          <line x1="12" y1="8" x2="12" y2="16"/>
          <line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
      </button>
      <button class="nav-icon" aria-label="Ontdek">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>
      </button>
      <?php if (isLoggedIn()): ?>
      <button class="nav-icon profile-btn" aria-label="Profiel" onclick="window.location.href='profile.php'">
        <div class="profile-avatar-small" style="--avatar-color: <?php echo getAvatarColor($_SESSION['username']); ?>;">
          <?php echo htmlspecialchars(strtoupper(mb_substr($_SESSION['username'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
        </div>
      </button>
      <?php else: ?>
      <a href="login.php" class="nav-icon" style="text-decoration: none; color: var(--text); font-weight: 600; font-size: 14px; display: flex; align-items: center;">Inloggen</a>
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
    </div>
  </header>

  <main class="search-page">
    <div class="search-page-inner">
    <div class="search-page-header">
      <h1>Zoek accounts</h1>
      <p>Vind gebruikers op gebruikersnaam of e-mailadres.</p>
    </div>

    <form action="search.php" method="get" class="search-page-form">
      <div class="search-page-input-wrap">
        <input type="search" name="q" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Zoek op gebruikersnaam..." class="search-page-input" autofocus>
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
    </form>

    <?php if ($query === ''): ?>
    <div class="search-empty">
      <svg class="search-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
      <h2>Waar ben je naar op zoek?</h2>
      <p>Typ een gebruikersnaam in de zoekbalk om accounts te vinden.</p>
    </div>
    <?php elseif (empty($results)): ?>
    <div class="search-empty">
      <svg class="search-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      <h2>Geen resultaten</h2>
      <p>Er zijn geen accounts gevonden voor "<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>".</p>
    </div>
    <?php else: ?>
    <div class="search-results">
      <?php foreach ($results as $account): ?>
      <?php
        $initial = strtoupper(mb_substr($account['username'], 0, 1));
        $isFollowing = $currentUserId && isFollowing($currentUserId, (int) $account['id']);
        $isSelf = $currentUserId && (int) $account['id'] === $currentUserId;
      ?>
      <div class="search-result-item">
        <a href="profile.php?user_id=<?php echo (int) $account['id']; ?>" class="search-result-link">
          <div class="search-result-avatar" style="--avatar-color: <?php echo getAvatarColor($account['username']); ?>;"><?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="search-result-info">
            <span class="search-result-username"><?php echo htmlspecialchars($account['username'], ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="search-result-meta">
              <?php echo (int) $account['post_count']; ?> posts · <?php echo htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
          </div>
        </a>
        <?php if (isLoggedIn() && !$isSelf): ?>
        <div class="search-result-action">
          <form method="post">
            <input type="hidden" name="action" value="<?php echo $isFollowing ? 'unfollow' : 'follow'; ?>" />
            <input type="hidden" name="following_id" value="<?php echo (int) $account['id']; ?>" />
            <button type="submit" class="follow-btn"><?php echo $isFollowing ? 'Gevolgd' : 'Volgen'; ?></button>
          </form>
        </div>
        <?php elseif (!isLoggedIn()): ?>
        <div class="search-result-action">
          <button type="button" class="follow-btn" onclick="window.location.href='login.php'">Volgen</button>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    </div>
  </main>

  <nav class="mobile-nav">
    <a href="index.php" class="mobile-nav-btn">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
    </a>
    <button class="mobile-nav-btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </button>
    <button class="mobile-nav-btn" <?php echo isLoggedIn() ? "onclick=\"window.location.href='index.php'\"" : "onclick=\"window.location.href='login.php'\""; ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
    </button>
    <button class="mobile-nav-btn active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    </button>
    <?php if (isLoggedIn()): ?>
    <button class="mobile-nav-btn" onclick="window.location.href='profile.php'">
      <div class="mobile-avatar"></div>
    </button>
    <?php else: ?>
    <a href="login.php" class="mobile-nav-btn" style="text-decoration: none; color: var(--text); font-size: 12px; display: flex; align-items: center; justify-content: center;">Inloggen</a>
    <?php endif; ?>
  </nav>
</body>
</html>
