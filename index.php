<?php
require_once __DIR__ . '/config.php';

if (!is_logged_in()) {
    header('Location: ' . ROOT_URL . 'login.php');
    exit;
}

if (is_admin()) {
    header('Location: ' . ROOT_URL . 'admin/dashboard.php');
} else {
    header('Location: ' . ROOT_URL . 'agent/search.php');
}
exit;
