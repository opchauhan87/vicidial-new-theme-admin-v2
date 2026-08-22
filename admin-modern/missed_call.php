<link href="css/style.css" rel="stylesheet" />
<link href="css/style_new.css" rel="stylesheet" />
<link href="js/bootstrap/dist/css/bootstrap.css" rel="stylesheet"/>
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css">
<link href='fonts/font-awesome-4.2.0/css/font-awesome.css' rel='stylesheet'/>
<link rel='shortcut icon' href='favicon.ico' type='image/x-icon'/>
<?php 
# AST_CLOSERstats.php
# 
# Copyright (C) 2022  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# CHANGES:
# 60619-1714 - Added variable filtering to eliminate SQL injection attack threat
#            - Added required user/pass to gain access to this page
# 60905-1326 - Added queue time stats
# 71008-1436 - Added shift to be defined in dbconnect_mysqli.php
# 71025-0021 - Added status breakdown
# 71218-1155 - Added end_date for multi-day reports
# 80430-1920 - Added Customer hangup cause stats
# 80709-0331 - Added time stats to call statuses
# 80722-2149 - Added Status Category stats
# 81015-0705 - Added IVR calls count
# 81024-0037 - Added multi-select inbound-groups
# 81105-2118 - Added Answered calls 15-minute breakdown
# 81109-2340 - Added custom indicators section
# 90116-1040 - Rewrite of the 15-minute sections to speed it up and allow multi-day calculations
# 90310-2037 - Admin header
# 90508-0644 - Changed to PHP long tags
# 90524-2231 - Changed to use functions.php for seconds to HH:MM:SS conversion
# 90801-0921 - Added in-group name to pulldown
# 91214-0955 - Added INITIAL QUEUE POSITION BREAKDOWN
# 100206-1454 - Fixed TMR(service level) calculation
# 100214-1421 - Sort menu alphabetically
# 100216-0042 - Added popup date selector
# 100709-1809 - Added system setting slave server option
# 100802-2347 - Added User Group Allowed Reports option validation
# 100913-1634 - Added DID option to select by DIDs instead of In-groups
# 100914-1326 - Added lookup for user_level 7 users to set to reports only which will remove other admin links
# 110703-1759 - Added download option
# 111103-0632 - Added MAXCAL as a drop status
# 111103-2003 - Added user_group restrictions for selecting in-groups
# 120224-0910 - Added HTML display option with bar graphs
# 120730-0724 - Small fix for HTML output
# 130124-1719 - Added email report support
# 130414-1429 - Added report logging
# 130610-1023 - Finalized changing of all ereg instances to preg
# 130621-0805 - Added filtering of input to prevent SQL injection attacks and new user auth
# 130704-0936 - Fixed issue #675
# 130901-0816 - Changed to mysqli PHP functions
# 140108-0746 - Added webserver and hostname to report logging
# 140328-0005 - Converted division calculations to use MathZDC function
# 141114-0009 - Finalized adding QXZ translation to all admin files
# 141128-0858 - Code cleanup for QXZ functions
# 141230-0942 - Added code for on-the-fly language translations display
# 150516-1259 - Fixed Javascript element problem, Issue #857
# 150522-1304 - Fixed issue with missing calls from user stats section
# 150928-1234 - Separated User Group permissions for this report by in-group and by DID
# 151124-1236 - Changed bottom chart to pull all time segments
# 151125-1633 - Added search archive option
# 160227-1129 - Uniform form format
# 160714-2348 - Added and tested ChartJS features for more aesthetically appealing graphs
# 160819-0054 - Fixed chart bugs
# 170227-1715 - Fix for default HTML report format, issue #997
# 170324-0740 - Fix for daylight savings time issue
# 170409-1559 - Added IP List validation code
# 170829-0040 - Added screen color settings
# 171012-2015 - Fixed javascript/apache errors with graphs
# 180323-2306 - Fix for user time calculation, subtracted queue_seconds
# 180712-1508 - Fix for rare allowed reports issue
# 190508-1900 - Streamlined DID check to optimize page load
# 190930-1345 - Fixed PHP7 array issue
# 200924-0917 - Added two new drop calculations
# 210525-1715 - Fixed help display, modification for more call details
# 210821-1521 - Added AHT to CUSTOM INDICATOR section
# 210923-2248 - Added OCR, SL-1 & SL-2 stats to CUSTOM INDICATOR section
# 211022-0735 - Added IR_SLA_all_statuses options.php setting
# 212207-2217 - Added IQNANQ to drop SQL calculation queries
# 220303-0850 - Added allow_web_debug system setting
#

