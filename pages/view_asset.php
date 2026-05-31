<?php
session_start();
include '../config/database.php';

$asset_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$asset_id) {
    header("Location: ../index.php");
    exit();
}

$query = "SELECT a.*, g.group_name FROM assets a 
          LEFT JOIN asset_groups g ON a.group_id = g.id 
          WHERE a.id = $asset_id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header("Location: ../index.php");
    exit();
}

$asset = $result->fetch_assoc();

// Get transfer history
$transfers = $conn->query("SELECT * FROM transfers WHERE asset_id = $asset_id ORDER BY date_of_transfer DESC");

// Get photos
$photos = $conn->query("SELECT * FROM asset_photos WHERE asset_id = $asset_id ORDER BY uploaded_at DESC");

// Get notes
$notes = $conn->query("SELECT * FROM asset_notes WHERE asset_id = $asset_id ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Asset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-exchange-alt"></i> Asset Transfer
            </a>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3"><?php echo htmlspecialchars($asset['asset_name']); ?></h1>
                <p class="text-muted">Asset Details & Transfer History</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="edit_asset.php?id=<?php echo $asset['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <!-- Asset Information -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Asset Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Asset ID:</td>
                                <td><?php echo htmlspecialchars($asset['asset_id']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">RFID Code:</td>
                                <td><code><?php echo htmlspecialchars($asset['rfid_code']); ?></code></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Group:</td>
                                <td><?php echo htmlspecialchars($asset['group_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>
                                    <?php
                                    $status_color = [
                                        'New' => 'success',
                                        'In Store' => 'info',
                                        'Used' => 'warning',
                                        'Scrap' => 'danger'
                                    ];
                                    $color = $status_color[$asset['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>"><?php echo $asset['status']; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Created:</td>
                                <td><?php echo date('d M Y H:i', strtotime($asset['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Last Updated:</td>
                                <td><?php echo date('d M Y H:i', strtotime($asset['updated_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Photos Section -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Photos</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($photos->num_rows > 0) {
                            ?>
                            <div class="row g-3">
                                <?php
                                while ($photo = $photos->fetch_assoc()) {
                                    ?>
                                    <div class="col-6">
                                        <img src="../<?php echo htmlspecialchars($photo['photo_path']); ?>" class="img-fluid rounded" alt="Asset Photo">
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        } else {
                            echo "<p class='text-muted'>No photos uploaded</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transfer History -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Transfer History</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>From</th>
                            <th>Stored Location</th>
                            <th>To</th>
                            <th>Date Received</th>
                            <th>Date of Transfer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($transfers->num_rows > 0) {
                            while ($transfer = $transfers->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transfer['transfer_from'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($transfer['stored_location'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($transfer['transfer_to']); ?></td>
                                    <td><?php echo $transfer['date_received'] ? date('d M Y', strtotime($transfer['date_received'])) : 'N/A'; ?></td>
                                    <td><?php echo date('d M Y', strtotime($transfer['date_of_transfer'])); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted py-3'>No transfer history</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notes -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <?php
                if ($notes->num_rows > 0) {
                    while ($note = $notes->fetch_assoc()) {
                        ?>
                        <div class="mb-3">
                            <p class="mb-1"><?php echo htmlspecialchars($note['note_text']); ?></p>
                            <small class="text-muted">Added on <?php echo date('d M Y H:i', strtotime($note['created_at'])); ?></small>
                            <hr>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p class='text-muted'>No notes added</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>