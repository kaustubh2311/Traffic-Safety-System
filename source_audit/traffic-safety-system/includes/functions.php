<?php
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string {
    return BASE_URL . ($path === '' ? '/' : '/' . ltrim($path, '/'));
}

function redirect(string $path): never {
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $posted = $_POST['csrf_token'] ?? '';
    if (!is_string($posted) || !hash_equals(csrf_token(), $posted)) {
        http_response_code(419);
        exit('Your form session expired. Go back, refresh the page, and try again.');
    }
}

function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function current_admin(): ?array {
    return isset($_SESSION['admin']) && is_array($_SESSION['admin']) ? $_SESSION['admin'] : null;
}

function require_admin(): void {
    if (!current_admin()) {
        flash('error', 'Please sign in to access the authority area.');
        redirect('admin/login.php');
    }
}

function current_citizen(): ?array {
    return isset($_SESSION['citizen']) && is_array($_SESSION['citizen']) ? $_SESSION['citizen'] : null;
}

function require_citizen(): void {
    if (!current_citizen()) {
        flash('error', 'Please sign in to access your citizen account.');
        redirect('citizen/login.php');
    }
}

function require_permission(string $permission): void {
    require_admin();
    $admin = current_admin();
    $role = $admin['role'] ?? '';
    $permissions = [
        'admin' => ['reports.view','reports.update','analytics.view','rules.manage','challans.manage','users.manage','audit.view'],
        'officer' => ['reports.view','reports.update','analytics.view'],
    ];
    if (!in_array($permission, $permissions[$role] ?? [], true)) {
        http_response_code(403);
        exit('You do not have permission to access this feature.');
    }
}

function audit_log(string $action, string $entityType, ?string $entityId = null, ?string $details = null): void {
    try {
        $actor = current_admin() ?: current_citizen();
        $role = $actor['role'] ?? null;
        $id = isset($actor['id']) ? (int)$actor['id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = db()->prepare('INSERT INTO audit_logs (user_id, actor_role, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$id, $role, $action, $entityType, $entityId, $details, $ip]);
    } catch (Throwable $exception) {
        error_log('Audit log failed: ' . $exception->getMessage());
    }
}

function notify_user(?int $userId, string $title, string $message, ?string $link = null, ?int $reportId = null, ?int $challanId = null): void {
    if (!$userId) return;
    try {
        $stmt = db()->prepare('INSERT INTO notifications (user_id, report_id, challan_id, title, message, link) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $reportId, $challanId, $title, $message, $link]);
    } catch (Throwable $exception) {
        error_log('Notification failed: ' . $exception->getMessage());
    }
}

function format_date(?string $date): string {
    if (!$date) return '—';
    $timestamp = strtotime($date);
    return $timestamp ? date('d M Y, h:i A', $timestamp) : '—';
}

function status_class(string $status): string {
    return match ($status) {
        'Submitted' => 'status-submitted',
        'Under Review' => 'status-review',
        'Verified' => 'status-verified',
        'Rejected' => 'status-rejected',
        'Closed' => 'status-closed',
        default => 'status-submitted',
    };
}

function violation_types(): array {
    try {
        $rows = db()->query('SELECT name FROM offence_rules WHERE active = 1 ORDER BY name')->fetchAll();
        $types = array_values(array_filter(array_map(fn($row) => trim((string)$row['name']), $rows)));
        if ($types) return $types;
    } catch (Throwable $exception) {
        // A missing/uninitialized optional table should not break the public form.
    }
    return [
        'Speeding', 'Red-light violation', 'Wrong-side driving', 'Illegal parking',
        'Helmet / seat-belt violation', 'Using a mobile phone while driving',
        'Blocking a pedestrian crossing', 'Dangerous / reckless driving', 'Other',
    ];
}
