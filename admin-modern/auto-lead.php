<?php
// save as /var/www/html/vicidial/upload_list.php

// Authentication check (simplified - add proper auth)
session_start();
if (!isset($_SESSION['6666'])) {
    die("Please login first");
}

// Function to call VICIdial Non-Agent API for list creation
function createListViaAPI($api_url, $api_user, $api_pass, $list_id, $list_name, $list_description, $campaign_id) {
    $params = [
        'source' => 'upload_form',
        'function' => 'add_list',
        'user' => $api_user,
        'pass' => $api_pass,
        'list_id' => $list_id,
        'list_name' => $list_name,
        'list_description' => $list_description,
        'campaign_id' => $campaign_id,
        'active' => 'Y'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Create the list first
    $list_id = preg_replace('/[^0-9]/', '', $_POST['list_id']);
    $list_name = $_POST['list_name'] ?: 'List_' . $list_id;
    $list_description = $_POST['list_description'];
    $campaign_id = $_POST['campaign_id'];
    
    // API configuration
    $api_url = "http://localhost/vicidial/non_agent_api.php";
    $api_user = "YOUR_API_USER";
    $api_pass = "YOUR_API_PASSWORD";
    
    $api_response = createListViaAPI($api_url, $api_user, $api_pass, 
                                    $list_id, $list_name, $list_description, $campaign_id);
    
    // 2. Handle file upload if list was created successfully
    if (strpos($api_response, 'SUCCESS') !== false && isset($_FILES['lead_file'])) {
        $upload_dir = '/usr/share/astguiclient/LEADS_IN/';
        $filename = $list_id . '_' . date('YmdHis') . '.txt';
        $upload_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['lead_file']['tmp_name'], $upload_path)) {
            // Optional: Automatically trigger the import script
            $output = shell_exec("/usr/share/astguiclient/VICIDIAL_IN_new_leads_file.pl --quiet 2>&1");
            $message = "List created and file uploaded successfully. Import queued.";
        } else {
            $message = "List created but file upload failed.";
        }
    } else {
        $message = "Error creating list: " . $api_response;
    }
}

// Get available campaigns for dropdown
function getCampaigns() {
    // You'd typically query the database
    // Example: SELECT campaign_id, campaign_name FROM vicidial_campaigns WHERE active='Y'
    return [
        'SALES2026' => 'Sales Campaign 2026',
        'SUPPORT' => 'Customer Support',
        'SURVEY' => 'Survey Campaign'
    ];
}

$campaigns = getCampaigns();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Leads & Create List</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
        input[type="text"], input[type="file"], textarea, select { 
            width: 100%; 
            padding: 8px; 
            box-sizing: border-box; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
        }
        select { background: white; }
        input[type="submit"] { 
            background: #4CAF50; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover { background: #45a049; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .help-text { font-size: 12px; color: #666; margin-top: 5px; }
        .required { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Lead File & Create List</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="campaign_id">Select Campaign <span class="required">*</span></label>
                <select id="campaign_id" name="campaign_id" required>
                    <option value="">-- Select a Campaign --</option>
                    <?php foreach ($campaigns as $id => $name): ?>
                        <option value="<?php echo htmlspecialchars($id); ?>">
                            <?php echo htmlspecialchars($name) . " ($id)"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="help-text">Choose which campaign this list belongs to</div>
            </div>
            
            <div class="form-group">
                <label for="list_id">List ID (digits only):</label>
                <input type="text" id="list_id" name="list_id" pattern="[0-9]+" 
                       placeholder="e.g., 260314 or leave blank for auto-generate">
                <div class="help-text">Leave blank to auto-generate (YYYYMMDDX format)</div>
            </div>
            
            <div class="form-group">
                <label for="list_name">List Name:</label>
                <input type="text" id="list_name" name="list_name" 
                       placeholder="Leave blank to use List ID">
            </div>
            
            <div class="form-group">
                <label for="list_description">List Description <span class="required">*</span></label>
                <textarea id="list_description" name="list_description" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="lead_file">Select Lead File <span class="required">*</span></label>
                <input type="file" id="lead_file" name="lead_file" accept=".txt,.csv" required>
                <div class="help-text">
                    File must be pipe-delimited format. 
                    <a href="#" onclick="alert('Format: vendor_lead_code|source_id|list_id|phone_code|phone_number|...')">View format</a>
                </div>
            </div>
            
            <div class="form-group">
                <input type="checkbox" id="auto_import" name="auto_import" checked>
                <label for="auto_import" style="display: inline;">Auto-import after upload</label>
            </div>
            
            <input type="submit" value="Upload File & Create List">
        </form>
        
        <hr>
        <h3>File Format Example</h3>
        <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px;">
TEST001|TEST|999|1|5551234567|Mr|John||Doe|123 Main St||||Anytown|CA||12345|USA||||||
TEST002|TEST|999|1|5552345678|Ms|Jane||Smith|456 Oak Ave||||Othercity|NY||67890|USA||||||</pre>
    </div>
</body>
</html>
