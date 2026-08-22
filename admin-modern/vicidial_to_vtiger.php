<?php
// ------------------------- CONFIG -------------------------
$vtigerUrl = "http://192.168.68.150/hnc/webservice.php";
$vtigerUser = "admin";                // Vtiger username
$vtigerAccessKey = "CFWveQp8Q1EG8CZ"; // Vtiger access key (from DB)
$assignedUserId = "19x1";             // Vtiger user ID to assign leads
// ----------------------------------------------------------

// Get VICIdial parameters
$phone   = isset($_GET['phone']) ? $_GET['phone'] : '';
$firstname = isset($_GET['firstname']) ? $_GET['firstname'] : '';
$lastname  = isset($_GET['lastname']) ? $_GET['lastname'] : 'Unknown';
$company   = isset($_GET['company']) ? $_GET['company'] : 'Unknown';
$email     = isset($_GET['email']) ? $_GET['email'] : '';

// Build full name
$fullName = trim($firstname . ' ' . $lastname);

// ------------------- STEP 1: GET CHALLENGE -------------------
$challengeUrl = $vtigerUrl . "?operation=getchallenge&username=" . urlencode($vtigerUser);
$challengeResp = json_decode(file_get_contents($challengeUrl), true);

if (!$challengeResp['success']) {
    die("Error getting challenge token");
}

$token = $challengeResp['result']['token'];

// ------------------- STEP 2: LOGIN -------------------
$accessKey = md5($token . $vtigerAccessKey);

$loginData = array(
    "operation" => "login",
    "username" => $vtigerUser,
    "accessKey" => $accessKey
);

$ch = curl_init($vtigerUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$loginResp = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!$loginResp['success']) {
    die("Login failed: " . $loginResp['error']['message']);
}

$sessionName = $loginResp['result']['sessionName'];

// ------------------- STEP 3: CREATE LEAD -------------------
$leadData = array(
    "lastname" => $lastname,
    "firstname" => $firstname,
    "phone" => $phone,
    "email" => $email,
    "company" => $company,
    "assigned_user_id" => $assignedUserId
);

$createData = array(
    "operation" => "create",
    "sessionName" => $sessionName,
    "elementType" => "Leads",
    "element" => json_encode($leadData)
);

$ch = curl_init($vtigerUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $createData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$createResp = json_decode(curl_exec($ch), true);
curl_close($ch);

// ------------------- STEP 4: RESPONSE -------------------
if ($createResp['success']) {
    echo "Lead created successfully: ID = " . $createResp['result']['id'];
} else {
    echo "Error creating lead: " . $createResp['error']['message'];
}
?>

