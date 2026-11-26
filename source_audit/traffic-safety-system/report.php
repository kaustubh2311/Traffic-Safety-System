<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Report a Violation';
$reportErrors = $_SESSION['report_errors'] ?? [];
$old = $_SESSION['report_old'] ?? [];
unset($_SESSION['report_errors'], $_SESSION['report_old']);
require __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php
?>
<div class="page-heading"><span class="eyebrow">CITIZEN REPORTING</span><h1>Report a traffic-safety concern</h1><p>Share accurate details so an authorised reviewer can assess the report. Please do not put yourself or others at risk to collect evidence.</p></div>
<div class="form-layout">
    <section class="panel form-panel">
        <?php if ($reportErrors): ?><div class="alert alert-error"><strong>Please correct these items:</strong><ul><?php foreach ($reportErrors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <div class="panel-heading"><div><h2>Incident details</h2><p>Fields marked * are required.</p></div><span class="panel-step">FORM 01</span></div>
        <form action="<?= e(url('submit_report.php')) ?>" method="post" enctype="multipart/form-data" class="stack-form" data-validate-form>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="form-grid">
                <div class="form-field"><label for="reporter_name">Your name *</label><input id="reporter_name" name="reporter_name" type="text" maxlength="100" autocomplete="name" required></div>
                <div class="form-field"><label for="reporter_email">Email address *</label><input id="reporter_email" name="reporter_email" type="email" maxlength="190" autocomplete="email" required><small>Needed with the reference number to track this report.</small></div>
                <div class="form-field"><label for="vehicle_number">Vehicle registration number *</label><input id="vehicle_number" name="vehicle_number" type="text" maxlength="15" placeholder="e.g. MH12AB1234" required style="text-transform:uppercase"><small>Enter the number as observed; avoid guessing.</small></div>
                <div class="form-field"><label for="violation_type">Violation category *</label><select id="violation_type" name="violation_type" required><option value="">Choose a category</option><?php foreach (violation_types() as $type): ?><option value="<?= e($type) ?>" <?= ($old['violation_type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></div>
                <div class="form-field full-field"><label for="location_text">Incident location *</label><input id="location_text" name="location_text" type="text" maxlength="255" placeholder="Road, landmark, area, city" required><small>Use a clear location description.</small><div class="gps-actions"><button type="button" class="btn btn-light" id="capture-location">⌖ Capture my location</button><span id="location-status" class="muted-text">Optional GPS coordinates</span></div><input type="hidden" id="latitude" name="latitude"><input type="hidden" id="longitude" name="longitude"><div id="incident-map" class="incident-map" aria-label="Interactive incident location map"></div><small>Click the map to select the incident point if automatic location capture is unavailable.</small></div>
                <div class="form-field"><label for="incident_at">Date and time of incident</label><input id="incident_at" name="incident_at" type="datetime-local" max="<?= e(date('Y-m-d\TH:i')) ?>"><small>Optional; enter the approximate time if known.</small></div>
                <div class="form-field"><label for="evidence">Evidence photograph (optional)</label><input id="evidence" name="evidence" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP; maximum 5 MB. Do not upload unrelated or private content.</small></div>
                <div class="form-field full-field"><label for="description">Description *</label><textarea id="description" name="description" rows="5" maxlength="2000" minlength="10" placeholder="Describe what you observed, without assumptions or abusive language." required></textarea><div class="field-bottom"><small>10–2,000 characters.</small><small><span id="description-count">0</span>/2000</small></div></div>
            </div>
            <label class="check-line"><input type="checkbox" name="truth_confirm" value="1" required><span>I confirm that the information is accurate to the best of my knowledge and understand that the report will require review.</span></label>
            <button class="btn btn-primary btn-wide" type="submit">Submit Report <span aria-hidden="true">→</span></button>
        </form>
    </section>
    <aside class="side-column">
        <div class="side-card side-blue"><span class="side-card-icon">✓</span><h3>What happens next?</h3><ol class="mini-steps"><li><span>1</span><div><strong>Reference created</strong><small>A unique number is generated for tracking.</small></div></li><li><span>2</span><div><strong>Review queue</strong><small>An authorised reviewer may examine details and evidence.</small></div></li><li><span>3</span><div><strong>Status updated</strong><small>Use your reference number and email to check progress.</small></div></li></ol></div>
        <div class="side-card"><span class="privacy-icon">⌑</span><h3>Protect your privacy</h3><p>Only submit information relevant to the incident. Do not include passwords, identity documents, or unnecessary personal details.</p></div>
        <div class="side-warning"><strong>Safety first</strong><p>Never chase, confront, or photograph a driver while operating a vehicle. Report only when safe.</p></div>
    </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
