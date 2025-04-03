<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/functions/item_loader.php';

$tool = $_GET['tool'] ?? '';

switch ($tool) {
    case 'converter':
        require_once __DIR__ . '/tools/converter.php';
        break;
    case 'item_bag_generator':
        require_once __DIR__ . '/tools/generator.php';
        break;
    default:
        break;
}

$itemFile = __DIR__ . '/Item.txt';
$itemNames = loadItemNames($itemFile);
$categories = array_keys($itemNames);
$itemNamesJson = json_encode($itemNames);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

include 'dashboard.html';
?>