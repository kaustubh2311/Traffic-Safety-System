<?php
$pageTitle = $pageTitle ?? 'Traffic Safety System';
$admin = current_admin();
$citizen = current_citizen();
$unreadNotifications = 0;
if ($citizen) {
    try {
        $unreadNotifications = (int)db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0')->execute([(int)$citizen['id']]) ?: 0;
        $notificationCount = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $notificationCount->execute([(int)$citizen['id']]);
        $unreadNotifications = (int)$notificationCount->fetchColumn();
    } catch (Throwable $exception) {
        $unreadNotifications = 0;
    }
}
$successMessage = flash('success');
$errorMessage = flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#102b52">
    <title><?= e($pageTitle) ?> | Team 6</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script defer src="<?= e(url('assets/js/main.js')) ?>"></script>
</head>
<body>
<header class="site-header">
    <div class="nav-wrap">
        <a class="brand" href="<?= e(url()) ?>" aria-label="Traffic Safety System home">
            <span class="brand-mark">T6</span>
            <span><strong>TrafficSafe</strong><small>Team 6 project prototype</small></span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">☰</button>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="<?= e(url()) ?>">Home</a>
            <a href="<?= e(url('report.php')) ?>">Report a Violation</a>
            <a href="<?= e(url('track.php')) ?>">Track Report</a>
            <?php if ($admin): ?>
                <a href="<?= e(url('admin/dashboard.php')) ?>">Dashboard</a>
                <a href="<?= e(url('admin/reports.php')) ?>">Manage Reports</a>
                <?php if (($admin['role'] ?? '') === 'admin'): ?>
                    <a href="<?= e(url('admin/rules.php')) ?>">Rules</a>
                    <a href="<?= e(url('admin/challans.php')) ?>">Challans</a>
                <?php endif; ?>
                <a class="nav-cta" href="<?= e(url('admin/logout.php')) ?>">Sign Out</a>
            <?php elseif ($citizen): ?>
                <a href="<?= e(url('citizen/dashboard.php')) ?>">My Account</a>
                <a href="<?= e(url('citizen/notifications.php')) ?>">Notifications<?= $unreadNotifications ? ' (' . $unreadNotifications . ')' : '' ?></a>
                <a class="nav-cta" href="<?= e(url('citizen/logout.php')) ?>">Sign Out</a>
            <?php else: ?>
                <a href="<?= e(url('citizen/login.php')) ?>">Citizen Login</a>
                <a class="nav-cta" href="<?= e(url('admin/login.php')) ?>">Authority Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="page-shell">
    <?php if ($successMessage): ?><div class="alert alert-success" role="status"><?= e($successMessage) ?></div><?php endif; ?>
    <?php if ($errorMessage): ?><div class="alert alert-error" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
