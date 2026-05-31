<?php
session_start();
include '../config/database.php';

// Get statistics
$stats = [
    'total_assets' => $conn->query("SELECT COUNT(*) as count FROM assets")->fetch_assoc()['count'],
    'new' => $conn->query("SELECT COUNT(*) as count FROM assets WHERE status='New'")->fetch_assoc()['count'],
    'in_store' => $conn->query("SELECT COUNT(*) as count FROM assets WHERE status='In Store'")->fetch_assoc()['count'],
    'used' => $conn->query("SELECT COUNT(*) as count FROM assets WHERE status='Used'")->fetch_assoc()['count'],
    'scrap' => $conn->query("SELECT COUNT(*) as count FROM assets WHERE status='Scrap'")->fetch_assoc()['count'],
    'total_transfers' => $conn->query("SELECT COUNT(*) as count FROM transfers")->fetch_assoc()['count']
];

// Get assets by group
$group_stats = $conn->query("SELECT g.group_name, COUNT(a.id) as count FROM asset_groups g LEFT JOIN assets a ON g.id = a.group_id GROUP BY g.id");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
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

    <div class="container-fluid mt-4 mb-5">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3">Reports</h1>
                <p class="text-muted">Asset Transfer Management Statistics</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Total Assets</h6>
                                <h3 class="mb-0"><?php echo $stats['total_assets']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-info">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Total Transfers</h6>
                                <h3 class="mb-0"><?php echo $stats['total_transfers']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Operational Rate</h6>
                                <h3 class="mb-0"><?php echo $stats['total_assets'] > 0 ? round(($stats['new'] + $stats['in_store']) / $stats['total_assets'] * 100) : 0; ?>%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-warning">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Inactive Assets</h6>
                                <h3 class="mb-0"><?php echo $stats['used'] + $stats['scrap']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Assets by Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>
                                            <span class="badge bg-success">New</span>
                                        </td>
                                        <td class="fw-bold"><?php echo $stats['new']; ?> Assets</td>
                                        <td class="text-end">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" style="width: <?php echo $stats['total_assets'] > 0 ? ($stats['new'] / $stats['total_assets'] * 100) : 0; ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="badge bg-info">In Store</span>
                                        </td>
                                        <td class="fw-bold"><?php echo $stats['in_store']; ?> Assets</td>
                                        <td class="text-end">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-info" style="width: <?php echo $stats['total_assets'] > 0 ? ($stats['in_store'] / $stats['total_assets'] * 100) : 0; ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="badge bg-warning">Used</span>
                                        </td>
                                        <td class="fw-bold"><?php echo $stats['used']; ?> Assets</td>
                                        <td class="text-end">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-warning" style="width: <?php echo $stats['total_assets'] > 0 ? ($stats['used'] / $stats['total_assets'] * 100) : 0; ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="badge bg-danger">Scrap</span>
                                        </td>
                                        <td class="fw-bold"><?php echo $stats['scrap']; ?> Assets</td>
                                        <td class="text-end">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-danger" style="width: <?php echo $stats['total_assets'] > 0 ? ($stats['scrap'] / $stats['total_assets'] * 100) : 0; ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Assets by Group</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Group</th>
                                        <th class="text-end">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = $group_stats->fetch_assoc()) {
                                        echo "<tr>
                                                <td>" . htmlspecialchars($row['group_name']) . "</td>
                                                <td class='text-end'><strong>" . $row['count'] . "</strong></td>
                                              </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>