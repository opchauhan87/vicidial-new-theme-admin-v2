<link href="css/style.css" rel="stylesheet" />
<link href="js/bootstrap/dist/css/bootstrap.css" rel="stylesheet"/>
<link href='fonts/font-awesome-4.2.0/css/font-awesome.css' rel='stylesheet'/>
<link href='fonts/font-awesome-4.2.0/css/font-awesome.min.css' rel='stylesheet'/>
<link rel='shortcut icon' href='favicon.ico' type='image/x-icon'/>
<script src="js/jquery.js"></script>
    <script src="js/jquery_j.js"></script>
	<script src="js/jquery.cookie/jquery.cookie.js"></script>
  <script src="js/jquery.pushmenu/js/jPushMenu.js"></script>
	<script type="text/javascript" src="js/jquery.nanoscroller/jquery.nanoscroller.js"></script>
	<script type="text/javascript" src="js/jquery.sparkline/jquery.sparkline.min.js"></script>
  <script type="text/javascript" src="js/jquery.ui/jquery-ui.js" ></script>
	<script type="text/javascript" src="js/jquery.gritter/js/jquery.gritter.js"></script>
	<script type="text/javascript" src="js/behaviour/core.js"></script> 

  <script src="js/bootstrap/dist/js/bootstrap.min.js"></script>
<?php
# vdremote.php
# 
# make sure you have added a user to the vicidial_users MySQL table with at 
# least user_level 4 to access this page the first time
#
# Copyright (C) 2024  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# Changes
# 50307-1721 - First version
# 51123-1502 - removed requirement of PHP Globals=on
# 60421-1229 - check GET/POST vars lines with isset to not trigger PHP NOTICES
# 60619-1603 - Added variable filtering to eliminate SQL injection attack threat
# 90508-0644 - Changed to PHP long tags
# 91129-2249 - Replaced SELECT STAR in SQL queries, formatting fixes
# 120223-2135 - Removed logging of good login passwords if webroot writable is enabled
# 130610-1105 - Finalized changing of all ereg instances to preg
# 130616-0005 - Added filtering of input to prevent SQL injection attacks and new user auth
# 130901-0833 - Changed to mysqli PHP functions
# 140328-0005 - Converted division calculations to use MathZDC function
# 141007-2123 - Finalized adding QXZ translation to all admin files
# 141229-1847 - Added code for on-the-fly language translations display
# 170217-1213 - Fixed non-latin auth issue #995
# 210618-1001 - Added CORS support
# 220224-0820 - Added allow_web_debug system setting
# 221006-1454 - Added missing input variable, issue #1383
# 240801-1130 - Code updates for PHP8 compatibility
#

$version = '2.14-16';
$build = '221006-1454';
$php_script='vdremote.php';

$popup_page = './closer_popup.php';
$STARTtime = date("U");
$NOW_DATE = date("Y-m-d");
$NOW_TIME = date("Y-m-d H:i:s");
$date = date("r");
$ip = getenv("REMOTE_ADDR");
$browser = getenv("HTTP_USER_AGENT");

require("dbconnect_mysqli.php");
require("functions.php");


$PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
$PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
$PHP_SELF=$_SERVER['PHP_SELF'];
$PHP_SELF = preg_replace('/\.php.*/i','.php',$PHP_SELF);
if (isset($_GET["ADD"]))			{$ADD=$_GET["ADD"];}
	elseif (isset($_POST["ADD"]))	{$ADD=$_POST["ADD"];}
if (isset($_GET["DB"]))				{$DB=$_GET["DB"];}
	elseif (isset($_POST["DB"]))	{$DB=$_POST["DB"];}
if (isset($_GET["user"]))			{$user=$_GET["user"];}
	elseif (isset($_POST["user"]))	{$user=$_POST["user"];}
if (isset($_GET["force_logout"]))			{$force_logout=$_GET["force_logout"];}
	elseif (isset($_POST["force_logout"]))	{$force_logout=$_POST["force_logout"];}
if (isset($_GET["groups"]))				{$groups=$_GET["groups"];}
	elseif (isset($_POST["groups"]))	{$groups=$_POST["groups"];}
if (isset($_GET["remote_agent_id"]))			{$remote_agent_id=$_GET["remote_agent_id"];}
	elseif (isset($_POST["remote_agent_id"]))	{$remote_agent_id=$_POST["remote_agent_id"];}
if (isset($_GET["user_start"]))				{$user_start=$_GET["user_start"];}
	elseif (isset($_POST["user_start"]))	{$user_start=$_POST["user_start"];}
if (isset($_GET["number_of_lines"]))			{$number_of_lines=$_GET["number_of_lines"];}
	elseif (isset($_POST["number_of_lines"]))	{$number_of_lines=$_POST["number_of_lines"];}
