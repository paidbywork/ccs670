<?php
$current = \App\Core\Security::user();
$role = $current['role'] ?? null;
$title = $title ?? 'Dashboard';
$home = $role === 'admin' ? '/admin' : ($role === 'teacher' ? '/teacher' : ($role === 'student' ? '/student' : '/login'));
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$avatar = $current ? strtoupper(substr(trim($current['name'] ?? 'U'), 0, 1)) : 'C';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#6957e8">
<title><?= e($title) ?> — ClassFlow LMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/assets/app.css?v=1.2" rel="stylesheet">
</head>
<body class="<?= $current ? 'app-authenticated' : 'app-guest' ?>">
<?php if ($current): ?>
<div class="app-shell">
<aside class="app-sidebar" id="appSidebar" aria-label="Primary navigation">
  <a class="brand" href="<?= e($home) ?>" aria-label="ClassFlow home"><span class="brand-mark">✦</span><span class="brand-copy"><strong>ClassFlow</strong><small>Learning Management</small></span></a>
  <div class="sidebar-label">WORKSPACE</div>
  <nav class="side-nav" aria-label="Main menu">
    <a class="side-link <?= $uriPath === $home ? 'active' : '' ?>" href="<?= e($home) ?>"><span class="side-icon">▦</span><span>Dashboard</span></a>
    <?php if ($role === 'admin'): ?>
      <a class="side-link" href="/admin#accounts"><span class="side-icon">♙</span><span>Manage accounts</span></a>
      <a class="side-link" href="/admin#courses"><span class="side-icon">▤</span><span>Courses</span></a>
      <a class="side-link" href="/admin#sections"><span class="side-icon">▥</span><span>Sections</span></a>
    <?php elseif ($role === 'teacher'): ?>
      <a class="side-link <?= str_starts_with($uriPath, '/teacher/sections/') ? 'active' : '' ?>" href="/teacher#my-sections"><span class="side-icon">▤</span><span>My sections</span></a>
      <a class="side-link" href="/teacher#my-sections"><span class="side-icon">▥</span><span>Assignments & materials</span></a>
    <?php elseif ($role === 'student'): ?>
      <a class="side-link <?= str_starts_with($uriPath, '/student/sections/') ? 'active' : '' ?>" href="/student#my-sections"><span class="side-icon">▤</span><span>My courses</span></a>
      <a class="side-link" href="/student#enroll"><span class="side-icon">⊕</span><span>Join a section</span></a>
    <?php endif ?>
  </nav>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-tip"><span class="tip-symbol">✧</span><strong>Keep learning, keep growing.</strong><p>Everything you need for your next milestone.</p></div>
  <div class="sidebar-bottom"><span class="profile-avatar"><?= e($avatar) ?></span><span class="profile-meta"><strong><?= e($current['name']) ?></strong><small><?= e(ucfirst($role)) ?> account</small></span></div>
</aside>
<div class="app-workspace">
  <header class="topbar"><div class="topbar-left"><button class="menu-toggle" id="menuToggle" aria-label="Toggle menu" aria-controls="appSidebar" aria-expanded="false">☰</button><span class="topbar-breadcrumb">Workspace <span>/</span> <?= e(ucfirst($role)) ?></span></div><div class="topbar-right"><span class="topbar-label">ClassFlow LMS</span><span class="topbar-avatar"><?= e($avatar) ?></span><form method="post" action="/logout" class="logout-form"><input type="hidden" name="_csrf" value="<?= csrf() ?>"><button class="btn btn-outline-secondary btn-sm" type="submit">Sign out</button></form></div></header>
  <main class="page-content" id="main-content"><div class="page-heading"><div><div class="eyebrow">CLASSFLOW WORKSPACE</div><h1><?= e($title) ?></h1><p><?= $role === 'admin' ? 'Manage your learning community and keep everything organized.' : ($role === 'teacher' ? 'Manage your classes, materials, and student progress.' : 'Stay on track with your courses and assignments.') ?></p></div><div class="heading-mark">✦</div></div>
<?php else: ?>
<main class="guest-layout" id="main-content">
<?php endif ?>
<?php if ($current && ($flash = ($_SESSION['flash'] ?? null))): unset($_SESSION['flash']); ?><div class="alert alert-info" role="status"><?= e($flash) ?></div><?php endif ?>
