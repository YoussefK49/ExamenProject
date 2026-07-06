<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$searchQuery = htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8');
$isLoggedIn = isLoggedIn();

function navActive($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}
?>
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
    <input type="search" name="q" value="<?php echo $searchQuery; ?>" placeholder="Zoek accounts..." class="search-input" autocomplete="off">
    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"></circle>
      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
  </form>

  <button class="menu-toggle" id="menuToggle" aria-label="Open menu" type="button">
    <span></span>
    <span></span>
    <span></span>
  </button>

  <div class="navbar-icons">
    <button class="nav-icon" type="button" aria-label="Berichten">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
    </button>
    <?php if ($isLoggedIn): ?>
      <div class="profile-dropdown">
        <button class="nav-icon profile-btn dropdown-toggle <?php echo navActive('profile.php', $currentPage); ?>" type="button" id="profileDropdownToggle" aria-haspopup="true" aria-expanded="false" aria-label="Menu">
          <div class="profile-avatar-small"></div>
        </button>
        <div class="dropdown-menu" id="profileDropdownMenu" role="menu" aria-labelledby="profileDropdownToggle">
          <a href="index.php" class="dropdown-item" role="menuitem">Home</a>
          <a href="search.php" class="dropdown-item" role="menuitem">Ontdek</a>
          <a href="index.php" class="dropdown-item" role="menuitem">Nieuwe post</a>
          <a href="likes.php" class="dropdown-item" role="menuitem">Geliked</a>
          <a href="profile.php" class="dropdown-item" role="menuitem">Profiel</a>
          <div class="dropdown-divider"></div>
          <a href="settings.php" class="dropdown-item" role="menuitem">Instellingen</a>
          <button type="button" class="dropdown-item dropdown-action" id="dropdownThemeToggle">Wissel thema</button>
          <a href="logout.php" class="dropdown-item" role="menuitem">Uitloggen</a>
        </div>
      </div>
    <?php else: ?>
      <a href="login.php" class="nav-icon nav-login" aria-label="Inloggen">Inloggen</a>
    <?php endif; ?>
  </div>
</header>

<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
  <div class="mobile-menu-panel">
    <div class="mobile-menu-header">
      <div class="brand-inline">
        <span class="brand-dot"></span>
        <span>Instant</span>
      </div>
      <button class="menu-close" id="menuClose" type="button" aria-label="Sluit menu">×</button>
    </div>
    <nav class="mobile-menu-nav">
      <a href="index.php" class="mobile-menu-link <?php echo navActive('index.php', $currentPage); ?>">Home</a>
      <a href="search.php" class="mobile-menu-link <?php echo navActive('search.php', $currentPage); ?>">Ontdek</a>
      <a href="index.php" class="mobile-menu-link">Nieuwe post</a>
      <?php if ($isLoggedIn): ?>
        <a href="likes.php" class="mobile-menu-link <?php echo navActive('likes.php', $currentPage); ?>">Geliked</a>
        <a href="profile.php" class="mobile-menu-link <?php echo navActive('profile.php', $currentPage); ?>">Profiel</a>
        <a href="logout.php" class="mobile-menu-link">Uitloggen</a>
      <?php else: ?>
        <a href="login.php" class="mobile-menu-link">Inloggen</a>
      <?php endif; ?>
    </nav>
  </div>
</div>
