<?php
session_start();
include '../config/database.php';

$message = '';
$error = '';

// Handle group operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $group_name = $conn->real_escape_string($_POST['group_name'] ?? '');
            $description = $conn->real_escape_string($_POST['description'] ?? '');
            
            if (!$group_name) {
                $error = "Group name is required!";
            } else {
                $query = "INSERT INTO asset_groups (group_name, description) VALUES ('$group_name', '$description')";
                if ($conn->query($query)) {
                    $message = "Group added successfully!";
                } else {
                    $error = "Error adding group: " . $conn->error;
                }
            }
        } elseif ($_POST['action'] == 'delete') {
            $group_id = intval($_POST['group_id'] ?? 0);
            if ($group_id) {
                $conn->query("DELETE FROM asset_groups WHERE id = $group_id");
                $message = "Group deleted successfully!";
            }
        }
    }
}

// Get all groups
$groups = $conn->query("SELECT * FROM asset_groups ORDER BY group_name");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Asset Groups</title>
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
                <h1 class="h3">Asset Groups</h1>
                <p class="text-muted">Manage asset categories and groups</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Add Group Form -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Group</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="mb-3">
                                <label for="group_name" class="form-label">Group Name *</label>
                                <input type="text" class="form-control" id="group_name" name="group_name" 
                                       placeholder="e.g. HVAC, Electrical, Furniture" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="3" placeholder="Add description for this group..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Add Group
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Groups List -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Asset Groups</h5>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php
                        if ($groups->num_rows > 0) {
                            while ($group = $groups->fetch_assoc()) {
                                $asset_count = $conn->query("SELECT COUNT(*) as count FROM assets WHERE group_id = {$group['id']}")->fetch_assoc()['count'];
                                ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($group['group_name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($group['description'] ?? 'No description'); ?></small>
                                        <br>
                                        <small class="badge bg-info"><?php echo $asset_count; ?> asset(s)</small>
                                    </div>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Delete this group? Assets will not be deleted.')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<p class='text-muted text-center py-3'>No groups yet. Create one to get started!</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="../pages/add_asset.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Asset
                </a>
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-th-large"></i> View All Assets
                </a>
                <a href="../pages/reports.php" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> View Reports
                </a>
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
