<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_permission('challans.manage');
$pageTitle = 'Notice & Challan Workflow';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action=(string)$_POST['action'];
    if ($action==='create') {
        $reportId=(int)$_POST['report_id']; $ruleId=(int)$_POST['rule_id']; $status=(string)$_POST['status'];
        $report=db()->prepare('SELECT r.*, u.id AS citizen_id FROM reports r LEFT JOIN users u ON u.id=r.citizen_id WHERE r.id=?'); $report->execute([$reportId]); $row=$report->fetch();
        $rule=db()->prepare('SELECT * FROM offence_rules WHERE id=?'); $rule->execute([$ruleId]); $ruleRow=$rule->fetch();
        if (!$row || !$ruleRow) flash('error','Select a valid report and offence rule.');
        else {
            $challanNo='CHN-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(4)));
            $amount=(float)$ruleRow['default_penalty']; $notice=trim((string)$_POST['notice_text']);
            $issued=$status==='Issued'?date('Y-m-d H:i:s'):null; $due=$status==='Issued'?date('Y-m-d H:i:s',strtotime('+30 days')):null;
            $stmt=db()->prepare('INSERT INTO challans (challan_no, report_id, rule_id, citizen_id, vehicle_number, offence_name, amount, status, issued_at, due_at, notice_text, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$challanNo,$reportId,$ruleId,$row['citizen_id'],$row['vehicle_number'],$ruleRow['name'],$amount,$status,$issued,$due,$notice,current_admin()['id']]);
            $challanId=(int)db()->lastInsertId(); audit_log('challan.issue','challan',(string)$challanId,'Official workflow notice '.$status);
            if ($row['citizen_id']) notify_user((int)$row['citizen_id'],'Notice / challan '.$challanNo,$status==='Issued'?'An official workflow notice has been issued.':'A notice draft was created for review.','citizen/dashboard.php',$reportId,$challanId);
            flash('success','Notice/challan record '.$challanNo.' created.');
        }
    }
    redirect('admin/challans.php');
}
$reports=db()->query("SELECT id,reference_no,vehicle_number,violation_type,status FROM reports ORDER BY created_at DESC LIMIT 250")->fetchAll();
$rules=db()->query('SELECT id,name,default_penalty FROM offence_rules WHERE active=1 ORDER BY name')->fetchAll();
$challans=db()->query('SELECT c.*, r.reference_no FROM challans c JOIN reports r ON r.id=c.report_id ORDER BY c.created_at DESC')->fetchAll();
require __DIR__.'/../includes/header.php';
?>
<div class="dashboard-heading"><div><span class="eyebrow">OFFICIAL WORKFLOW</span><h1>Notice / challan management</h1><p>Create draft or issued notice records. No payment gateway or legal authority is implied.</p></div></div>
<div class="review-layout"><section class="panel table-panel"><div class="panel-heading"><div><h2>Notice register</h2><p>Prototype official-workflow records</p></div></div><?php if(!$challans):?><div class="empty-state"><span>§</span><h3>No notices yet</h3></div><?php else:?><div class="table-scroll"><table><thead><tr><th>Notice</th><th>Report</th><th>Vehicle / offence</th><th>Amount</th><th>Status</th></tr></thead><tbody><?php foreach($challans as $c):?><tr><td><strong><?=e($c['challan_no'])?></strong></td><td><?=e($c['reference_no'])?></td><td><?=e($c['vehicle_number'])?><small><?=e($c['offence_name'])?></small></td><td>₹<?=number_format((float)$c['amount'],2)?></td><td><span class="status-pill"><?=e($c['status'])?></span></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></section>
<aside class="panel update-panel"><div class="panel-heading"><div><h2>Create notice</h2></div></div><form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="create"><div class="form-field"><label>Verified report</label><select name="report_id" required><option value="">Choose report</option><?php foreach($reports as $r):?><option value="<?= (int)$r['id'] ?>"><?=e($r['reference_no'].' — '.$r['vehicle_number'].' ('.$r['status'].')')?></option><?php endforeach;?></select></div><div class="form-field"><label>Offence rule</label><select name="rule_id" required><option value="">Choose rule</option><?php foreach($rules as $r):?><option value="<?= (int)$r['id'] ?>"><?=e($r['name'].' — ₹'.number_format((float)$r['default_penalty'],2))?></option><?php endforeach;?></select></div><div class="form-field"><label>Record status</label><select name="status"><option>Draft</option><option>Issued</option></select></div><div class="form-field"><label>Notice text</label><textarea name="notice_text" rows="4" maxlength="3000" placeholder="Describe the formal notice context used by the authorised authority."></textarea></div><button class="btn btn-primary btn-wide" data-confirm="Create this notice record?">Create record</button></form></aside></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
