

<meta charset="utf-8">
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">

<!-- Bootstrap core CSS -->
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<?php
require("dbconnect_mysqli.php");   # Vicidial DB connection
require("functions.php");

// ---------------- CSV DOWNLOAD BLOCK ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['list_id'])) {
    $list_id = mysqli_real_escape_string($link, $_POST['list_id']);
    $filename = "Export_call_notes" . $list_id . "_" . date("Y-m-d_His") . ".csv";

    header('Content-Type: text/xlsx; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Main query
    $query = "SELECT 
        vl.lead_id,vl.entry_date,vl.modify_date,vl.status,vl.user,vl.vendor_lead_code,vl.source_id,vl.gmt_offset_now,
        vl.called_since_last_reset,vl.list_id,vl.phone_number,vl.first_name,vl.last_name,vl.address1,vl.address2,vcn.call_date,
        vl.city,vl.state,vl.province,vl.postal_code,vl.country_code,vl.gender,vl.date_of_birth,vl.alt_phone,
        vl.email, vl.comments,vcn.call_notes
        FROM vicidial_list vl 
        INNER JOIN vicidial_call_notes vcn ON vl.lead_id = vcn.lead_id  
        WHERE vl.list_id = '$list_id'
        ORDER BY vcn.call_date DESC";

    $rows = mysqli_query($link, $query);

    // ? Column headers (auto-detect from query result)
    $headers = [];
    while ($fieldinfo = mysqli_fetch_field($rows)) {
        $headers[] = $fieldinfo->name;
    }
    fputcsv($output, $headers);

    // ? Data rows
    while ($row = mysqli_fetch_assoc($rows)) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit; // stop here, so no HTML is added
}
?>

<!-- ---------------- HTML UI BELOW ---------------- -->
<?php require("admin_header.php"); ?>

<style>
    .card {
        border-radius: 15px;
    }
    .form-select, .btn {
        height: 45px; /* match height */
        font-size: 16px;
    }
    .btn i {
        margin-right: 6px;
    }
</style>
		<head>
		<title>ADMINISTRATION:Reports</title>
		</head>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg p-4 text-center">
        <h2 class="text-primary mb-4">All List comments and noted Download</h2>
        <form method="post" action="">
            <div class="mb-3">
                <label for="list_id" class="form-label fw-bold text-secondary">Select List ID</label>
                <select name="list_id" id="list_id" class="form-select border-primary shadow-sm" required>
                    <option value="">-- Choose a List --</option>
                    <?php
                    $lists = mysqli_query($link, "SELECT list_id, list_name FROM vicidial_lists ORDER BY list_id");
                    while ($row = mysqli_fetch_assoc($lists)) {
                        echo "<option value='" . htmlspecialchars($row['list_id']) . "'>" .
                             htmlspecialchars($row['list_id']) . " - " . htmlspecialchars($row['list_name']) .
                             "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success shadow px-4">
                    <i class="bi bi-download"></i> Download CSV
                </button>
            </div>
        </form>
    </div>
</div>
<?php require("admin_footer.php"); ?>



