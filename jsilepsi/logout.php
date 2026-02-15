<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../src/autoload.php';

use App\Auth;

$auth = new Auth();
$auth->logout();

header('Location: index.php');
exit;
