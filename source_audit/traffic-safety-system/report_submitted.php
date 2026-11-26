<?php
require_once __DIR__ . '/includes/bootstrap.php';
$confirmation = $_SESSION['new_report_confirmation'] ?? null;
unset($_SESSION['new_report_confirmation']);
if (!$confirmation) redirect('report.php');
$pageTitle = 'Report Submitted';
require __DIR__ . '/includes/header.php';
?>
<section class="success-page">
    <div class="success-check">✓</div><span class="eyebrow">SUBMISSION RECEIVED</span>
    <h1>Your report has been submitted.</h1>
    <p>The information has been stored in the project system with an initial status of <strong>Submitted</strong>. It has not yet been verified.</p>
    <div class="reference-card"><span>YOUR REPORT REFERENCE</span><strong><?= e($confirmation['reference_no']) ?></strong><button type="button" class="btn btn-secondary btn-small" data-copy="<?= e($confirmation['reference_no']) ?>">Copy reference</button></div>
    <div class="info-grid"><div><small>Tracking email</small><strong><?= e($confirmation['reporter_email']) ?></strong></div><div><small>Initial status</small><strong><span class="status-pill status-submitted">Submitted</span></strong></div></div>
    <div class="alert alert-info">Save the reference number. To track the report, you must provide this number and the same email address used in the form.</div>
    <div class="hero-actions centered-actions"><a class="btn btn-primary" href="<?= e(url('track.php')) ?>">Track this report →</a><a class="btn btn-light" href="<?= e(url()) ?>">Back to home</a></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
