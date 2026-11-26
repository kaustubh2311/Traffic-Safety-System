<?php
require_once __DIR__ . '/../includes/bootstrap.php';
unset($_SESSION['citizen']);
session_regenerate_id(true);
flash('success', 'You have been signed out.');
redirect('');