$startMS = microtime();

require("dbconnect_mysqli.php");
require("functions.php");

# Inbound reports, use all statuses for SLA calculation
$IR_SLA_all_statuses=0;
# if options file exists, use the override values for the above variables
#   see the options-example.php file for more information
if (file_exists('options.php'))
	{
	require_once('options.php');
	}
	
$PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
$PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
$PHP_SELF=$_SERVER['PHP_SELF'];
$PHP_SELF = preg_replace('/\.php.*/i','.php',$PHP_SELF);
if (isset($_GET["group"]))				{$group=$_GET["group"];}
	elseif (isset($_POST["group"]))		{$group=$_POST["group"];}
if (isset($_GET["query_date"]))				{$query_date=$_GET["query_date"];}
	elseif (isset($_POST["query_date"]))	{$query_date=$_POST["query_date"];}
if (isset($_GET["end_date"]))			{$end_date=$_GET["end_date"];}
	elseif (isset($_POST["end_date"]))	{$end_date=$_POST["end_date"];}
if (isset($_GET["shift"]))				{$shift=$_GET["shift"];}
	elseif (isset($_POST["shift"]))		{$shift=$_POST["shift"];}
if (isset($_GET["submit"]))				{$submit=$_GET["submit"];}
	elseif (isset($_POST["submit"]))	{$submit=$_POST["submit"];}
if (isset($_GET["SUBMIT"]))				{$SUBMIT=$_GET["SUBMIT"];}
	elseif (isset($_POST["SUBMIT"]))	{$SUBMIT=$_POST["SUBMIT"];}
if (isset($_GET["DID"]))				{$DID=$_GET["DID"];}
	elseif (isset($_POST["DID"]))		{$DID=$_POST["DID"];}
if (isset($_GET["EMAIL"]))				{$EMAIL=$_GET["EMAIL"];}
	elseif (isset($_POST["EMAIL"]))		{$EMAIL=$_POST["EMAIL"];}
if (isset($_GET["DB"]))					{$DB=$_GET["DB"];}
	elseif (isset($_POST["DB"]))		{$DB=$_POST["DB"];}
if (isset($_GET["file_download"]))			{$file_download=$_GET["file_download"];}
	elseif (isset($_POST["file_download"]))	{$file_download=$_POST["file_download"];}
if (isset($_GET["report_display_type"]))			{$report_display_type=$_GET["report_display_type"];}
	elseif (isset($_POST["report_display_type"]))	{$report_display_type=$_POST["report_display_type"];}
if (isset($_GET["search_archived_data"]))			{$search_archived_data=$_GET["search_archived_data"];}
	elseif (isset($_POST["search_archived_data"]))	{$search_archived_data=$_POST["search_archived_data"];}

$DB=preg_replace("/[^0-9a-zA-Z]/","",$DB);

$MT[0]='0';
$NOW_DATE = date("Y-m-d");
$NOW_TIME = date("Y-m-d H:i:s");
$STARTtime = date("U");
if (!isset($group)) {$group = array();}
if (!isset($query_date)) {$query_date = $NOW_DATE;}
if (!isset($end_date)) {$end_date = $NOW_DATE;}
if (strlen($shift)<2) {$shift='ALL';}

$report_name = 'Missed Call Report';
$db_source = 'M';