if (isset($_GET["server_ip"]))				{$server_ip=$_GET["server_ip"];}
	elseif (isset($_POST["server_ip"]))		{$server_ip=$_POST["server_ip"];}
if (isset($_GET["conf_exten"]))				{$conf_exten=$_GET["conf_exten"];}
	elseif (isset($_POST["conf_exten"]))	{$conf_exten=$_POST["conf_exten"];}
if (isset($_GET["status"]))				{$status=$_GET["status"];}
	elseif (isset($_POST["status"]))	{$status=$_POST["status"];}
if (isset($_GET["campaign_id"]))			{$campaign_id=$_GET["campaign_id"];}
	elseif (isset($_POST["campaign_id"]))	{$campaign_id=$_POST["campaign_id"];}
if (isset($_GET["groups"]))				{$groups=$_GET["groups"];}
	elseif (isset($_POST["groups"]))	{$groups=$_POST["groups"];}
if (isset($_GET["submit"]))				{$submit=$_GET["submit"];}
	elseif (isset($_POST["submit"]))	{$submit=$_POST["submit"];}
if (isset($_GET["SUBMIT"]))				{$SUBMIT=$_GET["SUBMIT"];}
	elseif (isset($_POST["SUBMIT"]))	{$SUBMIT=$_POST["SUBMIT"];}
if (isset($_GET["query_date"]))				{$query_date=$_GET["query_date"];}
	elseif (isset($_POST["query_date"]))	{$query_date=$_POST["query_date"];}
	
if (!isset($query_date)) {$query_date = $NOW_DATE;}
if (!isset($force_logout)) {$force_logout = 0;}
$DB=preg_replace("/[^0-9a-zA-Z]/","",$DB);

if (file_exists('options.php'))
	{require('options.php');}

if ($force_logout)
	{
	if( (strlen($PHP_AUTH_USER)>0) or (strlen($PHP_AUTH_PW)>0) )
		{
		Header("WWW-Authenticate: Basic realm=\"CONTACT-CENTER-ADMIN\"");
		Header("HTTP/1.0 401 Unauthorized");
		}
    echo _QXZ("You have now logged out. Thank you")."\n";
    exit;
	}

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,outbound_autodial_active,slave_db_server,reports_use_slave_db,custom_fields_enabled,enable_languages,language_method,allow_web_debug FROM system_settings;";
$rslt=mysql_to_mysqli($stmt, $link);
#if ($DB) {echo "$stmt\n";}
$qm_conf_ct = mysqli_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$non_latin =					$row[0];
	$outbound_autodial_active =		$row[1];
	$slave_db_server =				$row[2];
	$reports_use_slave_db =			$row[3];
	$custom_fields_enabled =		$row[4];
	$SSenable_languages =			$row[5];
	$SSlanguage_method =			$row[6];
	$SSallow_web_debug =			$row[7];
	}
if ($SSallow_web_debug < 1) {$DB=0;}
##### END SETTINGS LOOKUP #####
###########################################

$remote_agent_id = preg_replace('/[^-_0-9a-zA-Z]/', '', $remote_agent_id);
$query_date = preg_replace('/[^-_0-9a-zA-Z]/', '', $query_date);
$ADD = preg_replace('/[^-_0-9a-zA-Z]/', '', $ADD);
$force_logout = preg_replace('/[^-_0-9a-zA-Z]/', '', $force_logout);
$user_start = preg_replace('/[^-_0-9a-zA-Z]/', '', $user_start);
$number_of_lines = preg_replace('/[^-_0-9a-zA-Z]/', '', $number_of_lines);
$status = preg_replace('/[^-_0-9a-zA-Z]/', '', $status);
$conf_exten = preg_replace('/[^-_0-9a-zA-Z]/', '', $conf_exten);
$server_ip = preg_replace('/[^-\.\:\_0-9a-zA-Z]/', '', $server_ip);
$submit = preg_replace('/[^-_0-9a-zA-Z]/', '', $submit);
$SUBMIT = preg_replace('/[^-_0-9a-zA-Z]/', '', $SUBMIT);

# Variables filtered further down in the code
# $groups

if ($non_latin < 1)
	{
	$PHP_AUTH_USER = preg_replace('/[^-_0-9a-zA-Z]/', '', $PHP_AUTH_USER);
	$PHP_AUTH_PW = preg_replace('/[^-_0-9a-zA-Z]/', '', $PHP_AUTH_PW);
	$user = preg_replace('/[^-_0-9a-zA-Z]/', '', $user);
	$campaign_id = preg_replace('/[^-_0-9a-zA-Z]/', '', $campaign_id);
	}
