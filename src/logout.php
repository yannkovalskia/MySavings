<?php
require_once '../config/koneksi.php';

// Destroy session
session_destroy();

// Redirect ke login page
header("Location: login.php");
exit;
