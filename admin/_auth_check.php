<?php
// admin/_auth_check.php
session_start();
if (empty($_SESSION['admin_user'])) {
    header('Location: login.php');
    exit;
}

