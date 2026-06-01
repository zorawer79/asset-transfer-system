<?php
session_start();
include 'config/database.php';

// Get statistics
$total_assets = $conn->query("SELECT COUNT(*) as count FROM assets")->fetch_assoc()['count'];
$new_assets = $conn->query("SELECT COUNT(*) as count FROM assets WHERE status='New'")->fetch_assoc()['count'];
$used_assets = $conn->query("SELECT COUNT(*) as count FROM assets WHERE status='Used'")->fetch_assoc()['count'];
$scrap_assets = $conn->query("SELECT COUNT(*) as count FROM assets WHERE status='Scrap'")->fetch_assoc()['count'];

// Get search parameters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_group = isset($_GET['group']) ? $_GET['group'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$where_clause = "1=1";
if ($search) {
    $where_clause .= " AND (a.asset_name LIKE '%$search%' OR a.asset_id LIKE '%$search%' OR a.rfid_code LIKE '%$search%')";
}
if ($filter_group) {
    $where_clause .= " AND a.group_id = " . intval($filter_group);
}
if ($filter_status) {
    $where_clause .= " AND a.status = '$filter_status'";
}

$query = "SELECT a.*, g.group_name FROM assets a 
          LEFT JOIN asset_groups g ON a.group_id = g.id 
          WHERE $where_clause 
          ORDER BY a.created_at DESC";
$result = $conn->query($query);

// Get all groups for filter
$groups_result = $conn->query("SELECT id, group_name FROM asset_groups ORDER BY group_name");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Transfer Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-exchange-alt"></i> Asset Transfer
            </a>
            <span class="navbar-text text-white">Track & manage asset transfers</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="pages/manage_groups.php">
                            <i class="fas fa-layer-group"></i> Manage Groups
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h1 class="h3">Asset Transfer</h1>
                <p class="text-muted">Track & manage asset transfers</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="pages/add_asset.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Add Asset
                </a>
                <a href="pages/reports.php" class="btn btn-outline-primary">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
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
                                <h3 class="mb-0"><?php echo $total_assets; ?></h3>
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
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">New</h6>
                                <h3 class="mb-0"><?php echo $new_assets; ?></h3>
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
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Used</h6>
                                <h3 class="mb-0"><?php echo $used_assets; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-danger">
                                <i class="fas fa-trash"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Scrap</h6>
                                <h3 class="mb-0"><?php echo $scrap_assets; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by name, RFID, ID, location..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="group">
                            <option value="">All Groups</option>
                            <?php
                            while ($group = $groups_result->fetch_assoc()) {
                                $selected = ($filter_group == $group['id']) ? 'selected' : '';
                                echo "<option value='{$group['id']}' $selected>{$group['group_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="New" <?php echo ($filter_status == 'New') ? 'selected' : ''; ?>>New</option>
                            <option value="Used" <?php echo ($filter_status == 'Used') ? 'selected' : ''; ?>>Used</option>
                            <option value="Scrap" <?php echo ($filter_status == 'Scrap') ? 'selected' : ''; ?>>Scrap</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assets Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Name</th>
                            <th>Asset ID</th>
                            <th>RFID Code</th>
                            <th>Group</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status_color = [
                                    'New' => 'success',
                                    'Used' => 'warning',
                                    'Scrap' => 'danger'
                                ];
                                $color = $status_color[$row['status']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['asset_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['asset_id']); ?></td>
                                    <td><code><?php echo htmlspecialchars($row['rfid_code'] ?? 'N/A'); ?></code></td>
                                    <td><?php echo htmlspecialchars($row['group_name'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-<?php echo $color; ?>"><?php echo $row['status']; ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="pages/view_asset.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="pages/edit_asset.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="pages/delete_asset.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center text-muted py-4'>No assets found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2024 Asset Transfer Management System</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted mb-0">Powered by PHP & MySQL</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>