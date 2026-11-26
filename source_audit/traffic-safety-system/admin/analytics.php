<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$pageTitle = 'Road Safety Analytics';
$pdo = db();
$byType = $pdo->query('SELECT violation_type, COUNT(*) AS total FROM reports GROUP BY violation_type ORDER BY total DESC, violation_type ASC')->fetchAll();
$byStatus = $pdo->query('SELECT status, COUNT(*) AS total FROM reports GROUP BY status ORDER BY total DESC')->fetchAll();
$hotspots = $pdo->query('SELECT location_text, COUNT(*) AS total FROM reports GROUP BY location_text ORDER BY total DESC, location_text ASC LIMIT 8')->fetchAll();
$coordinateHotspots = $pdo->query('SELECT latitude, longitude, location_text, COUNT(*) AS total FROM reports WHERE latitude IS NOT NULL AND longitude IS NOT NULL GROUP BY latitude, longitude, location_text ORDER BY total DESC LIMIT 100')->fetchAll();
$total = (int)$pdo->query('SELECT COUNT(*) FROM reports')->fetchColumn();
$maxType = max(1, ...array_map(fn($row) => (int)$row['total'], $byType));
$maxLocation = max(1, ...array_map(fn($row) => (int)$row['total'], $hotspots));
require __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-heading"><div><span class="eyebrow">DATA OVERVIEW</span><h1>Road-safety analytics</h1><p>Summary statistics based on reports stored in this system. Repeated locations are not automatically confirmed accident hotspots.</p></div><a class="btn btn-secondary" href="<?= e(url('admin/reports.php')) ?>">Report register</a></div>
<section class="dashboard-stats analytics-stats"><div class="metric-card"><span>Total submissions</span><strong><?= $total ?></strong><small>All stored reports</small></div><div class="metric-card metric-blue"><span>Violation categories</span><strong><?= count($byType) ?></strong><small>Categories reported</small></div><div class="metric-card metric-purple"><span>Locations reported</span><strong><?= count($hotspots) ?></strong><small>Top locations shown</small></div></section>
<div class="analytics-grid"><section class="panel chart-panel"><div class="panel-heading"><div><h2>Reports by violation type</h2><p>Number of submissions per category</p></div></div><?php if (!$byType): ?><div class="empty-state"><span>▥</span><h3>No data yet</h3><p>Analytics will appear after reports are submitted.</p></div><?php else: ?><div class="bar-list"><?php foreach ($byType as $row): ?><div class="bar-row"><div class="bar-label"><span><?= e($row['violation_type']) ?></span><strong><?= (int)$row['total'] ?></strong></div><div class="bar-track"><span style="width: <?= round(((int)$row['total'] / $maxType) * 100, 1) ?>%"></span></div></div><?php endforeach; ?></div><?php endif; ?></section>
<section class="panel chart-panel"><div class="panel-heading"><div><h2>Status distribution</h2><p>Current report workflow status</p></div></div><?php if (!$byStatus): ?><div class="empty-state"><span>◷</span><h3>No status data</h3></div><?php else: ?><div class="status-summary-list"><?php foreach ($byStatus as $row): ?><div class="status-summary"><span class="status-pill <?= e(status_class($row['status'])) ?>"><?= e($row['status']) ?></span><strong><?= (int)$row['total'] ?></strong></div><?php endforeach; ?></div><?php endif; ?></section>
<section class="panel chart-panel hotspot-panel"><div class="panel-heading"><div><h2>Frequently reported locations</h2><p>Grouped by the location text entered by reporters</p></div><span class="panel-step">TOP 8</span></div><?php if (!$hotspots): ?><div class="empty-state"><span>⌖</span><h3>No locations yet</h3><p>Location counts appear after reports are submitted.</p></div><?php else: ?><div class="hotspot-list"><?php foreach ($hotspots as $row): ?><div class="hotspot-row"><span class="hotspot-pin">⌖</span><div class="hotspot-info"><strong><?= e($row['location_text']) ?></strong><div class="bar-track"><span class="hotspot-bar" style="width: <?= round(((int)$row['total'] / $maxLocation) * 100, 1) ?>%"></span></div></div><strong class="hotspot-count"><?= (int)$row['total'] ?></strong></div><?php endforeach; ?></div><?php endif; ?><div class="dashboard-disclaimer"><strong>Interpretation caution</strong><p>This is a frequency summary of user-submitted location text, not a geospatial or police-verified hotspot model.</p></div></section>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<section class="panel chart-panel"><div class="panel-heading"><div><h2>GPS hotspot map</h2><p>Grouped by submitted coordinates</p></div><span class="panel-step"><?= count($coordinateHotspots) ?> POINTS</span></div><div id="hotspot-map" style="height:420px;border-radius:12px"></div><script>
const hotspotData = <?= json_encode(array_map(fn($r)=>['lat'=>(float)$r['latitude'],'lng'=>(float)$r['longitude'],'name'=>$r['location_text'],'count'=>(int)$r['total']],$coordinateHotspots), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
const map = L.map('hotspot-map').setView(hotspotData.length ? [hotspotData[0].lat, hotspotData[0].lng] : [20.5937,78.9629], hotspotData.length ? 11 : 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
hotspotData.forEach(point=>{L.circle([point.lat,point.lng],{radius:Math.min(2000,250+(point.count*250)),color:'#2869ce',fillColor:'#2869ce',fillOpacity:.55}).bindPopup('<strong>'+point.name.replace(/[<>&]/g,'')+'</strong><br>'+point.count+' report(s)').addTo(map);});
</script></section>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