# $test_table_name="vicidial_closer_log";

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,outbound_autodial_active,slave_db_server,reports_use_slave_db,enable_languages,language_method,report_default_format,allow_web_debug FROM system_settings;";
$rslt=mysql_to_mysqli($stmt, $link);
#if ($DB) {$MAIN.="$stmt\n";}
$qm_conf_ct = mysqli_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$non_latin =					$row[0];
	$outbound_autodial_active =		$row[1];
	$slave_db_server =				$row[2];
	$reports_use_slave_db =			$row[3];
	$SSenable_languages =			$row[4];
	$SSlanguage_method =			$row[5];
	$SSreport_default_format =		$row[6];
	$SSallow_web_debug =			$row[7];
	}
if ($SSallow_web_debug < 1) {$DB=0;}
if (strlen($report_display_type)<2) {$report_display_type = $SSreport_default_format;}
##### END SETTINGS LOOKUP #####
###########################################
$query_date = preg_replace('/[^- \:\_0-9a-zA-Z]/', '', $query_date);
$end_date = preg_replace('/[^- \:\_0-9a-zA-Z]/', '', $end_date);
$SUBMIT = preg_replace('/[^-_0-9a-zA-Z]/', '', $SUBMIT);
$submit = preg_replace('/[^-_0-9a-zA-Z]/', '', $submit);
$file_download = preg_replace('/[^-_0-9a-zA-Z]/', '', $file_download);
$search_archived_data = preg_replace('/[^-_0-9a-zA-Z]/', '', $search_archived_data);
$report_display_type = preg_replace('/[^-_0-9a-zA-Z]/', '', $report_display_type);
$DID = preg_replace('/[^-_0-9a-zA-Z]/', '', $DID);
$EMAIL = preg_replace('/[^-_0-9a-zA-Z]/', '', $EMAIL);

# Variables filtered further down in the code
# $group

if ($non_latin < 1)
	{
	$PHP_AUTH_USER = preg_replace('/[^-_0-9a-zA-Z]/', '', $PHP_AUTH_USER);
	$PHP_AUTH_PW = preg_replace('/[^-_0-9a-zA-Z]/', '', $PHP_AUTH_PW);
	$shift = preg_replace('/[^-_0-9a-zA-Z]/', '', $shift);
	}
else
	{
	$PHP_AUTH_USER = preg_replace('/[^-_0-9\p{L}]/u', '', $PHP_AUTH_USER);
	$PHP_AUTH_PW = preg_replace('/[^-_0-9\p{L}]/u', '', $PHP_AUTH_PW);
	$shift = preg_replace('/[^-_0-9\p{L}]/u', '', $shift);
	}


$auth=0;
$reports_auth=0;
$admin_auth=0;
$auth_message = user_authorization($PHP_AUTH_USER,$PHP_AUTH_PW,'REPORTS',1,0);
if ($auth_message == 'GOOD')
	{$auth=1;}

if ($auth > 0)
	{
	$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and user_level > 7 and view_reports='1';";
	if ($DB) {echo "|$stmt|\n";}
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$admin_auth=$row[0];

	$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and user_level > 6 and view_reports='1';";
	if ($DB) {echo "|$stmt|\n";}
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$reports_auth=$row[0];

	if ($reports_auth < 1)
		{
		$VDdisplayMESSAGE = _QXZ("You are not allowed to view reports");
		Header ("Content-type: text/html; charset=utf-8");
		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
		exit;
		}
	if ( ($reports_auth > 0) and ($admin_auth < 1) )
		{
		$ADD=999999;
		$reports_only_user=1;
		}
	}
else
	{
	$VDdisplayMESSAGE = _QXZ("Login incorrect, please try again");
	if ($auth_message == 'LOCK')
		{
		$VDdisplayMESSAGE = _QXZ("Too many login attempts, try again in 15 minutes");
		Header ("Content-type: text/html; charset=utf-8");
		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
		exit;
		}
	if ($auth_message == 'IPBLOCK')
		{
		$VDdisplayMESSAGE = _QXZ("Your IP Address is not allowed") . ": $ip";
		Header ("Content-type: text/html; charset=utf-8");
		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
		exit;
		}
	Header("WWW-Authenticate: Basic realm=\"CONTACT-CENTER-ADMIN\"");
	Header("HTTP/1.0 401 Unauthorized");
	echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$PHP_AUTH_PW|$auth_message|\n";
	exit;
	}

