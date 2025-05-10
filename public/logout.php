<?php
require_once '../config/config.php';
require_once '../includes/log_action.php';
session_start();
if (isset($_SESSION['user_id'])) {
    log_action($conn, $_SESSION['user_id'], $_SESSION['username'], 'Logged out');
}
session_destroy();
header("Location: login.php");
exit();
?> 