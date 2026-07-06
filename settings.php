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
  <?php include 'nav.php'; ?>

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
  </style>
</body>
</html>
