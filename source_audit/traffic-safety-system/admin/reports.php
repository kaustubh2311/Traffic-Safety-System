<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$pageTitle = 'Manage Reports';
$validStatuses = ['Submitted', 'Under Review', 'Verified', 'Rejected', 'Closed'];
$statusFilter = trim((string)($_GET['status'] ?? ''));
$search = trim((string)($_GET['q'] ?? ''));
$where = [];
$params = [];
if ($statusFilter !== '' && in_array($statusFilter, $validStatuses, true)) { $where[] = 'status = ?'; $params[] = $statusFilter; }
if ($search !== '') { $where[] = '(reference_no LIKE ? OR vehicle_number LIKE ? OR location_text LIKE ? OR violation_type LIKE ?)'; for ($i=0;$i<4;$i++) $params[] = '%' . $search . '%'; }
$sql = 'SELECT id, reference_no, vehicle_number, violation_type, location_text, status, created_at FROM reports' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC LIMIT 250';
$statement = db()->prepare($sql); $statement->execute($params); $reports = $statement->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-heading"><div><span class="eyebrow">CASE REVIEW</span><h1>Manage reports</h1><p>Search submissions and open a record to update its review status.</p></div><a class="btn btn-secondary" href="<?= e(url('admin/analytics.php')) ?>">View analytics</a></div>
<section class="panel table-panel"><form class="filter-bar" method="get"><div class="form-field"><label for="q">Search</label><input id="q" name="q" type="search" placeholder="Reference, vehicle, category, location" value="<?= e($search) ?>"></div><div class="form-field"><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option><?php foreach ($validStatuses as $status): ?><option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div><button class="btn btn-primary" type="submit">Apply filters</button><a class="btn btn-light" href="<?= e(url('admin/reports.php')) ?>">Clear</a></form>
<div class="table-heading-line"><h2>Report register <span class="count-chip"><?= count($reports) ?></span></h2><span class="muted-text">Latest 250 matching records</span></div>
<?php if (!$reports): ?><div class="empty-state"><span>⌕</span><h3>No reports match</h3><p>Try a different search or remove the status filter.</p></div><?php else: ?><div class="table-scroll"><table><thead><tr><th>Reference / vehicle</th><th>Category</th><th>Location</th><th>Submitted</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($reports as $row): ?><tr><td><strong><?= e($row['reference_no']) ?></strong><small><?= e($row['vehicle_number']) ?></small></td><td><?= e($row['violation_type']) ?></td><td><?= e($row['location_text']) ?></td><td><?= e(format_date($row['created_at'])) ?></td><td><span class="status-pill <?= e(status_class($row['status'])) ?>"><?= e($row['status']) ?></span></td><td><a class="table-action" href="<?= e(url('admin/view_report.php?id=' . (int)$row['id'])) ?>">Open →</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