$stmt="SELECT user_group from vicidial_users where user='$PHP_AUTH_USER';";
if ($DB) {$MAIN.="|$stmt|\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$row=mysqli_fetch_row($rslt);
$LOGuser_group =			$row[0];

$stmt="SELECT allowed_campaigns,allowed_reports,admin_viewable_groups,admin_viewable_call_times from vicidial_user_groups where user_group='$LOGuser_group';";
if ($DB) {$MAIN.="|$stmt|\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$row=mysqli_fetch_row($rslt);
$LOGallowed_campaigns =			$row[0];
$LOGallowed_reports =			"$row[1],";
$LOGadmin_viewable_groups =		$row[2];
$LOGadmin_viewable_call_times =	$row[3];

if ( (!preg_match("/$report_name,/",$LOGallowed_reports)) and (!preg_match("/ALL REPORTS/",$LOGallowed_reports)) )
	{
    Header("WWW-Authenticate: Basic realm=\"CONTACT-CENTER-ADMIN\"");
    Header("HTTP/1.0 401 Unauthorized");
    echo _QXZ("You are not allowed to view this report").": |$PHP_AUTH_USER|$report_name|\n";
    exit;
	}


$stmt="select group_id,group_name,8 from vicidial_inbound_groups where group_handling='PHONE' $LOGadmin_viewable_groupsSQL order by group_id;";
if ($DID=='Y')
	{
	$stmt="select did_pattern,did_description,did_id from vicidial_inbound_dids $whereLOGadmin_viewable_groupsSQL order by did_pattern;";
	}

$rslt=mysql_to_mysqli($stmt, $link);
if ($DB) {$MAIN.="$stmt\n";}
$groups_to_print = mysqli_num_rows($rslt);
$i=0;
$LISTgroups=array();
$LISTgroup_names=array();
$LISTgroup_ids=array();
$LISTgroups[$i]='---NONE---';
$LISTgroup_names[$i]=_QXZ("None selected");
$i++;
$groups_to_print++;
$groups_string='|';
while ($i < $groups_to_print)
	{
	$row=mysqli_fetch_row($rslt);
	$LISTgroups[$i] =		$row[0];
	$LISTgroup_names[$i] =	$row[1];
	$LISTgroup_ids[$i] =	$row[2];
	$groups_string .= "$LISTgroups[$i]|";
	$i++;
	}

$i=0;
$group_string='|';
$group_ct = count($group);
while($i < $group_ct)
	{
	$group[$i] = preg_replace('/[^-_0-9\p{L}]/u', '', $group[$i]);
	if ( (strlen($group[$i]) > 0) and (preg_match("/\|$group[$i]\|/",$groups_string)) )
		{
		$group_string .= "$group[$i]|";
		$group_SQL .= "'$group[$i]',";
		$groupQS .= "&group[]=$group[$i]";
		}
	$i++;
	}
if ( (preg_match('/\s\-\-NONE\-\-\s/',$group_string) ) or ($group_ct < 1) )
	{
	$group_SQL = "''";
#	$group_SQL = "group_id IN('')";
	}
else
	{
	$group_SQL = preg_replace('/,$/i', '',$group_SQL);
#	$group_SQL = "group_id IN($group_SQL)";
	}
if (strlen($group_SQL)<3) {$group_SQL="''";}

##### BEGIN log visit to the vicidial_report_log table #####
$LOGip = getenv("REMOTE_ADDR");
$LOGbrowser = getenv("HTTP_USER_AGENT");
$LOGscript_name = getenv("SCRIPT_NAME");
$LOGserver_name = getenv("SERVER_NAME");
$LOGserver_port = getenv("SERVER_PORT");
$LOGrequest_uri = getenv("REQUEST_URI");
$LOGhttp_referer = getenv("HTTP_REFERER");
$LOGbrowser=preg_replace("/<|>|\'|\"|\\\\/","",$LOGbrowser);
$LOGrequest_uri=preg_replace("/<|>|\'|\"|\\\\/","",$LOGrequest_uri);
$LOGhttp_referer=preg_replace("/<|>|\'|\"|\\\\/","",$LOGhttp_referer);
if (preg_match("/443/i",$LOGserver_port)) {$HTTPprotocol = 'https://';}
  else {$HTTPprotocol = 'http://';}
if (($LOGserver_port == '80') or ($LOGserver_port == '443') ) {$LOGserver_port='';}
else {$LOGserver_port = ":$LOGserver_port";}
$LOGfull_url = "$HTTPprotocol$LOGserver_name$LOGserver_port$LOGrequest_uri";

$LOGhostname = php_uname('n');
if (strlen($LOGhostname)<1) {$LOGhostname='X';}
if (strlen($LOGserver_name)<1) {$LOGserver_name='X';}



if ( (strlen($slave_db_server)>5) and (preg_match("/$report_name/",$reports_use_slave_db)) )
	{
	mysqli_close($link);
	$use_slave_server=1;
	$db_source = 'S';
	require("dbconnect_mysqli.php");
	$MAIN.="<!-- Using slave server $slave_db_server $db_source -->\n";
	}


$NWB = "<IMG SRC=\"help.png\" onClick=\"FillAndShowHelpDiv(event, '";
$NWE = "')\" WIDTH=20 HEIGHT=20 BORDER=0 ALT=\"HELP\" ALIGN=TOP>";

$HTML_head.="<HTML>\n";
$HTML_head.="<HEAD>\n";
$HTML_head.="<meta charset=\"UTF-8\">\n";
$HTML_head.="<STYLE type=\"text/css\">\n";
$HTML_head.="<!--\n";
$HTML_head.="   .green {color: white; background-color: green}\n";
$HTML_head.="   .red {color: white; background-color: red}\n";
$HTML_head.="   .blue {color: white; background-color: blue}\n";
$HTML_head.="   .purple {color: white; background-color: purple}\n";
$HTML_head.="-->\n";
$HTML_head.=" </STYLE>\n";

$HTML_head.="<script language=\"JavaScript\" src=\"calendar_db.js\"></script>\n";
$HTML_head.="<link rel=\"stylesheet\" href=\"calendar.css\">\n";

$HTML_head.="<link rel=\"stylesheet\" type=\"text/css\" href=\"vicidial_stylesheet.php\">\n";
$HTML_head.="<script language=\"JavaScript\" src=\"help.js\"></script>\n";
$HTML_head.="<div id='HelpDisplayDiv' class='help_info' style='display:none;'></div>";

$HTML_text.="<script language=\"JavaScript\">\n";
$HTML_text.="function openNewWindow(url)\n";
$HTML_text.="  {\n";
$HTML_text.="  window.open (url,\"\",'width=620,height=300,scrollbars=yes,menubar=yes,address=yes');\n";
$HTML_text.="  }\n";
$HTML_text.="</script>\n";

$HTML_head.="<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">\n";
$HTML_head.="<TITLE>"._QXZ("$report_name")."</TITLE></HEAD><BODY BGCOLOR=WHITE marginheight=0 marginwidth=0 leftmargin=0 topmargin=0>\n";

$HTML_text.='<div id="pcont4" class="container-fluid">
		<div class="cl-mcont">
        <div class="row">
		<div class="col-md-12 no-padding"><div class="block-flat"><div class="header"> <h3 class="pull-left"> '._QXZ("$report_name").'</h3><div class="pull-right"><a class="btn btn-info" href="./admin.php?ADD=999999">'._QXZ("BACK").'</a></div> </div>
		<div class="col-md-12"><div class="content">';
//$HTML_text.="<TABLE CELLPADDING=3 CELLSPACING=0><TR><TD>";
//$HTML_text.="<b>"._QXZ("$report_name")."</b> $NWB#usergroup_login$NWE\n";
$HTML_text.="<FORM ACTION=\"$PHP_SELF\" METHOD=GET name=vicidial_report id=vicidial_report>\n";
$HTML_text.="<TABLE CELLSPACING=3 class=''>";
//$HTML_text.="<b>"._QXZ("$report_name")."</b> $NWB#usergroup_login$NWE\n";
$HTML_text.="<TR><TD class=\"col-md-4\" VALIGN=TOP><div class='col-lg-10 no-padding'><div class=\"form-group custom-input mb-10\"><INPUT class='form-control' TYPE=TEXT NAME=query_date SIZE=10 MAXLENGTH=10 VALUE=\"$query_date\"><label>"._QXZ("Date Range")."</label></div></div>";
$HTML_text.="<div class=\"col-md-2 text-center p-18\">";
$HTML_text.="<script language=\"JavaScript\">\n";
$HTML_text.="function openNewWindow(url)\n";
$HTML_text.="  {\n";
$HTML_text.="  window.open (url,\"\",'width=620,height=300,scrollbars=yes,menubar=yes,address=yes');\n";
$HTML_text.="  }\n";
$HTML_text.="var o_cal = new tcal ({\n";
$HTML_text.="	// form name\n";
$HTML_text.="	'formname': 'vicidial_report',\n";
$HTML_text.="	// input name\n";
$HTML_text.="	'controlname': 'query_date'\n";
$HTML_text.="});\n";
$HTML_text.="o_cal.a_tpl.yearscroll = false;\n";
$HTML_text.="// o_cal.a_tpl.weekstart = 1; // Monday week start\n";
$HTML_text.="</script></div></div>\n";
$HTML_text.="</div>";

$HTML_text.="<div class=\"col-md-12\" style=\"text-align:center;\"><label>"._QXZ("to")."</label></div>  <div class='col-md-12'><div class='col-lg-10 no-padding'><div class=\"form-group custom-input mb-10\"><INPUT class='form-control'  TYPE=TEXT NAME=end_date SIZE=10 MAXLENGTH=10 VALUE=\"$end_date\"></div></div>";
$HTML_text.="<div class=\"col-md-2 text-center p-18\">";
$HTML_text.="<script language=\"JavaScript\">\n";
$HTML_text.="var o_cal = new tcal ({\n";
$HTML_text.="	// form name\n";
$HTML_text.="	'formname': 'vicidial_report',\n";
$HTML_text.="	// input name\n";
$HTML_text.="	'controlname': 'end_date'\n";
$HTML_text.="});\n";
$HTML_text.="o_cal.a_tpl.yearscroll = false;\n";
$HTML_text.="// o_cal.a_tpl.weekstart = 1; // Monday week start\n";
$HTML_text.="</script>\n";
$HTML_text.="</div>";
$HTML_text.="<br><br><br><br>";



$HTML_text.="</TD>\n";

$HTML_text.="<TD class=\"col-md-4\"  VALIGN=TOP>\n";
$HTML_text.="<div class=\"col-lg-12\"><div class='form-group custom-input mb-10'><SELECT class='form-control' SIZE=5 NAME=group[] multiple>\n";

$o=0;
while ($groups_to_print > $o)
	{
	if (preg_match("/\|$LISTgroups[$o]\|/",$group_string)) 
		{$HTML_text.="<option selected value=\"$LISTgroups[$o]\">$LISTgroups[$o] - $LISTgroup_names[$o]</option>\n";}
	else
		{$HTML_text.="<option value=\"$LISTgroups[$o]\">$LISTgroups[$o] - $LISTgroup_names[$o]</option>\n";}
	$o++;
	}
$HTML_text.="</SELECT><label>";
$HTML_text.=""._QXZ("Inbound Groups").": \n";
$HTML_text.="</label></div></div>\n";



$HTML_text.="</TD></tr>";
$HTML_text.="<TR>";
$HTML_text.="<TD colspan='2'>";
$HTML_text.="<div class=\"col-md-12 text-center\">";
//$HTML_text.="<a href=\"$PHP_SELF?DB=$DB&query_date=$query_date&end_date=$end_date&query_date_D=$query_date_D&query_date_T=$query_date_T&end_date_D=$end_date_D&end_date_T=$end_date_T$user_groupQS$call_statusQS&file_download=1&SUBMIT=$SUBMIT&search_archived_data=$search_archived_data\">"._QXZ("DOWNLOAD")."</a> |";
$HTML_text.="<INPUT TYPE=SUBMIT NAME=SUBMIT  VALUE='"._QXZ("SUBMIT")."' class='btn btn-success'>\n";
$HTML_text.="<a class=\"btn btn-bdr-blue\" href=\"$PHP_SELF?DB=$DB&query_date=$query_date&end_date=$end_date&query_date_D=$query_date_D&query_date_T=$query_date_T&end_date_D=$end_date_D&end_date_T=$end_date_T$groupQS$user_groupQS$call_statusQS&file_download=1&SUBMIT=$SUBMIT&search_archived_data=$search_archived_data\">"._QXZ("DOWNLOAD")."</a>";
$HTML_text.="</div>";
$HTML_text.="</TD>";
$HTML_text.="</TR>";
$HTML_text.="</TABLE>";
$HTML_text.="</FORM>\n";

	if ($file_download < 1)
		{
		$ASCII_text.="<font size=2><PRE class='m-0'>\n";
		}
	else
		{
		$CSV_text .= _QXZ("Missed Call Report",24).": $user                     $NOW_TIME ($db_source)\n";
		}
		
$query_date_BEGIN = "$query_date 00:00:00";   
$query_date_END = "$end_date 23:59:59";

if ($SUBMIT) 
	{
	//~ $HTML_text2 .= "<span colspan=4><font class='top_head_key'><b>&nbsp;"._QXZ("Usergroup Login Report Report").":&nbsp;&nbsp;</b></font></span>";
	//~ $HTML_text2 .= "<span colspan=3><font class='top_settings_val'>&nbsp;</font></span>";
	//~ $HTML_text2 .= "<span colspan=4><font class='top_head_val'>$NOW_TIME\n</font></span>";
	$HTML_text2.="<table border='0' cellpadding='3' cellspacing='1' class=\"table_list hover centerline\">";
	$HTML_text2.="<thead>";
	
	$HTML_text2.="<tr bgcolor='#".$SSstd_row1_background."'>";
	$HTML_text2.="<th><font size='2'>"._QXZ("CALL TIME")."</font></th>";
	$HTML_text2.="<th><font size='2'>"._QXZ("INBOUND GROUP")."</font></th>";
	$HTML_text2.="<th><font size='2'>"._QXZ("CALL DURATION")."</font></th>";
	$HTML_text2.="<th><font size='2'>"._QXZ("PHONE NUMBER")."</font></th>";
	$HTML_text2.="<th><font size='2'>"._QXZ("AGENT")."</font></th>";
	$HTML_text2.="<th><font size='2'>"._QXZ("LIST ID")."</font></th>";
	//~ $HTML_text2.="<th><font size='2'>"._QXZ("SERVER IP")."</font></th>";
	//~ $HTML_text2.="<th><font size='2'>"._QXZ("COMPUTER IP")."</font></th>";
	//~ $HTML_text2.="<th><font size='2'>"._QXZ("EXTENSION")."</font></th>";
	//~ $HTML_text2.="<th><font size='2'>"._QXZ("BROWSER")."</font></th>";
	//~ $HTML_text2.="<th><font size='2'>"._QXZ("PHONE LOGIN")."</font></th>";
	//~ $HTML_text2.="<th><font size='2'>"._QXZ("SERVER PHONE")."</font></th>";
	//~ $HTML_text2.="<th><font size='2'>"._QXZ("PHONE IP")."</font></th>";
	$HTML_text2.="</tr>	</thead>\n";

	//~ $CSV_text="\""._QXZ("User group login report")."\",\""._QXZ("User groups").":\",\""._QXZ("$user_group_string")."\"\n\n";
	$CSV_text.="\""._QXZ("CALL TIME")."\",\""._QXZ("INBOUND GROUP")."\",\""._QXZ("CALL DURATION")."\",\""._QXZ("PHONE NUMBER")."\",\""._QXZ("AGENT")."\",\""._QXZ("LIST IDD")."\"\n";
	//~ $stmt="select distinct user, substr(full_name,1,30) as fullname, full_name from vicidial_users where user_group in ($user_group_SQL) order by user";
	
	//~ $stmt="select distinct user, substr(full_name,1,30) as fullname, full_name from vicidial_users $user_group_SQL order by user";
	 $stmt="SELECT closecallid,lead_id,list_id,campaign_id,call_date,length_in_sec,status,phone_code,phone_number,user,comments,queue_seconds,term_reason from vicidial_closer_log where call_date >= '$query_date_BEGIN' and call_date <= '$query_date_END' and (status = 'DROP' or status='TIMEOT')  and campaign_id in($group_SQL) and campaign_id NOT IN('AGENTDIRECT_CHAT') and (comments!='CHAT' or comments IS NULL) order by call_date DESC";
	$rslt=mysql_to_mysqli($stmt, $link);
	while ($row=mysqli_fetch_array($rslt)) 
		{
			$CSV_text.="\"$row[call_date]\",\"$row[campaign_id]\",\"$row[length_in_sec]\",\"$row[phone_number]\",\"$row[user]\",\"$row[list_id]\"\n";
			
			if($row["user"] == 'VDCL'){
				$call_user = "No User";
			}else{
				$call_user = $row["user"];
			}
			//$HTML_text2.="<tr bgcolor='#".$SSstd_row2_background."'>";
			$HTML_text2.="<tr>";
			$HTML_text2.="<td><font size='2'>".$row["call_date"]."</font></td>";
			$HTML_text2.="<td><font size='2'>".$row["campaign_id"]."</font></td>";
			$HTML_text2.="<td><font size='2'>".$row["length_in_sec"]."</font></td>";
			$HTML_text2.="<td><font size='2'>".$row["phone_number"]."</font></td>";
			$HTML_text2.="<td><font size='2'>".$call_user."</font></td>";
			$HTML_text2.="<td><font size='2'>".$row["list_id"]."</font></td>";
			//~ $HTML_text2.="<td><font size='2'>".$data_row["server_ip"]."</font></td>";
			//~ $HTML_text2.="<td><font size='2'>".$data_row["computer_ip"]."</font></td>";
			//~ $HTML_text2.="<td><font size='2'>".$data_row["ext"]."</font></td>";
			//~ $HTML_text2.="<td><font size='2'>".$browser."</font></td>";
			//~ $HTML_text2.="<td><font size='2'>".$data_row["phone_login"]."</font></td>";
			//~ $HTML_text2.="<td><font size='2'>".$data_row["server_phone"]."</font></td>";
			//~ $HTML_text2.="<td><font size='2'>".$data_row["phone_ip"]."</font></td>";
			$HTML_text2.="</tr>\n";
			
		}
	$HTML_text2.="</table>\n";
	}

if ($file_download>0) 
	{
	$FILE_TIME = date("Ymd-His");
	$CSVfilename = "Missed_call_report_$FILE_TIME.csv";
	$CSV_text=preg_replace('/\n +,/', ',', $CSV_text);
	$CSV_text=preg_replace('/ +\"/', '"', $CSV_text);
	$CSV_text=preg_replace('/\" +/', '"', $CSV_text);

	// We'll be outputting a TXT file
	header('Content-type: application/octet-stream');
	// It will be called LIST_101_20090209-121212.txt
	header("Content-Disposition: attachment; filename=\"$CSVfilename\"");
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	ob_clean();
	flush();

	echo "$CSV_text";
	}
else
	{
	header("Content-type: text/html; charset=utf-8");
#	$JS_onload.="}\n";
#	if ($report_display_type=='HTML') {$JS_text.=$JS_onload;}
#	$JS_text.="</script>\n";

	
	if ($SUBMIT) 
	{
		$HTML_text.=$HTML_text2;
	}else{
		$HTML_text.=$ASCII_text."</PRE></font>";
	}	

	echo $HTML_head;
#	echo $JS_text;
	$short_header=0;
	require("admin_header.php");
	echo $HTML_text."</div></div></div></div></div></div></div>";
	flush();
	}

if ($db_source == 'S')
	{
	mysqli_close($link);
	$use_slave_server=0;
	$db_source = 'M';
	require("dbconnect_mysqli.php");
	}
exit;




?>