else
	{
	$PHP_AUTH_USER = preg_replace('/[^-_0-9\p{L}]/u', '', $PHP_AUTH_USER);
	$PHP_AUTH_PW = preg_replace('/[^-_0-9\p{L}]/u', '', $PHP_AUTH_PW);
	$user = preg_replace('/[^-_0-9\p{L}]/u', '', $user);
	$campaign_id = preg_replace('/[^-_0-9\p{L}]/u', '', $campaign_id);
	}

$stmt="SELECT selected_language from vicidial_users where user='$PHP_AUTH_USER';";
if ($DB) {echo "|$stmt|\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$sl_ct = mysqli_num_rows($rslt);
if ($sl_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$VUselected_language =		$row[0];
	}

$auth=0;
$auth_message = user_authorization($PHP_AUTH_USER,$PHP_AUTH_PW,'REMOTE',1,0);
if ($auth_message == 'GOOD')
	{$auth=1;}

if( (strlen($PHP_AUTH_USER)<2) or (strlen($PHP_AUTH_PW)<2) or (!$auth))
	{
    Header("WWW-Authenticate: Basic realm=\"CONTACT-CENTER-ADMIN\"");
    Header("HTTP/1.0 401 Unauthorized");
    echo _QXZ("Invalid Username/Password").": |$PHP_AUTH_USER|$PHP_AUTH_PW|$auth_message|\n";
    exit;
	}
else
	{
	header ("Content-type: text/html; charset=utf-8");

	if($auth>0)
		{
		$stmt="SELECT full_name from vicidial_users where user='$PHP_AUTH_USER';";
		$rslt=mysql_to_mysqli($stmt, $link);
		$row=mysqli_fetch_row($rslt);
		$LOGfullname=$row[0];

		$stmt="SELECT count(*) from vicidial_remote_agents where user_start='$PHP_AUTH_USER';";
		if ($DB) {echo "|$stmt|\n";}
		$rslt=mysql_to_mysqli($stmt, $link);
		$row=mysqli_fetch_row($rslt);
		$authx=$row[0];

		if($authx>0)
			{
			$stmt="SELECT remote_agent_id,server_ip,number_of_lines from vicidial_remote_agents where user_start='$PHP_AUTH_USER';";
			if ($DB) {echo "|$stmt|\n";}
			$rslt=mysql_to_mysqli($stmt, $link);
			$row=mysqli_fetch_row($rslt);
			$remote_agent_id=$row[0];
			$server_ip=$row[1];
			if (!$number_of_lines) {$number_of_lines=$row[2];}
			}
		else
			{
			echo _QXZ("This remote agent does not exist").": |$PHP_AUTH_USER|\n";
			exit;
			}
		}
	}

echo "<html>\n";
echo "<head>\n";
echo "<meta charset='UTF-8'>";
echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">\n";
if ($ADD==61111)
	{
	echo"<META HTTP-EQUIV=Refresh CONTENT=\"5; URL=$PHP_SELF?ADD=61111&user=$user&DB=$DB\">\n";
	}
echo "<!-- VERSION: $version     BUILD: $build -->\n";
?>
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css">
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,600,400italic,700,800' rel='stylesheet' type='text/css'>
	<link href='//fonts.googleapis.com/css?family=Raleway:300,200,100' rel='stylesheet' type='text/css'>
<STYLE type="text/css">
<!--
   .green {color: white; background-color: green}
   .red {color: white; background-color: red}
   .blue {color: white; background-color: blue}
   .purple {color: white; background-color: purple}
   .yellow {color: black; background-color: yellow}
   .orange {color: black; background-color: orange}
-->
 </STYLE>


<?php
echo "<title>REMOTE AGENTS: $LOGfullname - $PHP_AUTH_USER   ";

if (!$ADD)			{$ADD="31111";}
if ($ADD==31111)	{echo "Modify Remote Agents";}
if ($ADD==41111)	{echo "Modify Remote Agents";}
if ($ADD==61111)	{echo "Remote Agent Status";}
if ($ADD==71111)	{echo "Remote Agent Closer Stats";}


if (strlen($ADD)>4)
	{
	##### get server listing for dynamic pulldown
	$stmt="SELECT server_ip,server_description from servers order by server_ip;";
	$rslt=mysql_to_mysqli($stmt, $link);
	$servers_to_print = mysqli_num_rows($rslt);
	$servers_list='';

	$o=0;
	while ($servers_to_print > $o)
		{
		$rowx=mysqli_fetch_row($rslt);
		$servers_list .= "<option value=\"$rowx[0]\">$rowx[0] - $rowx[1]</option>\n";
		$o++;
		}

	##### get campaigns listing for dynamic pulldown
	$stmt="SELECT campaign_id,campaign_name from vicidial_campaigns order by campaign_id;";
	$rslt=mysql_to_mysqli($stmt, $link);
	$campaigns_to_print = mysqli_num_rows($rslt);
	$campaigns_list='';

	$o=0;
	while ($campaigns_to_print > $o)
		{
		$rowx=mysqli_fetch_row($rslt);
		$campaigns_list .= "<option value=\"$rowx[0]\">$rowx[0] - $rowx[1]</option>\n";
		$o++;
		}

	##### get inbound groups listing for checkboxes
	if ( (($ADD==31111) or ($ADD==31111)) and (!is_array($groups)) )
		{
		$stmt="SELECT closer_campaigns from vicidial_remote_agents where remote_agent_id='" . mysqli_real_escape_string($link, $remote_agent_id) . "';";
		$rslt=mysql_to_mysqli($stmt, $link);
		$row=mysqli_fetch_row($rslt);
		$closer_campaigns =	$row[0];
		$closer_campaigns = preg_replace("/ -$/","",$closer_campaigns);
		$groups = explode(" ", $closer_campaigns);
		}

	$stmt="SELECT group_id,group_name from vicidial_inbound_groups order by group_id;";
	$rslt=mysql_to_mysqli($stmt, $link);
	$groups_to_print = mysqli_num_rows($rslt);
	$groups_list='';
	$groups_value='';

	$o=0;
	while ($groups_to_print > $o)
		{
		$rowx=mysqli_fetch_row($rslt);
		$group_id_value = $rowx[0];
		$group_name_value = $rowx[1];
		$groups_list .= "<input type=\"checkbox\" name=\"groups[]\" value=\"$group_id_value\"";
		$p=0;
		while ($p<100)
			{
			$groups[$p] = preg_replace('/[^-_0-9a-zA-Z]/', '', $groups[$p]);
			if ($group_id_value == $groups[$p]) 
				{
				$groups_list .= " CHECKED";
				$groups_value .= " $group_id_value";
				}
			$p++;
			}
		$groups_list .= "> $group_id_value - $group_name_value<BR>\n";
		$o++;
		}
	if (strlen($groups_value)>2) {$groups_value .= " -";}
	}

?>
</title>

</head>
<BODY BGCOLOR=white marginheight=0 marginwidth=0 leftmargin=0 topmargin=0>
<!-- <CENTER>
 -->
 <div id="cl-wrapper" class="sb-collapsed">
			<div class="col-md-12">
			<div class="col-md-2">
		 <div class="sidebar-logo">
            <div class="logo">
                <a href="./admin.php"><img src="images/logo.png" height="54" border="0" alt="System logo"></a>
            </div>
          </div>
</div>
<div class="col-md-10">
          
<div id="head-nav" class="navbar navbar-default">
    <div class="container-fluid">
      <div class="navbar-collapse">
		  <div class="pull-right spacer" style="padding-left:15px;">
			<a href="admin.php?force_logout=1"><i class="fa fa-power-off" style="color:#00c0ef;font-size: 30px;"></i></a>
		  </div>
        <ul class="nav navbar-nav navbar-right user-nav">
          <li class="dropdown profile_menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img alt="Avatar" src="./images/profile_pic.png"><span style="color:black !important;">1001</span> <b class="caret"></b></a>
         
          </li>
        </ul>	
	
        <span class="col-md-4 pull-right">
		<span class="pull-right spacer">
		<li class="dropdown notifications-menu" style="display:block;">
	   <a title="Call Information" href="#" class="dropdown-toggle" data-toggle="dropdown" style="background-color: transparent" ;=""><i class="fa fa-info-circle" style="color:#00c0ef;font-size: 30px;"></i></a>
	    
	   <ul class="dropdown-menu" style="padding-bottom:0px;padding-left: 16px;padding-right: 14px;margin-left: -6px;margin-top:14px;">
		   <p style="font-size:11px;"><strong>Version:</strong> 2.14-694a <br> <strong>Build: </strong>181005-1738 <br> <strong>Theme Version:</strong> 1.4.0<br><strong>SVN Version:</strong> 3053</p>
	   </ul>
	    </li>
	    </span>

		<span class="pull-left spacer">Friday February 1, 2019 14:42:58 PM</span>
	    </span>
      </div><!--/.nav-collapse animate-collapse -->
    </div>
  </div></div></div></div>
</br></br></br>

<TR><TD ALIGN=LEFT COLSPAN=2>
	
			<div class="fd-tile detail">
    		<div class="page-head"><a href="./vdremote.php"><?php echo _QXZ("MODIFY"); ?></a> | <a href="./vdremote.php?ADD=61111&user=<?php echo "$PHP_AUTH_USER" ?>"><?php echo _QXZ("STATUS"); ?></a> | <a href="./vdremote.php?ADD=71111&user=<?php echo "$PHP_AUTH_USER" ?>"><?php echo _QXZ("INBOUND STATS"); ?></a>	</div>	
			</div>
			
<?php 



######################
# ADD=31111 modify remote agents info in the system
######################

if ($ADD==31111)
	{
	//echo "<FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2>";

	$stmt="SELECT remote_agent_id,user_start,number_of_lines,server_ip,conf_exten,status,campaign_id,closer_campaigns from vicidial_remote_agents where remote_agent_id='" . mysqli_real_escape_string($link, $remote_agent_id) . "';";
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$remote_agent_id =	$row[0];
	$user_start =		$row[1];
	$number_of_lines =	$row[2];
	$server_ip =		$row[3];
	$conf_exten =		$row[4];
	$status =			$row[5];
	$campaign_id =		$row[6];
	echo "<div id=\"\" class=\"container-fluid\">
			  <div class=\"\">
			  <div class=\"row\">
			  <div class=\"col-md-12 no-padding\">
			  <div class=\"block-flat\">
			  <div class=\"header\">
			  <h3>"._QXZ("MODIFY A REMOTE AGENTS ENTRY")." : $row[0]</h3>
			  </div>";
	//echo "<br>"._QXZ("MODIFY A REMOTE AGENTS ENTRY").": $row[0]<form action=$PHP_SELF method=POST>\n";
		echo "<form action=$PHP_SELF method=POST>\n";
	echo "<input type=hidden name=ADD value=41111>\n";
	echo "<input type=hidden name=remote_agent_id value=\"$row[0]\">\n";
	echo "<center><div class=\"col-md-12\"><div class=\"content\"><table class='table1 col_table' width='750' cellspacing='3'>\n";
	echo "<tr><td class='no-padding'>"._QXZ("User ID Start").": </td><td align=left><div class=col-md-5>$user_start</div></td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("Number of Lines").": </td><td align=left><div class=col-md-5><input  type=text class='form-control' name=number_of_lines size=3 maxlength=3 value=\"$number_of_lines\"></div> ("._QXZ("numbers only").")</td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("Server IP").": </td><td align=left><div class=col-md-5>$row[3]</div></td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("External Extension").": </td><td align=left><div class=col-md-5><input type=text class='form-control' name=conf_exten size=20 maxlength=20 value=\"$conf_exten\"></div> ("._QXZ("number dialed to reach agents")." [i.e. 913125551212])</td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("Status").": </td><td align=left><div class=col-md-5><select class='form-control' size=1 name=status><option SELECTED value='ACTIVE'>"._QXZ("ACTIVE")."</option><option value='INACTIVE'>"._QXZ("INACTIVE")."</option><option SELECTED value='$status'>"._QXZ("$status")."</option></select></div></td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("Campaign").": </td><td align=left><div class=col-md-5>$campaign_id</div></td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("Inbound Groups").": </td><td align=left>\n";
	echo "<div class=col-md-5>$groups_list</div>";
	echo "</td></tr></TABLE></div>\n";
	echo "<div class=col-md-12><input class='btn btn-bdr-sucess' type=submit name=submit value='"._QXZ("submit")."'></div>\n";
	echo "</div></center></br>\n";

	echo _QXZ("NOTE: It can take up to 30 seconds for changes submitted on this screen to go live")."\n";
	}



######################
# ADD=41111 modify remote agents info in the system
######################

if ($ADD==41111)
	{
	//echo "<FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2>";
echo "<div id=\"\" class=\"container-fluid\">
			  <div class=\"\">
			  <div class=\"row\">
			  <div class=\"col-md-12 no-padding\">
			  <div class=\"block-flat\">
			  <div class=\"header\">
			  <h3>"._QXZ("MODIFY A REMOTE AGENTS ENTRY")." : $row[0]</h3>
			  </div>";
	if ( (strlen($number_of_lines) < 1) or (strlen($conf_exten) < 2) )
		{echo "<br>"._QXZ("REMOTE AGENTS NOT MODIFIED - Please go back and look at the data you entered")."\n";}
	else
		{
		$stmt="UPDATE vicidial_remote_agents set number_of_lines='" . mysqli_real_escape_string($link, $number_of_lines) . "', conf_exten='" . mysqli_real_escape_string($link, $conf_exten) . "', status='" . mysqli_real_escape_string($link, $status) . "', closer_campaigns='" . mysqli_real_escape_string($link, $groups_value) . "' where remote_agent_id='" . mysqli_real_escape_string($link, $remote_agent_id) . "';";
		$rslt=mysql_to_mysqli($stmt, $link);

#		echo "$stmt\n";
		echo "<br>REMOTE AGENTS MODIFIED\n";

		### LOG CHANGES TO LOG FILE ###
	#	$fp = fopen ("./admin_changes_log.txt", "a");
	#	fwrite ($fp, "$date|MODIFY REMOTE AGENTS ENTRY     |$PHP_AUTH_USER|$ip|$stmt|\n");
	#	fclose($fp);
		}

	$stmt="SELECT remote_agent_id,user_start,number_of_lines,server_ip,conf_exten,status,campaign_id,closer_campaigns from vicidial_remote_agents where remote_agent_id='" . mysqli_real_escape_string($link, $remote_agent_id) . "';";
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$remote_agent_id =	$row[0];
	$user_start =		$row[1];
	$number_of_lines =	$row[2];
	$server_ip =		$row[3];
	$conf_exten =		$row[4];
	$status =			$row[5];
	$campaign_id =		$row[6];
	echo "<div class=\"col-md-12\"><div class=\"content\">";
	echo "<form action=$PHP_SELF method=POST>\n";
	echo "<input type=hidden name=ADD value=41111>\n";
	echo "<input type=hidden name=remote_agent_id value=\"$row[0]\">\n";
	echo "<center><table class='table1 col_table' width='750' cellspacing='3'>\n";
	echo "<tr><td class='no-padding'>"._QXZ("User ID Start").": </td><td align=left><div class='col-md-5'>$user_start</div></td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("Number of Lines").": </td><td align=left><div class='col-md-5'><input class='form-control' type=text name=number_of_lines size=3 maxlength=3 value=\"$number_of_lines\"></div> ("._QXZ("numbers only").")</td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("Server IP").": </td><td align=left><div class='col-md-5'>$row[3]</div></td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("External Extension").": </td><td align=left><div class='col-md-5'><input class='form-control' type=text name=conf_exten size=20 maxlength=20 value=\"$conf_exten\"></div> ("._QXZ("number dialed to reach agents")." [i.e. 913125551212])</td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("Status").": </td><td align=left><div class='col-md-5'><select class='form-control' size=1 name=status><option SELECTED value='ACTIVE'>ACTIVE</option><option value='INACTIVE'>INACTIVE</option><option SELECTED value='$status'>"._QXZ("$status")."</option></select></div></td></tr>\n";
echo "<tr><td class='no-padding'>"._QXZ("Campaign").": </td><td align=left><div class=col-md-5>$campaign_id</div></td></tr>\n";
	echo "<tr><td class='no-padding'>"._QXZ("Inbound Groups").": </td><td align=left>\n";
	echo "<div class=col-md-5>$groups_list</div>";
	echo "</td></tr></TABLE></div>\n";
	echo "<div class=col-md-12><input class='btn btn-bdr-sucess' type=submit name=submit value='"._QXZ("submit")."'></div>\n";
	echo "</div></center></br>\n";
	echo _QXZ("NOTE: It can take up to 30 seconds for changes submitted on this screen to go live")."\n";
	echo "</div>";
	}



######################
# ADD=61111 status of remote agent in the system and active calls and queue
######################

if ($ADD==61111)
	{
			echo "<div id=\"\" class=\"container-fluid\">
			  <div class=\"\">
			  <div class=\"row\">
			  <div class=\"col-md-12 no-padding\">
			  <div class=\"block-flat\">
			  <div class=\"header\">
			  <h3>"._QXZ("STATUS OF REMOTE AGENTS")." </h3>
			  </div>";
			 echo "<div class=\"col-md-12\">
			  <div class=\"content\">";
	echo "<FONT FACE=\"Courier\" COLOR=BLACK SIZE=2><PRE>";

	if ( (strlen($server_ip) < 2) or (strlen($user) < 2) )
		{echo "<br>REMOTE AGENTS ERROR - Please go back and look at the data you entered\n";}
	else
		{
		$users_list = '';
		$k=0;
		while($k < $number_of_lines)
			{
			$nextuser=($user + $k);
			$users_list .= "'" . mysqli_real_escape_string($link, $nextuser) . "',";
			$k++;
			}
		$users_list = preg_replace("/.$/","",$users_list);

		echo "Remote Agent Time On Calls                                        $NOW_TIME\n\n";
		echo "+------------|--------+--------------+------------+--------+---------------------+---------+\n";
		echo "| STATION    | USER   | LEADID       | CHANNEL    | STATUS | START TIME          | MINUTES |\n";
		echo "+------------|--------+--------------+------------+--------+---------------------+---------+\n";


		$stmt="select extension,user,lead_id,channel,status,last_call_time,UNIX_TIMESTAMP(last_call_time),UNIX_TIMESTAMP(last_call_finish) from vicidial_live_agents where status NOT IN('PAUSED') and server_ip='" . mysqli_real_escape_string($link, $server_ip) . "' and user IN($users_list) order by extension;";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {echo "$stmt\n";}
		$talking_to_print = mysqli_num_rows($rslt);
		if ($talking_to_print > 0)
			{
			$i=0;
			while ($i < $talking_to_print)
				{
				$leadlink=0;
				$row=mysqli_fetch_row($rslt);
				if (preg_match("/READY|PAUSED/i",$row[4]))
					{
					$row[3]='';
					$row[5]=' - WAITING - ';
					$row[6]=$row[7];
					}
				$extension =		sprintf("%-10s", $row[0]);
				$user =				sprintf("%-6s", $row[1]);
				$leadid =			sprintf("%-12s", $row[2]);
				if ($row[2] > 0) 
					{
					$leadidLINK=$row[2];
					$leadlink++;
					if ( preg_match("/QUEUE/i",$row[4]) ) {$row[6]=$STARTtime;}
					$leadid = "<a href=\"./remote_dispo.php?lead_id=$row[2]&call_began=$row[6]\" target=\"_blank\">$leadid</a>";
					}
				$channel =			sprintf("%-10s", $row[3]);
				$cc=0;
				while ( (strlen($channel) > 10) and ($cc < 100) )
					{
					$channel = preg_replace('/.$/i', '',$channel);   
					$cc++;
					if (strlen($channel) <= 10) {$cc=101;}
					}
				$status =			sprintf("%-6s", $row[4]);
				$start_time =		sprintf("%-19s", $row[5]);
				$call_time_S = ($STARTtime - $row[6]);

				$call_time_M = MathZDC($call_time_S, 60);
				$call_time_M = round($call_time_M, 2);
				$call_time_M_int = intval("$call_time_M");
				$call_time_SEC = ($call_time_M - $call_time_M_int);
				$call_time_SEC = ($call_time_SEC * 60);
				$call_time_SEC = round($call_time_SEC, 0);
				if ($call_time_SEC < 10) {$call_time_SEC = "0$call_time_SEC";}
				$call_time_MS = "$call_time_M_int:$call_time_SEC";
				$call_time_MS =		sprintf("%7s", $call_time_MS);
				$G = '';		$EG = '';
				if ($call_time_M_int >= 5) {$G='<SPAN class="yellow"><B>'; $EG='</B></SPAN>';}
				if ($call_time_M_int >= 10) {$G='<SPAN class="orange"><B>'; $EG='</B></SPAN>';}

				echo "| $G$extension$EG | $G$user$EG | $G$leadid$EG | $G$channel$EG | $G$status$EG | $G$start_time$EG | $G$call_time_MS$EG |\n";

				$i++;
				}

			echo "+------------|--------+--------------+------------+--------+---------------------+---------+\n";
			echo "  $i agents logged in on server $server_ip\n\n";

			echo "  <SPAN class=\"yellow\"><B>          </SPAN> - 5 minutes or more on call</B>\n";
			echo "  <SPAN class=\"orange\"><B>          </SPAN> - Over 10 minutes on call</B>\n";
			}
		else
			{
			echo "**************************************************************************************\n";
			echo "********************************* NO AGENTS ON CALLS *********************************\n";
			echo "**************************************************************************************\n";
			}
		}

	echo "</PRE>\n\n";
	}


######################
# ADD=71111 stats for remote agents from closer logs(vicidial_closer_log)
######################

if ($ADD==71111)
	{
		echo "<div id=\"\" class=\"container-fluid\">
			  <div class=\"\">
			  <div class=\"row\">
			  <div class=\"col-md-12 no-padding\">
			  <div class=\"block-flat\">
			  <div class=\"header\">
			  <h3>"._QXZ("INBOUND STATS")." </h3>
			  </div>";
	 echo "<div  class=\"col-md-12\">
			  <div class=\"content\">";
	echo "<FORM ACTION=\"$PHP_SELF\" METHOD=GET>\n";

	echo "<div class='col-md-3'><INPUT class='form-control' TYPE=TEXT NAME=query_date SIZE=10 MAXLENGTH=10 VALUE=\"$query_date\"></div>\n";
	echo "<INPUT TYPE=HIDDEN NAME=ADD VALUE=\"71111\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=user VALUE=\"$user\">\n";
	echo "<INPUT class='btn btn-bdr-sucess' TYPE=SUBMIT NAME=SUBMIT value='"._QXZ("submit")."'>\n";
	echo "</FORM>\n\n";
	echo "<FONT FACE=\"Courier\" COLOR=BLACK SIZE=2><PRE>";

	if ( (strlen($server_ip) < 2) or (strlen($user) < 2) )
		{echo "<br>REMOTE AGENTS ERROR - Please go back and look at the data you entered\n";}
	else
		{
		$users_list = '';
		$k=0;
		while($k < $number_of_lines)
			{
			$nextuser=($user + $k);
			$users_list .= "'$nextuser',";
			$k++;
			}
		$users_list = preg_replace("/.$/","",$users_list);

		$stmt="select count(*),sum(length_in_sec) from vicidial_closer_log where call_date >= '$query_date 00:00:01' and call_date <= '$query_date 23:59:59' and user IN($users_list);";
		$rslt=mysql_to_mysqli($stmt, $link);
		$row=mysqli_fetch_row($rslt);
		$in_calls =		$row[0];
		$in_time =		$row[1];
		$in_time_M = MathZDC($in_time, 60);
		$in_time_M = round($in_time_M, 2);
		$in_time_M_int = intval("$in_time_M");
		$in_time_SEC = ($in_time_M - $in_time_M_int);
		$in_time_SEC = ($in_time_SEC * 60);
		$in_time_SEC = round($in_time_SEC, 0);
		if ($in_time_SEC < 10) {$in_time_SEC = "0$in_time_SEC";}
		$in_time_MS = "$in_time_M_int:$in_time_SEC";
		$in_time_MS =		sprintf("%7s", $in_time_MS);

		echo "Remote Agent inbound stats                                        $NOW_TIME\n\n";
		echo "\n";
		echo "Total calls taken $query_date:    $in_calls\n";
		echo "Total talk time on $query_date:   $in_time_MS (minutes:seconds)\n";
		echo "\n";
		echo "\n";
		echo "Call list for $query_date:\n";
		echo "+----------+------------+----------------+------------+--------+---------------------+---------+\n";
		echo "| USER     | LEADID     | GROUP          | PHONE NUM  | STATUS | CALL TIME           | MINUTES |\n";
		echo "+----------+------------+----------------+------------+--------+---------------------+---------+\n";


		$stmt="select user,lead_id,campaign_id,phone_number,status,call_date,length_in_sec from vicidial_closer_log where call_date >= '$query_date 00:00:01' and call_date <= '$query_date 23:59:59' and user IN($users_list) order by call_date;";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {echo "$stmt\n";}
		$talking_to_print = mysqli_num_rows($rslt);
		if ($talking_to_print > 0)
			{
			$i=0;
			while ($i < $talking_to_print)
				{
				$row=mysqli_fetch_row($rslt);
				$user =				sprintf("%-8s", $row[0]);
				$leadid =			sprintf("%-10s", $row[1]);
				$group =			sprintf("%-14s", $row[2]);
				$phone =			sprintf("%-10s", $row[3]);
				$status =			sprintf("%-6s", $row[4]);
				$start_time =		sprintf("%-19s", $row[5]);
				$call_time_S =		$row[6];

				$call_time_M = MathZDC($call_time_S, 60);
				$call_time_M = round($call_time_M, 2);
				$call_time_M_int = intval("$call_time_M");
				$call_time_SEC = ($call_time_M - $call_time_M_int);
				$call_time_SEC = ($call_time_SEC * 60);
				$call_time_SEC = round($call_time_SEC, 0);
				if ($call_time_SEC < 10) {$call_time_SEC = "0$call_time_SEC";}
				$call_time_MS = "$call_time_M_int:$call_time_SEC";
				$call_time_MS =		sprintf("%7s", $call_time_MS);
				$G = '';		$EG = '';
	#			if ($call_time_M_int >= 5) {$G='<SPAN class="yellow"><B>'; $EG='</B></SPAN>';}
	#			if ($call_time_M_int >= 10) {$G='<SPAN class="orange"><B>'; $EG='</B></SPAN>';}

				echo "| $G$user$EG | $G$leadid$EG | $G$group$EG | $G$phone$EG | $G$status$EG | $G$start_time$EG | $G$call_time_MS$EG |\n";

				$i++;
				}

			echo "+----------+------------+----------------+------------+--------+---------------------+---------+\n";
#			echo "  $i agents logged in on server $server_ip\n\n";

#			echo "  <SPAN class=\"yellow\"><B>          </SPAN> - 5 minutes or more on call</B>\n";
#			echo "  <SPAN class=\"orange\"><B>          </SPAN> - Over 10 minutes on call</B>\n";
			}
		else
			{
			echo "**************************************************************************************\n";
			echo "********************************* NO CALLS ON THIS DAY *******************************\n";
			echo "**************************************************************************************\n";
			}
		}

	echo "</PRE>\n\n";
	}





$ENDtime = date("U");

$RUNtime = ($ENDtime - $STARTtime);

echo "\n\n\n<br><br><br>\n\n";


//echo "<font size=0>\n\n\n<br><br><br>\nscript runtime: $RUNtime seconds</font>";


?>


</TD></TR><TABLE>
</body>
</html>

<?php
	
exit; 



?>

