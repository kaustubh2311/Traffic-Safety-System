<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_permission('rules.manage');
$pageTitle = 'Offence Rules';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'save') {
        $id = filter_input(INPUT_POST, 'rule_id', FILTER_VALIDATE_INT);
        $name = trim((string)$_POST['name']);
        $description = trim((string)$_POST['description']);
        $category = (string)$_POST['category'];
        $penalty = (float)($_POST['default_penalty'] ?? 0);
        $active = isset($_POST['active']) ? 1 : 0;
        if ($name === '' || mb_strlen($name) > 120 || !in_array($category, ['Minor','Major','Severe'], true) || $penalty < 0) flash('error', 'Enter a valid rule name, category and penalty.');
        else {
            if ($id) { $stmt=db()->prepare('UPDATE offence_rules SET name=?, description=?, category=?, default_penalty=?, active=? WHERE id=?'); $stmt->execute([$name,$description,$category,$penalty,$active,$id]); $entity=(string)$id; }
            else { $stmt=db()->prepare('INSERT INTO offence_rules (name, description, category, default_penalty, active) VALUES (?, ?, ?, ?, ?)'); $stmt->execute([$name,$description,$category,$penalty,$active]); $entity=(string)db()->lastInsertId(); }
            audit_log('offence_rule.save','offence_rule',$entity,'Rule '.$name.' saved'); flash('success','Offence rule saved.');
        }
    } elseif ($action === 'delete') {
        $id=(int)$_POST['rule_id']; if ($id) { db()->prepare('DELETE FROM offence_rules WHERE id=?')->execute([$id]); audit_log('offence_rule.delete','offence_rule',(string)$id,'Rule deleted'); flash('success','Offence rule deleted.'); }
    }
    redirect('admin/rules.php');
}
$rules=db()->query('SELECT * FROM offence_rules ORDER BY active DESC, name')->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-heading"><div><span class="eyebrow">ADMINISTRATION</span><h1>Configurable offence rules</h1><p>Control report categories and their illustrative default penalties.</p></div></div>
<div class="review-layout"><section class="panel table-panel"><div class="panel-heading"><div><h2>Rule register</h2><p>Active rules appear in the public report form.</p></div></div><?php if (!$rules): ?><div class="empty-state"><p>No rules configured.</p></div><?php else: ?><div class="table-scroll"><table><thead><tr><th>Rule</th><th>Category</th><th>Penalty</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rules as $rule): ?><tr><td><strong><?=e($rule['name'])?></strong><small><?=e($rule['description'])?></small></td><td><?=e($rule['category'])?></td><td>₹<?=number_format((float)$rule['default_penalty'],2)?></td><td><?= $rule['active']?'Active':'Disabled' ?></td><td><form method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="rule_id" value="<?= (int)$rule['id'] ?>"><button class="table-action" type="submit" data-confirm="Delete this rule?">Delete</button></form></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></section>
<aside class="panel update-panel"><div class="panel-heading"><div><h2>Add rule</h2><p>Defaults can be used for notices.</p></div></div><form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><div class="form-field"><label>Rule name</label><input name="name" maxlength="120" required></div><div class="form-field"><label>Description</label><textarea name="description" maxlength="255" rows="3"></textarea></div><div class="form-field"><label>Category</label><select name="category"><option>Minor</option><option>Major</option><option>Severe</option></select></div><div class="form-field"><label>Default penalty (₹)</label><input name="default_penalty" type="number" min="0" step="0.01" value="0"></div><label class="check-line"><input type="checkbox" name="active" checked><span>Active and available for reports</span></label><button class="btn btn-primary btn-wide">Save rule</button></form></aside></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
