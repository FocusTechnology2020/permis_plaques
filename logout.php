<?php
require_once __DIR__ . '/config.php';
logout_user();
session_start();
set_flash('Vous avez été déconnecté.');
header('Location: ' . ROOT_URL . 'login.php');
exit;
