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
    <a href="index.php" class="nav-icon <?php echo navActive('index.php', $currentPage); ?>" aria-label="Home">
      <svg viewBox="0 0 24 24" fill="currentColor">
        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
      </svg>
    </a>
    <button class="nav-icon" type="button" aria-label="Berichten">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
    </button>
    <a href="index.php" class="nav-icon" aria-label="Nieuwe post">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
        <line x1="12" y1="8" x2="12" y2="16"/>
        <line x1="8" y1="12" x2="16" y2="12"/>
      </svg>
    </a>
    <a href="search.php" class="nav-icon <?php echo navActive('search.php', $currentPage); ?>" aria-label="Ontdek">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
      </svg>
    </a>
    <?php if ($isLoggedIn): ?>
      <a href="likes.php" class="nav-icon <?php echo navActive('likes.php', $currentPage); ?>" aria-label="Geliked">
        <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
      </a>
      <div class="profile-dropdown">
        <button class="nav-icon profile-btn dropdown-toggle <?php echo navActive('profile.php', $currentPage); ?>" type="button" id="profileDropdownToggle" aria-haspopup="true" aria-expanded="false" aria-label="Profiel">
          <div class="profile-avatar-small"></div>
        </button>
        <div class="dropdown-menu" id="profileDropdownMenu" role="menu" aria-labelledby="profileDropdownToggle">
          <a href="profile.php" class="dropdown-item" role="menuitem">Profiel</a>
          <a href="settings.php" class="dropdown-item" role="menuitem">Instellingen</a>
          <a href="logout.php" class="dropdown-item" role="menuitem">Uitloggen</a>
        </div>
      </div>
    <?php else: ?>
      <a href="login.php" class="nav-icon nav-login" aria-label="Inloggen">Inloggen</a>
    <?php endif; ?>
    <button id="themeToggle" class="nav-icon" aria-label="Wissel thema" type="button">
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
