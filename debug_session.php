<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Session contents:\n";
print_r($_SESSION);
echo "\nPOST contents:\n";
print_r($_POST);
echo "</pre>";