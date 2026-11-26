<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_citizen();
$citizen = current_citizen();
$pageTitle = 'My Reports';
$stmt = db()->prepare('SELECT id, reference_no, vehicle_number, violation_type, location_text, status, created_at FROM reports WHERE citizen_id = ? OR LOWER(reporter_email) = LOWER(?) ORDER BY created_at DESC');
$stmt->execute([(int)$citizen['id'], $citizen['email']]);
$reports = $stmt->fetchAll();
$counts = db()->prepare('SELECT COUNT(*) FROM challans WHERE citizen_id = ?');
$counts->execute([(int)$citizen['id']]);
$challanCount = (int)$counts->fetchColumn();
require __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-heading"><div><span class="eyebrow">CITIZEN ACCOUNT</span><h1>Hello, <?= e(explode(' ', $citizen['full_name'])[0]) ?></h1><p>View your submitted reports and official notice records.</p></div><a class="btn btn-primary" href="<?= e(url('report.php')) ?>">New report →</a></div>
<section class="dashboard-stats"><div class="metric-card"><span>My reports</span><strong><?= count($reports) ?></strong><small>All submitted records</small></div><div class="metric-card metric-blue"><span>Notices / challans</span><strong><?= $challanCount ?></strong><small>Issued for your account</small></div></section>
<section class="panel table-panel"><div class="panel-heading"><div><h2>My report history</h2><p>Newest submissions first</p></div></div><?php if (!$reports): ?><div class="empty-state"><span>＋</span><h3>No reports yet</h3><p>Submit a traffic-safety concern to begin.</p></div><?php else: ?><div class="table-scroll"><table><thead><tr><th>Reference</th><th>Violation</th><th>Location</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($reports as $row): ?><tr><td><strong><?= e($row['reference_no']) ?></strong><small><?= e($row['vehicle_number']) ?></small></td><td><?= e($row['violation_type']) ?></td><td><?= e($row['location_text']) ?></td><td><span class="status-pill <?= e(status_class($row['status'])) ?>"><?= e($row['status']) ?></span></td><td><a class="table-action" href="<?= e(url('track.php')) ?>">Track →</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
