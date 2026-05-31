<?php
session_start();
include '../config/database.php';

$asset_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$asset_id) {
    header("Location: ../index.php");
    exit();
}

// Delete associated transfers, photos, notes
$conn->query("DELETE FROM transfers WHERE asset_id = $asset_id");
$conn->query("DELETE FROM asset_photos WHERE asset_id = $asset_id");
$conn->query("DELETE FROM asset_notes WHERE asset_id = $asset_id");

// Delete asset
$conn->query("DELETE FROM assets WHERE id = $asset_id");

$_SESSION['success'] = "Asset deleted successfully!";
header("Location: ../index.php");
exit();
?>