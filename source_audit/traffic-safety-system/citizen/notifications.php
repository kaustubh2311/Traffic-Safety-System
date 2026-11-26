<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_citizen();
$citizen = current_citizen();
$pageTitle = 'Notifications';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([(int)$citizen['id']]);
    audit_log('notifications.read_all', 'notification', null, 'All notifications marked read');
    flash('success', 'All notifications marked as read.');
    redirect('citizen/notifications.php');
}
$stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC');
$stmt->execute([(int)$citizen['id']]);
$notifications = $stmt->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-heading"><div><span class="eyebrow">IN-APP UPDATES</span><h1>Notifications</h1><p>Status changes and official notice updates.</p></div><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="btn btn-secondary" type="submit">Mark all read</button></form></div>
<section class="panel table-panel"><div class="notification-list"><?php if (!$notifications): ?><div class="empty-state"><span>✓</span><h3>No notifications</h3><p>You are all caught up.</p></div><?php else: foreach ($notifications as $item): ?><article class="notification-item <?= $item['is_read'] ? '' : 'notification-unread' ?>"><div><strong><?= e($item['title']) ?></strong><p><?= e($item['message']) ?></p><small><?= e(format_date($item['created_at'])) ?></small></div><?php if ($item['link']): ?><a class="table-action" href="<?= e(url($item['link'])) ?>">Open →</a><?php endif; ?></article><?php endforeach; endif; ?></div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
