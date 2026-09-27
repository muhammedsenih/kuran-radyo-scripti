<?php
require_once __DIR__ . '/../config.php';

unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_user']);
session_destroy();

header('Location: login.php');
exit;
