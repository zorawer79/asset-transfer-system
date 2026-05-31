<?php
session_start();
include '../config/database.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $step = isset($_POST['step']) ? intval($_POST['step']) : 1;
    $current_step = $_SESSION['current_step'] ?? 1;
    
    if ($step == 1) {
        // Validate Asset Info
        $asset_name = $conn->real_escape_string($_POST['asset_name'] ?? '');
        $asset_id = $conn->real_escape_string($_POST['asset_id'] ?? '');
        $group_id = intval($_POST['group_id'] ?? 0);
        
        if (!$asset_name || !$asset_id || !$group_id) {
            $error = "All fields are required!";
        } else {
            $_SESSION['asset_info'] = [
                'asset_name' => $asset_name,
                'asset_id' => $asset_id,
                'group_id' => $group_id
            ];
            $_SESSION['current_step'] = 2;
            $current_step = 2;
        }
    } elseif ($step == 2) {
        // Validate Identification
        $rfid_code = $conn->real_escape_string($_POST['rfid_code'] ?? '');
        $status = $conn->real_escape_string($_POST['status'] ?? 'New');
        
        if (!$rfid_code) {
            $error = "RFID Code is required!";
        } else {
            $_SESSION['identification'] = [
                'rfid_code' => $rfid_code,
                'status' => $status
            ];
            $_SESSION['current_step'] = 3;
            $current_step = 3;
        }
    } elseif ($step == 3) {
        // Validate Transfer
        $transfer_from = $conn->real_escape_string($_POST['transfer_from'] ?? '');
        $stored_location = $conn->real_escape_string($_POST['stored_location'] ?? '');
        $transfer_to = $conn->real_escape_string($_POST['transfer_to'] ?? '');
        $date_received = $_POST['date_received'] ?? '';
        $date_of_transfer = $_POST['date_of_transfer'] ?? '';
        
        if (!$transfer_to || !$date_of_transfer) {
            $error = "Transfer To and Date of Transfer are required!";
        } else {
            $_SESSION['transfer'] = [
                'transfer_from' => $transfer_from,
                'stored_location' => $stored_location,
                'transfer_to' => $transfer_to,
                'date_received' => $date_received,
                'date_of_transfer' => $date_of_transfer
            ];
            $_SESSION['current_step'] = 4;
            $current_step = 4;
        }
    } elseif ($step == 4) {
        // Handle photo upload and notes
        $notes = $conn->real_escape_string($_POST['notes'] ?? '');
        
        // Insert into database
        if (isset($_SESSION['asset_info']) && isset($_SESSION['identification']) && isset($_SESSION['transfer'])) {
            $info = $_SESSION['asset_info'];
            $id_data = $_SESSION['identification'];
            $transfer = $_SESSION['transfer'];
            
            $query = "INSERT INTO assets (asset_name, asset_id, group_id, rfid_code, status) 
                      VALUES ('{$info['asset_name']}', '{$info['asset_id']}', {$info['group_id']}, '{$id_data['rfid_code']}', '{$id_data['status']}')";
            
            if ($conn->query($query)) {
                $asset_id = $conn->insert_id;
                
                // Insert transfer record
                $transfer_query = "INSERT INTO transfers (asset_id, transfer_from, stored_location, transfer_to, date_received, date_of_transfer, notes) 
                                  VALUES ($asset_id, '{$transfer['transfer_from']}', '{$transfer['stored_location']}', '{$transfer['transfer_to']}', '{$transfer['date_received']}', '{$transfer['date_of_transfer']}', '$notes')";
                
                $conn->query($transfer_query);
                
                // Handle photo upload
                if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
                    $upload_dir = '../uploads/assets/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    
                    $file_name = time() . '_' . basename($_FILES['photo']['name']);
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
                        $photo_query = "INSERT INTO asset_photos (asset_id, photo_path, photo_name) VALUES ($asset_id, '$file_path', '$file_name')";
                        $conn->query($photo_query);
                    }
                }
                
                // Clear session
                unset($_SESSION['asset_info']);
                unset($_SESSION['identification']);
                unset($_SESSION['transfer']);
                unset($_SESSION['current_step']);
                
                $_SESSION['success'] = "Asset added successfully!";
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Error adding asset: " . $conn->error;
            }
        }
    }
}

