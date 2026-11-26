<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_permission('audit.view');
$pageTitle='General Audit Logs';
$logs=db()->query('SELECT a.*, u.full_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC, a.id DESC LIMIT 500')->fetchAll();
require __DIR__.'/../includes/header.php';
?>
<div class="dashboard-heading"><div><span class="eyebrow">SECURITY RECORD</span><h1>General audit logs</h1><p>Administrative, citizen and system actions recorded for accountability.</p></div></div>
<section class="panel table-panel"><div class="table-heading-line"><h2>Recent events <span class="count-chip"><?=count($logs)?></span></h2></div><?php if(!$logs):?><div class="empty-state"><h3>No audit events yet</h3></div><?php else:?><div class="table-scroll"><table><thead><tr><th>Time</th><th>Actor</th><th>Action</th><th>Entity</th><th>Details</th></tr></thead><tbody><?php foreach($logs as $log):?><tr><td><?=e(format_date($log['created_at']))?></td><td><?=e($log['full_name'] ?: ($log['actor_role'] ?: 'System'))?><small><?=e($log['ip_address'])?></small></td><td><strong><?=e($log['action'])?></strong></td><td><?=e($log['entity_type'])?><?= $log['entity_id']?' #'.e($log['entity_id']):'' ?></td><td><?=e($log['details'])?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></section>
<?php require __DIR__.'/../includes/footer.php'; ?>
