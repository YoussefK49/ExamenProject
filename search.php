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
  <?php include 'nav.php'; ?>

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

</body>
</html>