$current_step = $_SESSION['current_step'] ?? 1;
$groups = $conn->query("SELECT id, group_name FROM asset_groups ORDER BY group_name");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Asset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step-item <?php echo $current_step >= 1 ? 'active' : ''; ?> <?php echo $current_step > 1 ? 'completed' : ''; ?>">
                        <div class="step-number">1</div>
                        <p class="small">Asset Info</p>
                    </div>
                    <div class="step-item <?php echo $current_step >= 2 ? 'active' : ''; ?> <?php echo $current_step > 2 ? 'completed' : ''; ?>">
                        <div class="step-number">2</div>
                        <p class="small">Identification</p>
                    </div>
                    <div class="step-item <?php echo $current_step >= 3 ? 'active' : ''; ?> <?php echo $current_step > 3 ? 'completed' : ''; ?>">
                        <div class="step-number">3</div>
                        <p class="small">Transfer</p>
                    </div>
                    <div class="step-item <?php echo $current_step >= 4 ? 'active' : ''; ?>">
                        <div class="step-number">4</div>
                        <p class="small">Photo & Notes</p>
                    </div>
                </div>

                <!-- Error Message -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" enctype="multipart/form-data" id="assetForm">
                    <!-- Step 1: Asset Info -->
                    <div class="wizard-step <?php echo $current_step == 1 ? 'active' : ''; ?>">
                        <div class="form-section">
                            <h5 class="form-section-title">Asset Information</h5>
                            
                            <div class="mb-3">
                                <label for="asset_name" class="form-label">Asset Name *</label>
                                <input type="text" class="form-control" id="asset_name" name="asset_name" 
                                       placeholder="e.g. Main Chiller Unit" 
                                       value="<?php echo $_SESSION['asset_info']['asset_name'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="asset_id" class="form-label">Asset ID *</label>
                                <input type="text" class="form-control" id="asset_id" name="asset_id" 
                                       placeholder="e.g. AST-2024-001" 
                                       value="<?php echo $_SESSION['asset_info']['asset_id'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="group_id" class="form-label">Asset Group *</label>
                                <select class="form-select" id="group_id" name="group_id" required>
                                    <option value="">Select a group...</option>
                                    <?php
                                    while ($group = $groups->fetch_assoc()) {
                                        $selected = (isset($_SESSION['asset_info']['group_id']) && $_SESSION['asset_info']['group_id'] == $group['id']) ? 'selected' : '';
                                        echo "<option value='{$group['id']}' $selected>{$group['group_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Identification -->
                    <div class="wizard-step <?php echo $current_step == 2 ? 'active' : ''; ?>">
                        <div class="form-section">
                            <h5 class="form-section-title">Identification & Status</h5>
                            
                            <div class="mb-3">
                                <label for="rfid_code" class="form-label">RFID Code *</label>
                                <input type="text" class="form-control" id="rfid_code" name="rfid_code" 
                                       placeholder="e.g. RFID-001" 
                                       value="<?php echo $_SESSION['identification']['rfid_code'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status *</label>
                                <div class="d-grid gap-2">
                                    <?php
                                    $statuses = ['New', 'In Store', 'Used', 'Scrap'];
                                    $current_status = $_SESSION['identification']['status'] ?? 'New';
                                    foreach ($statuses as $s) {
                                        $active = ($current_status == $s) ? 'active' : '';
                                        echo "<input type='radio' class='btn-check' name='status' id='status_$s' value='$s' " . ($current_status == $s ? 'checked' : '') . ">";
                                        echo "<label class='btn btn-outline-primary $active' for='status_$s'>$s</label>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Transfer -->
                    <div class="wizard-step <?php echo $current_step == 3 ? 'active' : ''; ?>">
                        <div class="form-section">
                            <h5 class="form-section-title">Transfer & Location Details</h5>
                            
                            <div class="mb-3">
                                <label for="transfer_from" class="form-label">Transfer From</label>
                                <input type="text" class="form-control" id="transfer_from" name="transfer_from" 
                                       placeholder="e.g. RAK Store" 
                                       value="<?php echo $_SESSION['transfer']['transfer_from'] ?? ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="stored_location" class="form-label">Stored Location</label>
                                <input type="text" class="form-control" id="stored_location" name="stored_location" 
                                       placeholder="e.g. Warehouse B, Shelf 3" 
                                       value="<?php echo $_SESSION['transfer']['stored_location'] ?? ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="transfer_to" class="form-label">Transfer To (Final Destination) *</label>
                                <input type="text" class="form-control" id="transfer_to" name="transfer_to" 
                                       placeholder="e.g. Dubai Office" 
                                       value="<?php echo $_SESSION['transfer']['transfer_to'] ?? ''; ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_received" class="form-label">Date Received</label>
                                    <input type="date" class="form-control" id="date_received" name="date_received" 
                                           value="<?php echo $_SESSION['transfer']['date_received'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_transfer" class="form-label">Date of Transfer *</label>
                                    <input type="date" class="form-control" id="date_of_transfer" name="date_of_transfer" 
                                           value="<?php echo $_SESSION['transfer']['date_of_transfer'] ?? ''; ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Photo & Notes -->
                    <div class="wizard-step <?php echo $current_step == 4 ? 'active' : ''; ?>">
                        <div class="form-section">
                            <h5 class="form-section-title">Photo & Notes</h5>
                            
                            <div class="mb-3">
                                <label for="photo" class="form-label">Upload Photo</label>
                                <div class="photo-upload-box">
                                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*" style="display:none;">
                                    <div id="photoPlaceholder">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                        <p>Click to upload or drag and drop</p>
                                        <small>PNG, JPG, GIF up to 5MB</small>
                                    </div>
                                    <img id="photoPreview" class="photo-preview" style="display:none;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4" 
                                          placeholder="Add any additional notes or details about this asset..."><?php echo $_SESSION['transfer_notes'] ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden input for step -->
                    <input type="hidden" id="step" name="step" value="<?php echo $current_step; ?>">

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <?php if ($current_step > 1): ?>
                            <button type="button" class="btn btn-secondary" onclick="previousStep()">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                        <?php else: ?>
                            <a href="../index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>

                        <?php if ($current_step < 4): ?>
                            <button type="submit" class="btn btn-primary">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Asset
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('photo').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoPreview').src = e.target.result;
                    document.getElementById('photoPreview').style.display = 'block';
                    document.getElementById('photoPlaceholder').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        document.querySelector('.photo-upload-box').addEventListener('click', function() {
            document.getElementById('photo').click();
        });

        function previousStep() {
            const currentStep = <?php echo $current_step; ?>;
            if (currentStep > 1) {
                document.getElementById('step').value = currentStep - 1;
                document.getElementById('assetForm').submit();
            }
        }
    </script>
</body>
</html>