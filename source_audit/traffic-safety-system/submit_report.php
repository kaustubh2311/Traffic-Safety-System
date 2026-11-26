<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('report.php');
}
verify_csrf();

$name = trim((string)($_POST['reporter_name'] ?? ''));
$email = trim((string)($_POST['reporter_email'] ?? ''));
$vehicle = strtoupper(trim((string)($_POST['vehicle_number'] ?? '')));
$type = trim((string)($_POST['violation_type'] ?? ''));
$location = trim((string)($_POST['location_text'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$incidentInput = trim((string)($_POST['incident_at'] ?? ''));
$latitudeInput = trim((string)($_POST['latitude'] ?? ''));
$longitudeInput = trim((string)($_POST['longitude'] ?? ''));

$errors = [];
if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter a name no longer than 100 characters.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors[] = 'Enter a valid email address.';
if (!preg_match('/^[A-Z0-9][A-Z0-9 -]{3,14}$/', $vehicle)) $errors[] = 'Enter a valid-looking vehicle registration (4–15 letters, numbers, spaces or hyphens).';
if (!in_array($type, violation_types(), true)) $errors[] = 'Choose a valid violation category.';
if ($location === '' || mb_strlen($location) > 255) $errors[] = 'Enter the incident location (maximum 255 characters).';
if (mb_strlen($description) < 10 || mb_strlen($description) > 2000) $errors[] = 'Description must contain 10–2,000 characters.';
if (($_POST['truth_confirm'] ?? '') !== '1') $errors[] = 'Please confirm that the report is accurate to the best of your knowledge.';

$incidentAt = null;
if ($incidentInput !== '') {
    $date = DateTime::createFromFormat('Y-m-d\TH:i', $incidentInput);
    if (!$date || $date->format('Y-m-d\TH:i') !== $incidentInput) {
        $errors[] = 'Enter a valid incident date and time.';
    } elseif ($date > new DateTime()) {
        $errors[] = 'Incident date and time cannot be in the future.';
    } else {
        $incidentAt = $date->format('Y-m-d H:i:s');
    }
}

$latitude = null;
$longitude = null;
if ($latitudeInput !== '' || $longitudeInput !== '') {
    if (!is_numeric($latitudeInput) || !is_numeric($longitudeInput) || (float)$latitudeInput < -90 || (float)$latitudeInput > 90 || (float)$longitudeInput < -180 || (float)$longitudeInput > 180) {
        $errors[] = 'The captured GPS coordinates are invalid.';
    } else {
        $latitude = number_format((float)$latitudeInput, 7, '.', '');
        $longitude = number_format((float)$longitudeInput, 7, '.', '');
    }
}

$uploadedAbsolutePath = null;
$evidenceRelativePath = null;
if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['evidence'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'The photograph could not be uploaded. Try again with a file smaller than 5 MB.';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = 'The photograph must be 5 MB or smaller.';
    } elseif (!is_uploaded_file($file['tmp_name'])) {
        $errors[] = 'The uploaded file could not be verified.';
    } else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            $errors[] = 'Upload a JPG, PNG or WebP image only.';
        } else {
            $storageDir = __DIR__ . '/storage/evidence';
            if (!is_dir($storageDir) && !mkdir($storageDir, 0750, true) && !is_dir($storageDir)) {
                $errors[] = 'Evidence storage is unavailable. Please contact the project administrator.';
            } else {
                $filename = bin2hex(random_bytes(24)) . '.' . $allowed[$mime];
                $uploadedAbsolutePath = $storageDir . '/' . $filename;
                $evidenceRelativePath = 'storage/evidence/' . $filename;
                if (!move_uploaded_file($file['tmp_name'], $uploadedAbsolutePath)) {
                    $errors[] = 'The photograph could not be saved. Try again.';
                    $uploadedAbsolutePath = null;
                    $evidenceRelativePath = null;
                }
            }
        }
    }
}

if ($errors) {
    if ($uploadedAbsolutePath && is_file($uploadedAbsolutePath)) @unlink($uploadedAbsolutePath);
    $_SESSION['report_errors'] = $errors;
    $_SESSION['report_old'] = ['reporter_name' => $name, 'reporter_email' => $email, 'vehicle_number' => $vehicle, 'violation_type' => $type, 'location_text' => $location, 'description' => $description, 'incident_at' => $incidentInput];
    flash('error', 'Please correct the items listed below.');
    redirect('report.php');
}

try {
    $pdo = db();
    $pdo->beginTransaction();
    $reference = '';
    $insert = $pdo->prepare('INSERT INTO reports (reference_no, reporter_name, reporter_email, vehicle_number, violation_type, location_text, incident_at, latitude, longitude, citizen_id, description, evidence_path, status, created_at, updated_at) VALUES (:reference_no, :reporter_name, :reporter_email, :vehicle_number, :violation_type, :location_text, :incident_at, :latitude, :longitude, :citizen_id, :description, :evidence_path, :status, NOW(), NOW())');
    // A random suffix makes references difficult to guess; retry in the unlikely event of a collision.
    for ($attempt = 0; $attempt < 4; $attempt++) {
        $reference = 'TSR-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        try {
            $insert->execute(['reference_no' => $reference, 'reporter_name' => $name, 'reporter_email' => $email, 'vehicle_number' => $vehicle, 'violation_type' => $type, 'location_text' => $location, 'incident_at' => $incidentAt, 'latitude' => $latitude, 'longitude' => $longitude, 'citizen_id' => current_citizen()['id'] ?? null, 'description' => $description, 'evidence_path' => $evidenceRelativePath, 'status' => 'Submitted']);
            break;
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000' || $attempt === 3) throw $exception;
        }
    }
    $reportId = (int)$pdo->lastInsertId();
    $history = $pdo->prepare('INSERT INTO report_status_history (report_id, old_status, new_status, notes, changed_by, changed_at) VALUES (?, NULL, ?, ?, NULL, NOW())');
    $history->execute([$reportId, 'Submitted', 'Report created by citizen submission.']);
    $pdo->commit();
    unset($_SESSION['report_old'], $_SESSION['report_errors']);
    $_SESSION['new_report_confirmation'] = ['reference_no' => $reference, 'reporter_email' => $email];
    redirect('report_submitted.php');
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    if ($uploadedAbsolutePath && is_file($uploadedAbsolutePath)) @unlink($uploadedAbsolutePath);
    error_log('Report submission failed: ' . $exception->getMessage());
    flash('error', 'Your report could not be saved. Check that MySQL is running and the database schema has been imported, then try again.');
    redirect('report.php');
}
