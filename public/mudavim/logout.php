<?php
declare(strict_types=1);
require_once __DIR__ . '/autoload.php';
use Mudavim\Core\Auth;
Auth::boot();
Auth::logout();
header('Location: login.php');
exit;
