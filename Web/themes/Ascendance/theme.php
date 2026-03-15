<?php
/**
 * Ascendance Theme for DT Web 2.0
 * Drop-in replacement theme — works with existing loader.php routing
 * Place this file at: /themes/Ascendance/theme.php
 * Then set theme = 'Ascendance' in DTweb_settings table
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header("Location:../../error.php");
    exit();
}

require $_SERVER['DOCUMENT_ROOT'] . "/inc/loader.php";

$set         = web_settings();
$current_page = isset($_GET['p']) ? $_GET['p'] : 'home';
$is_logged   = logged();
$is_admin    = ($is_logged) ? check_admin($is_logged) : false;

// Server name from DB settings
$server_name = $set[4] ?? 'MU Ascendance';

// Active page helper
function asc_active($pages, $cp) {
    return in_array($cp, (array)$pages) ? ' nav-active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?php echo htmlspecialchars(base64_decode($set[2] ?? '')); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(base64_decode($set[1] ?? '')); ?>">
<title><?php echo htmlspecialchars($server_name); ?> — <?php echo htmlspecialchars(base64_decode($set[0] ?? 'MU Online')); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&family=Cinzel:wght@400;500;600;700&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/themes/Ascendance/css/ascendance.css">
</head>
<body>

<!-- ═══════════════════════════════════════════
     HEADER / NAV
════════════════════════════════════════════ -->
<header class="asc-header" id="asc-header">
  <div class="asc-header-inner">

    <!-- Logo -->
    <a href="?p=home" class="asc-logo">
      <div class="asc-logo-emblem">M</div>
      <div class="asc-logo-text">
        <span class="asc-logo-title"><?php echo htmlspecialchars($server_name); ?></span>
        <span class="asc-logo-sub"><?php echo htmlspecialchars(base64_decode($set[7] ?? 'Season VI')); ?></span>
      </div>
    </a>

    <!-- Desktop Nav -->
    <nav class="asc-nav">
      <a href="?p=home"        class="asc-nav-link<?php echo asc_active('home', $current_page); ?>">News</a>
      <a href="?p=information" class="asc-nav-link<?php echo asc_active('information', $current_page); ?>">Server Info</a>

      <!-- Rankings dropdown -->
      <div class="asc-dropdown<?php echo asc_active(['topchars','topguilds','topkillers','topelf','topdl','topmg','topbk','topsm','hof','onlinenow','topbc','topds','topsky','mostonline'], $current_page); ?>">
        <span class="asc-nav-link asc-dropdown-toggle">Rankings ▾</span>
        <div class="asc-dropdown-menu">
          <a href="?p=topchars">◆ Top Characters</a>
          <a href="?p=topguilds">◆ Top Guilds</a>
          <a href="?p=topkillers">◆ Top Killers</a>
          <a href="?p=hof">◆ Hall of Fame</a>
          <a href="?p=onlinenow">◆ Online Now</a>
          <div class="asc-dropdown-divider"></div>
          <a href="?p=topbc">◆ Blood Castle</a>
          <a href="?p=topds">◆ Devil Square</a>
          <a href="?p=topsky">◆ Sky Event</a>
        </div>
      </div>

      <a href="?p=market"  class="asc-nav-link<?php echo asc_active('market', $current_page); ?>">Market</a>
      <a href="?p=auction" class="asc-nav-link<?php echo asc_active('auction', $current_page); ?>">Auction</a>
      <a href="?p=files"   class="asc-nav-link<?php echo asc_active('files', $current_page); ?>">Download</a>

      <?php if ($is_logged): ?>
        <!-- Logged in nav -->
        <div class="asc-dropdown">
          <span class="asc-nav-link asc-dropdown-toggle asc-nav-user">⚔ <?php echo htmlspecialchars($is_logged); ?> ▾</span>
          <div class="asc-dropdown-menu">
            <a href="?p=characters">◆ My Characters</a>
            <a href="?p=accdetails">◆ Account Details</a>
            <a href="?p=bank">◆ Zen Bank</a>
            <a href="?p=storage">◆ Storage</a>
            <a href="?p=buycredits">◆ Buy Credits</a>
            <?php if ($is_admin): ?>
            <div class="asc-dropdown-divider"></div>
            <a href="?p=general" style="color:#e8cc7a;">⚙ Admin Panel</a>
            <?php endif; ?>
            <div class="asc-dropdown-divider"></div>
            <a href="?p=login&logout=1" style="color:#e88a8a;">✕ Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="#" class="asc-nav-link" onclick="ascOpenModal('login'); return false;">Login</a>
        <a href="?p=register" class="asc-nav-link asc-nav-cta<?php echo asc_active('register', $current_page); ?>">Register</a>
      <?php endif; ?>
    </nav>

    <!-- Mobile hamburger -->
    <button class="asc-hamburger" onclick="ascToggleMobileNav()" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<!-- Mobile Nav -->
<div class="asc-mobile-nav" id="asc-mobile-nav">
  <a href="?p=home">News</a>
  <a href="?p=information">Server Info</a>
  <a href="?p=topchars">Rankings</a>
  <a href="?p=market">Market</a>
  <a href="?p=auction">Auction</a>
  <a href="?p=files">Download</a>
  <?php if ($is_logged): ?>
    <a href="?p=characters">My Characters</a>
    <a href="?p=accdetails">Account Details</a>
    <a href="?p=login&logout=1">Logout</a>
  <?php else: ?>
    <a href="#" onclick="ascOpenModal('login')">Login</a>
    <a href="?p=register" style="color:var(--gold)">Register</a>
  <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════
     MAIN CONTENT WRAPPER
════════════════════════════════════════════ -->
<div class="asc-page-wrap">

  <?php
  // ── HOME PAGE: show hero before content ──
  if ($current_page === 'home'): ?>

  <section class="asc-hero">
    <div class="asc-hero-bg"></div>
    <div class="asc-particles" id="asc-particles"></div>
    <div class="asc-hero-content">
      <div class="asc-hero-badge">◆ Now Live — <?php echo htmlspecialchars(base64_decode($set[7] ?? 'Season VI')); ?> ◆</div>
      <h1 class="asc-hero-title"><?php echo htmlspecialchars($server_name); ?></h1>
      <p class="asc-hero-sub">Eternal Conquest Awaits</p>
      <div class="asc-divider"><div class="asc-divider-line"></div><div class="asc-divider-gem"></div><div class="asc-divider-line"></div></div>
      <p class="asc-hero-desc">Forge your legend. <?php echo htmlspecialchars(base64_decode($set[9] ?? '')); ?> EXP · <?php echo htmlspecialchars(base64_decode($set[11] ?? '')); ?> Drop · <?php echo htmlspecialchars(base64_decode($set[10] ?? '')); ?> Max Resets</p>
      <div class="asc-hero-actions">
        <a href="?p=register" class="asc-btn asc-btn-primary">⚔ Begin Your Journey</a>
        <a href="?p=files"    class="asc-btn asc-btn-danger">⬇ Download Client</a>
        <a href="?p=information" class="asc-btn asc-btn-secondary">Server Info</a>
      </div>
    </div>

    <!-- Server Stats Strip -->
    <div class="asc-stats-strip">
      <div class="asc-stats-inner">
        <?php
        $online_count = mssql_num_rows(mssql_query("SELECT memb___id FROM MEMB_STAT WHERE ConnectStat >= 1"));
        $total_accounts = mssql_num_rows(mssql_query("SELECT memb_guid FROM MEMB_INFO"));
        $total_chars = mssql_num_rows(mssql_query("SELECT Name FROM Character"));
        ?>
        <div class="asc-stat"><span class="asc-stat-val asc-online"><?php echo number_format($online_count); ?></span><span class="asc-stat-label">Online Now</span></div>
        <div class="asc-stat"><span class="asc-stat-val"><?php echo number_format($total_accounts); ?></span><span class="asc-stat-label">Accounts</span></div>
        <div class="asc-stat"><span class="asc-stat-val"><?php echo number_format($total_chars); ?></span><span class="asc-stat-label">Characters</span></div>
        <div class="asc-stat"><span class="asc-stat-val"><?php echo htmlspecialchars(base64_decode($set[7] ?? 'S6')); ?></span><span class="asc-stat-label">Version</span></div>
        <div class="asc-stat"><span class="asc-stat-val" style="color:#4ade80">⬤</span><span class="asc-stat-label">Server Online</span></div>
      </div>
    </div>
  </section>

  <?php endif; ?>

  <!-- ── CONTENT PANEL ── -->
  <main class="asc-main <?php echo ($current_page === 'home') ? 'asc-main-home' : 'asc-main-inner'; ?>">
    <div class="asc-container">

      <?php
      // ── SIDEBAR LAYOUT: show sidebar only when logged in and on user pages ──
      $sidebar_pages = ['characters','accdetails','bank','storage','warehouse','jewels','buyjewels',
                        'buycredits','buyvip','addstats','pkclear','resetstats','grandreset',
                        'resetcharacter','lotto','changeaccdet','addnews','addbox','general',
                        'bans','auctioned','logs','accountedit'];
      $show_sidebar = $is_logged && in_array($current_page, $sidebar_pages);
      ?>

      <?php if ($show_sidebar): ?>
      <div class="asc-layout-sidebar">
        <aside class="asc-sidebar">
          <?php include($_SERVER['DOCUMENT_ROOT'] . "/inc/user_panel.php"); ?>
        </aside>
        <div class="asc-content-area">
          <?php
          if (isset($active_pages[$current_page])) {
              include($_SERVER['DOCUMENT_ROOT'] . "/mod/" . $active_pages[$current_page]);
          } else {
              echo '<div class="asc-404"><span class="asc-404-code">404</span><p>Page not found.</p><a href="?p=home" class="asc-btn asc-btn-secondary">Return Home</a></div>';
          }
          ?>
        </div>
      </div>

      <?php else: ?>
      <div class="asc-content-full">
        <?php
        if (isset($active_pages[$current_page])) {
            include($_SERVER['DOCUMENT_ROOT'] . "/mod/" . $active_pages[$current_page]);
        } elseif ($current_page !== 'home') {
            echo '<div class="asc-404"><span class="asc-404-code">404</span><p>Page not found.</p><a href="?p=home" class="asc-btn asc-btn-secondary">Return Home</a></div>';
        }
        ?>
      </div>
      <?php endif; ?>

    </div>
  </main>

</div><!-- /asc-page-wrap -->

<!-- ═══════════════════════════════════════════
     FOOTER
════════════════════════════════════════════ -->
<footer class="asc-footer">
  <div class="asc-footer-inner">

    <div class="asc-footer-brand">
      <div class="asc-logo">
        <div class="asc-logo-emblem">M</div>
        <div class="asc-logo-text">
          <span class="asc-logo-title"><?php echo htmlspecialchars($server_name); ?></span>
          <span class="asc-logo-sub"><?php echo htmlspecialchars(base64_decode($set[7] ?? '')); ?></span>
        </div>
      </div>
      <p class="asc-footer-tagline">A private MU Online server. <?php echo htmlspecialchars(base64_decode($set[9] ?? '')); ?>× EXP · <?php echo htmlspecialchars(base64_decode($set[11] ?? '')); ?>× Drop</p>
      <div class="asc-social">
        <?php if (!empty($set[18])): ?><a href="<?php echo htmlspecialchars($set[18]); ?>" class="asc-social-btn" target="_blank" rel="noopener">FB</a><?php endif; ?>
        <?php if (!empty($set[19])): ?><a href="<?php echo htmlspecialchars($set[19]); ?>" class="asc-social-btn" target="_blank" rel="noopener">TW</a><?php endif; ?>
        <a href="?p=files" class="asc-social-btn">DL</a>
      </div>
    </div>

    <div class="asc-footer-col">
      <div class="asc-footer-col-title">Navigate</div>
      <ul class="asc-footer-links">
        <li><a href="?p=home">News</a></li>
        <li><a href="?p=information">Server Info</a></li>
        <li><a href="?p=topchars">Rankings</a></li>
        <li><a href="?p=market">Market</a></li>
        <li><a href="?p=files">Download</a></li>
      </ul>
    </div>

    <div class="asc-footer-col">
      <div class="asc-footer-col-title">Account</div>
      <ul class="asc-footer-links">
        <li><a href="?p=register">Register</a></li>
        <?php if ($is_logged): ?>
          <li><a href="?p=characters">My Characters</a></li>
          <li><a href="?p=accdetails">Account Details</a></li>
          <li><a href="?p=login&logout=1">Logout</a></li>
        <?php else: ?>
          <li><a href="#" onclick="ascOpenModal('login')">Login</a></li>
          <li><a href="?p=retrivepass">Forgot Password</a></li>
        <?php endif; ?>
        <li><a href="?p=buycredits">Buy Credits</a></li>
      </ul>
    </div>

    <div class="asc-footer-col">
      <div class="asc-footer-col-title">Language</div>
      <?php echo lang_form(); ?>
    </div>

  </div>

  <div class="asc-footer-bottom">
    <span>© <?php echo date('Y'); ?> <?php echo htmlspecialchars($server_name); ?>. All rights reserved. MU Online is a trademark of Webzen.</span>
    <span style="color:var(--asc-text-dim);font-size:0.78rem;">Powered by DT Web 2.0 · Ascendance Theme</span>
  </div>
</footer>

<!-- ═══════════════════════════════════════════
     LOGIN MODAL
════════════════════════════════════════════ -->
<div class="asc-modal-overlay" id="asc-modal-login" onclick="ascCloseModalBg(event, 'login')">
  <div class="asc-modal">
    <button class="asc-modal-close" onclick="ascCloseModal('login')">✕</button>
    <div class="asc-modal-header">
      <div class="asc-modal-title">Welcome Back</div>
      <p class="asc-modal-sub">Enter your credentials to continue</p>
    </div>

    <?php
    // Handle login form submission
    if (isset($_POST['login_submit'])) {
        $store = do_login();
        show_messages($store);
    }
    ?>

    <form class="asc-modal-body" method="post" action="?p=login">
      <div class="asc-form-group">
        <label class="asc-form-label">Username</label>
        <input type="text" name="account" class="asc-form-input" placeholder="Your account name" required>
      </div>
      <div class="asc-form-group">
        <label class="asc-form-label">Password</label>
        <input type="password" name="password" class="asc-form-input" placeholder="Your password" required>
      </div>
      <div class="asc-modal-footer">
        <button type="submit" name="login_submit" class="asc-btn asc-btn-primary" style="width:100%;justify-content:center;">Login to Account</button>
        <div class="asc-modal-links">
          <a href="?p=register">Create Account</a>
          <a href="?p=retrivepass">Forgot Password?</a>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="/themes/Ascendance/js/ascendance.js"></script>
</body>
</html>
