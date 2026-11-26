<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Track a Report';
$report = null;
$notFound = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $reference = strtoupper(trim((string)($_POST['reference_no'] ?? '')));
    $email = trim((string)($_POST['reporter_email'] ?? ''));
    if (!preg_match('/^TSR-[0-9]{6}-[A-F0-9]{6}$/', $reference) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $notFound = true;
    } else {
        $statement = db()->prepare('SELECT reference_no, reporter_name, reporter_email, vehicle_number, violation_type, location_text, incident_at, description, status, officer_notes, created_at, updated_at FROM reports WHERE reference_no = :ref AND LOWER(reporter_email) = LOWER(:email) LIMIT 1');
        $statement->execute(['ref' => $reference, 'email' => $email]);
        $report = $statement->fetch() ?: null;
        $notFound = !$report;
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><span class="eyebrow">REPORT TRACKING</span><h1>Track your report</h1><p>Enter your report reference number and the email address used when submitting it.</p></div>
<div class="track-layout">
    <section class="panel form-panel track-form-panel"><div class="panel-heading"><div><h2>Find a report</h2><p>Your reference is in the submission confirmation page.</p></div><span class="panel-step">LOOKUP</span></div>
        <form method="post" action="<?= e(url('track.php')) ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="form-field"><label for="reference_no">Report reference number *</label><input id="reference_no" name="reference_no" type="text" placeholder="TSR-261017-A1B2C3" pattern="TSR-[0-9]{6}-[A-Fa-f0-9]{6}" required value="<?= e($_POST['reference_no'] ?? '') ?>"></div>
            <div class="form-field"><label for="reporter_email">Email address used in report *</label><input id="reporter_email" name="reporter_email" type="email" required value="<?= e($_POST['reporter_email'] ?? '') ?>"></div>
            <button class="btn btn-primary btn-wide" type="submit">Check Status →</button>
        </form>
        <?php if ($notFound): ?><div class="alert alert-error track-error">No matching report was found. Check the reference number and email address, then try again.</div><?php endif; ?>
    </section>
    <aside class="side-card"><span class="privacy-icon">⌕</span><h3>Where do I find my reference?</h3><p>It is displayed after a report is submitted. The reference and email address must both match to show the report details.</p><div class="timeline-mini"><span>Submitted</span><span>Under Review</span><span>Verified / Rejected</span><span>Closed</span></div></aside>
</div>
<?php if ($report): ?>
<section class="panel result-panel"><div class="result-top"><div><span class="eyebrow">MATCH FOUND</span><h2><?= e($report['reference_no']) ?></h2></div><span class="status-pill <?= e(status_class($report['status'])) ?>"><?= e($report['status']) ?></span></div>
    <div class="details-grid"><div><small>Vehicle number</small><strong><?= e($report['vehicle_number']) ?></strong></div><div><small>Violation category</small><strong><?= e($report['violation_type']) ?></strong></div><div><small>Location</small><strong><?= e($report['location_text']) ?></strong></div><div><small>Submitted</small><strong><?= e(format_date($report['created_at'])) ?></strong></div><div><small>Incident time</small><strong><?= e(format_date($report['incident_at'])) ?></strong></div><div><small>Last updated</small><strong><?= e(format_date($report['updated_at'])) ?></strong></div></div>
    <div class="detail-description"><small>Description</small><p><?= nl2br(e($report['description'])) ?></p></div>
    <?php if (!empty($report['officer_notes'])): ?><div class="review-note"><strong>Reviewer update</strong><p><?= nl2br(e($report['officer_notes'])) ?></p></div><?php endif; ?>
    <p class="muted-text">A status is a project workflow update and does not itself constitute an official legal finding.</p>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
