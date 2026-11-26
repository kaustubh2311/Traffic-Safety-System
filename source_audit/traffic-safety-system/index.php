<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$routeFile = __DIR__ . rtrim($requestPath, '/');
if ($requestPath !== '/' && is_file($routeFile . '.php')) {
    require $routeFile . '.php';
    exit;
}
if ($requestPath !== '/' && is_file($routeFile)) {
    require $routeFile;
    exit;
}
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Home';
try {
    $stats = db()->query("SELECT COUNT(*) AS total, SUM(status = 'Under Review') AS reviewing, SUM(status = 'Verified') AS verified FROM reports")->fetch();
    $publicHotspots = db()->query("SELECT ROUND(latitude, 3) AS lat, ROUND(longitude, 3) AS lng, COUNT(*) AS total FROM reports WHERE latitude IS NOT NULL AND longitude IS NOT NULL GROUP BY ROUND(latitude, 3), ROUND(longitude, 3) HAVING total >= 1 ORDER BY total DESC LIMIT 100")->fetchAll();
} catch (Throwable $exception) {
    $stats = ['total' => 0, 'reviewing' => 0, 'verified' => 0];
    $publicHotspots = [];
}
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow"><span class="eyebrow-dot"></span> Safer roads start with responsible reporting</span>
        <h1>Make every road<br><span>safer for everyone.</span></h1>
        <p>Report a traffic-safety concern, share relevant evidence, and follow the review status with a private reference number.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?= e(url('report.php')) ?>">Report a Violation <span aria-hidden="true">→</span></a>
            <a class="btn btn-light" href="<?= e(url('track.php')) ?>">Track a Report</a>
        </div>
        <div class="hero-note"><span class="shield-icon">✓</span> Reports are reviewed by an authorised project administrator.</div>
    </div>
    <div class="hero-visual" aria-label="Illustration of a city road and a vehicle">
        <div class="visual-sun"></div><div class="visual-building building-one"></div><div class="visual-building building-two"></div><div class="visual-building building-three"></div>
        <div class="road"><span class="road-line"></span><div class="car"><span class="car-window"></span><span class="wheel wheel-one"></span><span class="wheel wheel-two"></span></div></div>
        <div class="visual-badge"><span>✓</span><div><strong>Road safety</strong><small>Starts with awareness</small></div></div>
        <div class="visual-caption">REPORT · REVIEW · IMPROVE</div>
    </div>
</section>
<section class="stats-row" aria-label="System overview">
    <div class="stat-card"><span class="stat-icon blue">▤</span><div><strong><?= number_format((int)($stats['total'] ?? 0)) ?></strong><span>Reports received</span></div></div>
    <div class="stat-card"><span class="stat-icon amber">◷</span><div><strong><?= number_format((int)($stats['reviewing'] ?? 0)) ?></strong><span>Under review</span></div></div>
    <div class="stat-card"><span class="stat-icon green">✓</span><div><strong><?= number_format((int)($stats['verified'] ?? 0)) ?></strong><span>Verified reports</span></div></div>
</section>
<section class="section-block">
    <div class="section-heading"><div><span class="eyebrow">HOW IT WORKS</span><h2>A simple way to speak up for safety.</h2></div><p>Submit useful details. Keep the reference number. Check the status when convenient.</p></div>
    <div class="feature-grid">
        <article class="feature-card"><span class="feature-icon icon-blue">＋</span><span class="step-label">01 / REPORT</span><h3>Share what happened</h3><p>Provide the vehicle number, violation category, incident location and a clear description. A photograph is optional but helpful.</p><a href="<?= e(url('report.php')) ?>" class="text-link">Submit a report →</a></article>
        <article class="feature-card"><span class="feature-icon icon-purple">⌕</span><span class="step-label">02 / TRACK</span><h3>Follow the progress</h3><p>Use the unique reference number and the same email address used in the report to view its latest status.</p><a href="<?= e(url('track.php')) ?>" class="text-link">Track your report →</a></article>
        <article class="feature-card"><span class="feature-icon icon-green">▥</span><span class="step-label">03 / REVIEW</span><h3>Support informed action</h3><p>Authorised reviewers can assess submissions, record remarks and study report trends for road-safety awareness.</p><a href="<?= e(url('admin/login.php')) ?>" class="text-link">Authority area →</a></article>
    </div>
</section>
<section class="section-block public-hotspot-section">
    <div class="section-heading"><div><span class="eyebrow">ROAD-SAFETY MAP</span><h2>Reported accident-prone areas</h2></div><p>Circles show aggregated report clusters. They are awareness indicators, not official accident or police hotspot records.</p></div>
    <div id="public-hotspot-map" class="public-hotspot-map"></div>
</section>
<script>
window.addEventListener('DOMContentLoaded', () => {
    if (!window.L) return;
    const hotspotData = <?= json_encode(array_map(fn($r) => ['lat'=>(float)$r['lat'], 'lng'=>(float)$r['lng'], 'count'=>(int)$r['total']], $publicHotspots), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
    const map = L.map('public-hotspot-map').setView(hotspotData.length ? [hotspotData[0].lat, hotspotData[0].lng] : [20.5937,78.9629], hotspotData.length ? 10 : 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19, attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
    hotspotData.forEach((point) => {
        L.circle([point.lat, point.lng], {radius:Math.min(3000, 350 + point.count * 300), color:'#b43434', fillColor:'#ef4444', fillOpacity:.45}).bindPopup('<strong>Reported cluster</strong><br>' + point.count + ' report(s)').addTo(map);
    });
});
</script>
<section class="notice-panel"><div class="notice-symbol">i</div><div><strong>Responsible reporting notice</strong><p>Submitted reports are user-provided reports, not confirmed violations. This application does not connect to police systems and cannot issue official challans or collect fines. Any penalty figure is illustrative until confirmed by an authorised authority.</p></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
