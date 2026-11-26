<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
require_permission('reports.update');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin/reports.php');
verify_csrf();
$id = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
$status = trim((string)($_POST['status'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));
$penaltyInput = trim((string)($_POST['penalty_amount'] ?? ''));
$statuses = ['Submitted', 'Under Review', 'Verified', 'Rejected', 'Closed'];
if (!$id || $id < 1 || !in_array($status, $statuses, true) || mb_strlen($notes) < 3 || mb_strlen($notes) > 1500) {
    flash('error', 'Please provide a valid report, status, and reviewer remark (3–1,500 characters).');
    redirect('admin/reports.php');
}
$penalty = null;
if ($penaltyInput !== '') {
    if (!is_numeric($penaltyInput) || (float)$penaltyInput < 0 || (float)$penaltyInput > 1000000) {
        flash('error', 'The illustrative penalty estimate must be between ₹0 and ₹1,000,000.');
        redirect('admin/view_report.php?id=' . $id);
    }
    $penalty = number_format((float)$penaltyInput, 2, '.', '');
}
try {
    $pdo = db(); $pdo->beginTransaction();
    $lookup = $pdo->prepare('SELECT status, citizen_id, reporter_email, reference_no FROM reports WHERE id = ?');
    $lookup->execute([$id]); $old = $lookup->fetch();
    if (!$old) { $pdo->rollBack(); flash('error', 'Report not found.'); redirect('admin/reports.php'); }
    $update = $pdo->prepare('UPDATE reports SET status = ?, officer_notes = ?, penalty_amount = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $update->execute([$status, $notes, $penalty, $id]);
    if ($old['status'] !== $status) {
        $history = $pdo->prepare('INSERT INTO report_status_history (report_id, old_status, new_status, notes, changed_by, changed_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)');
        $history->execute([$id, $old['status'], $status, $notes, (int)current_admin()['id']]);
    } else {
        $history = $pdo->prepare('INSERT INTO report_status_history (report_id, old_status, new_status, notes, changed_by, changed_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)');
        $history->execute([$id, $old['status'], $status, 'Review note updated: ' . $notes, (int)current_admin()['id']]);
    }
    $pdo->commit();
    audit_log('report.status_update', 'report', (string)$id, 'Status changed from ' . $old['status'] . ' to ' . $status);
    if (!empty($old['citizen_id'])) {
        notify_user((int)$old['citizen_id'], 'Report status updated', 'Report ' . $old['reference_no'] . ' is now ' . $status . '.', 'track.php', $id);
    }
    flash('success', 'Report review updated successfully.');
    redirect('admin/view_report.php?id=' . $id);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Report update failed: ' . $exception->getMessage());
    flash('error', 'The report could not be updated. Please try again.');
    redirect('admin/view_report.php?id=' . $id);
}
