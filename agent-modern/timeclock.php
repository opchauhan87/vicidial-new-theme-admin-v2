<?php
# timeclock.php - VICIDIAL system user timeclock
# 
# Copyright (C) 2022  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# CHANGELOG
# 80523-0134 - First Build 
# 80524-0225 - Changed event_date to DATETIME, added timestamp field and tcid_link field
# 80525-2351 - Added an audit log that is not to be editable
# 80602-0641 - Fixed status update bug
# 90508-0727 - Changed to PHP long tags
# 100621-1023 - Added admin_web_directory variable
# 130328-0021 - Converted ereg to preg functions
# 130603-2211 - Added login lockout for 15 minutes after 10 failed logins, and other security fixes
# 130705-2010 - Added optional encrypted passwords compatibility
# 130802-1031 - Changed to PHP mysqli functions
# 131208-2155 - Added user log TIMEOUTLOGOUT event status
# 140810-2138 - Changed to use QXZ function for echoing text
# 141118-1239 - Formatting changes for QXZ output
# 141216-2122 - Added language settings lookups and user/pass variable standardization
# 150210-1307 - Fixed QXZ tags and formatting(issue #827)
# 150212-0033 - Added case-sensitive user validation(issue #682)
# 150727-0912 - Added default_language
# 161106-2112 - Added screen colors, fixed formatting
# 201117-2117 - Changes for better compatibility with non-latin data input
# 210616-2101 - Added optional CORS support, see options.php for details
# 220220-0934 - Added allow_web_debug system setting
# 220921-1702 - Added failed login reason messages
#

$version = '2.14-22';
$build = '220921-1702';

$StarTtimE = date("U");
$NOW_TIME = date("Y-m-d H:i:s");
	$last_action_date = $NOW_TIME;

$US='_';
$CL=':';
$AT='@';
$DS='-';
$date = date("r");
$ip = getenv("REMOTE_ADDR");
$browser = getenv("HTTP_USER_AGENT");
$script_name = getenv("SCRIPT_NAME");
$server_name = getenv("SERVER_NAME");
$server_port = getenv("SERVER_PORT");
if (preg_match("/443/i",$server_port)) {$HTTPprotocol = 'https://';}
  else {$HTTPprotocol = 'http://';}
if (($server_port == '80') or ($server_port == '443') ) {$server_port='';}
else {$server_port = "$CL$server_port";}
$agcPAGE = "$HTTPprotocol$server_name$server_port$script_name";
$agcDIR = preg_replace('/timeclock\.php/i','',$agcPAGE);


if (isset($_GET["DB"]))							{$DB=$_GET["DB"];}
        elseif (isset($_POST["DB"]))			{$DB=$_POST["DB"];}
if (isset($_GET["phone_login"]))				{$phone_login=$_GET["phone_login"];}
        elseif (isset($_POST["phone_login"]))	{$phone_login=$_POST["phone_login"];}
if (isset($_GET["phone_pass"]))					{$phone_pass=$_GET["phone_pass"];}
        elseif (isset($_POST["phone_pass"]))	{$phone_pass=$_POST["phone_pass"];}
if (isset($_GET["VD_login"]))					{$VD_login=$_GET["VD_login"];}
        elseif (isset($_POST["VD_login"]))		{$VD_login=$_POST["VD_login"];}
if (isset($_GET["VD_pass"]))					{$VD_pass=$_GET["VD_pass"];}
        elseif (isset($_POST["VD_pass"]))		{$VD_pass=$_POST["VD_pass"];}
if (isset($_GET["VD_campaign"]))				{$VD_campaign=$_GET["VD_campaign"];}
        elseif (isset($_POST["VD_campaign"]))	{$VD_campaign=$_POST["VD_campaign"];}
if (isset($_GET["stage"]))						{$stage=$_GET["stage"];}
        elseif (isset($_POST["stage"]))			{$stage=$_POST["stage"];}
if (isset($_GET["commit"]))						{$commit=$_GET["commit"];}
        elseif (isset($_POST["commit"]))		{$commit=$_POST["commit"];}
if (isset($_GET["referrer"]))					{$referrer=$_GET["referrer"];}
        elseif (isset($_POST["referrer"]))		{$referrer=$_POST["referrer"];}
if (isset($_GET["user"]))						{$user=$_GET["user"];}
        elseif (isset($_POST["user"]))			{$user=$_POST["user"];}
if (isset($_GET["pass"]))						{$pass=$_GET["pass"];}
        elseif (isset($_POST["pass"]))			{$pass=$_POST["pass"];}
if (strlen($VD_login)<1) {$VD_login = $user;}

if (!isset($phone_login)) 
	{
	if (isset($_GET["pl"]))					{$phone_login=$_GET["pl"];}
			elseif (isset($_POST["pl"]))	{$phone_login=$_POST["pl"];}
	}
if (!isset($phone_pass))
	{
	if (isset($_GET["pp"]))					{$phone_pass=$_GET["pp"];}
			elseif (isset($_POST["pp"]))	{$phone_pass=$_POST["pp"];}
	}

### security strip all non-alphanumeric characters out of the variables ###
$DB=preg_replace("/[^0-9a-z]/","",$DB);

$VD_login=preg_replace("/\'|\"|\\\\|;| /","",$VD_login);
$VD_pass=preg_replace("/\'|\"|\\\\|;| /","",$VD_pass);
$user=preg_replace("/\'|\"|\\\\|;| /","",$user);
$pass=preg_replace("/\'|\"|\\\\|;| /","",$pass);

require_once("dbconnect_mysqli.php");
require_once("functions.php");

# if options file exists, use the override values for the above variables
#   see the options-example.php file for more information
if (file_exists('options.php'))
	{
	require('options.php');
	}
#############################################
##### START SYSTEM_SETTINGS AND USER LANGUAGE LOOKUP #####
$stmt = "SELECT use_non_latin,admin_home_url,admin_web_directory,enable_languages,language_method,default_language,agent_screen_colors,agent_script,allow_web_debug FROM system_settings;";
#if ($DB) {echo "$stmt\n";}
$rslt=mysql_to_mysqli($stmt, $link);
	if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'00XXX',$VD_login,$server_ip,$session_name,$one_mysql_log);}
$qm_conf_ct = mysqli_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$non_latin =			$row[0];
	$welcomeURL =			$row[1];
	$admin_web_directory =	$row[2];
	$SSenable_languages =	$row[3];
	$SSlanguage_method =	$row[4];
	$SSdefault_language =	$row[5];
	$agent_screen_colors =	$row[6];
	$SSagent_script =		$row[7];
	$SSallow_web_debug =	$row[8];
	}
if ($SSallow_web_debug < 1) {$DB=0;}

$VUselected_language = '';
$stmt="SELECT user,selected_language from vicidial_users where user='$VD_login';";
if ($DB) {echo "|$stmt|\n";}
$rslt=mysql_to_mysqli($stmt, $link);
	if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'00XXX',$VD_login,$server_ip,$session_name,$one_mysql_log);}
$sl_ct = mysqli_num_rows($rslt);
if ($sl_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$VUuser =				$row[0];
	$VUselected_language =	$row[1];
	}

if (strlen($VUselected_language) < 1)
	{$VUselected_language = $SSdefault_language;}
##### END SETTINGS LOOKUP #####
###########################################
//chirag
//16-03-21
$user=preg_replace("/\'|\"|\\\\|;| /","",$user);
$pass=preg_replace("/\'|\"|\\\\|;| /","",$pass);
$phone_login=preg_replace("/\'|\"|\\\\|;| /","",$phone_login);
$phone_pass=preg_replace("/\'|\"|\\\\|;| /","",$phone_pass);
$stage=preg_replace("/[^0-9a-zA-Z]/","",$stage);
$commit=preg_replace("/[^0-9a-zA-Z]/","",$commit);
$referrer=preg_replace("/[^0-9a-zA-Z]/","",$referrer);
//end

if ($non_latin < 1)
	{
	$user=preg_replace("/[^-_0-9a-zA-Z]/","",$user);
	$pass=preg_replace("/[^-\.\+\/\=_0-9a-zA-Z]/","",$pass);
	$VD_login=preg_replace("/[^-_0-9a-zA-Z]/","",$VD_login);
	$VD_pass=preg_replace("/[^-_0-9a-zA-Z]/","",$VD_pass);
	$VD_campaign=preg_replace("/[^-_0-9a-zA-Z]/","",$VD_campaign);
	$phone_login=preg_replace("/[^\,0-9a-zA-Z]/","",$phone_login);
	$phone_pass=preg_replace("/[^-_0-9a-zA-Z]/","",$phone_pass);
	}
else
	{
	$user=preg_replace("/[^-_0-9\p{L}]/u","",$user);
	$pass = preg_replace('/[^-\.\+\/\=_0-9\p{L}]/u','',$pass);
	$VD_login=preg_replace("/[^-_0-9\p{L}]/u","",$VD_login);
	$VD_pass=preg_replace("/[^-_0-9\p{L}]/u","",$VD_pass);
	$VD_campaign=preg_replace("/[^-_0-9\p{L}]/u","",$VD_campaign);
	$phone_login=preg_replace("/[^\,0-9\p{L}]/u","",$phone_login);
	$phone_pass=preg_replace("/[^-_0-9\p{L}]/u","",$phone_pass);
	}

header ("Content-type: text/html; charset=utf-8");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0


##### BEGIN Define colors and logo #####
$SSmenu_background='015B91';
$SSframe_background='D9E6FE';
$SSstd_row1_background='9BB9FB';
$SSstd_row2_background='B9CBFD';
$SSstd_row3_background='8EBCFD';
$SSstd_row4_background='B6D3FC';
$SSstd_row5_background='A3C3D6';
$SSalt_row1_background='BDFFBD';
$SSalt_row2_background='99FF99';
$SSalt_row3_background='CCFFCC';

if ($agent_screen_colors != 'default')
	{
	$stmt = "SELECT menu_background,frame_background,std_row1_background,std_row2_background,std_row3_background,std_row4_background,std_row5_background,alt_row1_background,alt_row2_background,alt_row3_background,web_logo FROM vicidial_screen_colors where colors_id='$agent_screen_colors';";
	$rslt=mysql_to_mysqli($stmt, $link);
		if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'01XXX',$VD_login,$server_ip,$session_name,$one_mysql_log);}
	if ($DB) {echo "$stmt\n";}
	$qm_conf_ct = mysqli_num_rows($rslt);
	if ($qm_conf_ct > 0)
		{
		$row=mysqli_fetch_row($rslt);
		$SSmenu_background =		$row[0];
		$SSframe_background =		$row[1];
		$SSstd_row1_background =	$row[2];
		$SSstd_row2_background =	$row[3];
		$SSstd_row3_background =	$row[4];
		$SSstd_row4_background =	$row[5];
		$SSstd_row5_background =	$row[6];
		$SSalt_row1_background =	$row[7];
		$SSalt_row2_background =	$row[8];
		$SSalt_row3_background =	$row[9];
		$SSweb_logo =				$row[10];
		}
	}
$Mhead_color =	$SSstd_row5_background;
$Mmain_bgcolor = $SSmenu_background;
$Mhead_color =	$SSstd_row5_background;

$selected_logo = "./images/vicidial_admin_web_logo.png";
$logo_new=0;
$logo_old=0;
if (file_exists('../$admin_web_directory/images/vicidial_admin_web_logo.png')) {$logo_new++;}
if (file_exists('vicidial_admin_web_logo.gif')) {$logo_old++;}
if ($SSweb_logo=='default_new')
	{
	$selected_logo = "./images/vicidial_admin_web_logo.png";
	}
if ( ($SSweb_logo=='default_old') and ($logo_old > 0) )
	{
	$selected_logo = "../$admin_web_directory/vicidial_admin_web_logo.gif";
	}
if ( ($SSweb_logo!='default_new') and ($SSweb_logo!='default_old') )
	{
	if (file_exists("../$admin_web_directory/images/vicidial_admin_web_logo$SSweb_logo")) 
		{
		$selected_logo = "../$admin_web_directory/images/vicidial_admin_web_logo$SSweb_logo";
		}
	}
##### END Define colors and logo #####
echo "<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css\">";
echo "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js\"></script>";
echo "<script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js\"></script>";

//~ echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../agc/css/style.css\" />\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/style_new_theme.css\" />\n";
//~ echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../agc/css/custom.css\" />\n";

if ( ($stage == 'login') or ($stage == 'logout') )
	{
	### see if user/pass exist for this user in vicidial_users table
	$valid_user=0;
	//ishwari
	//30-dec-19
	$auth_message = user_authorization($user,$pass,'',1,0,0,0,'timeclock');
	if ($auth_message == 'GOOD')
		{$valid_user=1;}

	# case-sensitive check for user
	if($valid_user>0)
		{
		if ($user != "$VUuser")
			{
			$valid_user=0;
			print "<!-- case check $user|$VD_login|$VUuser:   |$valid_user| -->\n";
			}
		}

	print "<!-- vicidial_users active count for $user:   |$valid_user| -->\n";

	if ($valid_user < 1)
		{
		### NOT A VALID USER/PASS
		$VDdisplayMESSAGE = _QXZ("The user and password you entered are not active in the system. Please try again :");
		if ($auth_message == 'LOCK')
			{$VDdisplayMESSAGE = _QXZ("Too many login attempts, try again in 15 minutes")."<br />";}
		if ($auth_message == 'ERRNETWORK')
			{$VDdisplayMESSAGE = _QXZ("Too many network errors, please contact your administrator")."<br />";}
		if ($auth_message == 'ERRSERVERS')
			{$VDdisplayMESSAGE = _QXZ("No available servers, please contact your administrator")."<br />";}
		if ($auth_message == 'ERRPHONES')
			{$VDdisplayMESSAGE = _QXZ("No available phones, please contact your administrator")."<br />";}
		if ($auth_message == 'ERRDUPLICATE')
			{$VDdisplayMESSAGE = _QXZ("You are already logged in, please log out of your other session first")."<br />";}
		if ($auth_message == 'ERRAGENTS')
			{$VDdisplayMESSAGE = _QXZ("Too many agents logged in, please contact your administrator")."<br />";}
		if ($auth_message == 'ERRCAMPAGENTS')
			{$VDdisplayMESSAGE = _QXZ("Too many agents logged in to this campaign, please contact your manager")."<br />";}
		if ($auth_message == 'ERRCASE')
			{$VDdisplayMESSAGE = _QXZ("Login incorrect, user names are case sensitive")."<br />";}
		if ($auth_message == 'IPBLOCK')
			{$VDdisplayMESSAGE = _QXZ("Your IP Address is not allowed").": $ip<br />";}
		
		$placeholder1 = _QXZ('Timeclock');
		echo "<title>"._QXZ("Agent Timeclock")."</title>\n";
		echo "<meta name='viewport' content='width=device-width'>";
		echo "<meta charset='utf-8'>";
		echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'>";
		echo "</head>\n";
        echo "<BODY class=\"texture\" BGCOLOR=#E8E8E8 MARGINHEIGHT=0 MARGINWIDTH=0>\n";
		
		//New design start
		echo "<div class=\"Logo container-fluid login\">";
		echo "<div class=\"row content\" style=\"padding: 0;\">";
		
		echo "<div class=\"col-sm-6 login-bg hidden-xs\">"; 
		echo "<div class=\"wrap d-lg-flex d-sm-block\" style=\"\">";
        echo "<div class=\"TextWrap  text-center d-flex align-items-center\" style=\"display: flex;flex-direction: column;justify-content: center;align-items: center;height: 100%;\">";
	    echo "<img src=\"img/login-img.png\" style=\"width: 40%;\">";
	    echo "<p style=\"color: #ffff;font-size: 20px;margin-top: 10px;\">Keeping you <br>always connected</p>";
        echo "</div>";
        echo "</div>";     
        echo "</div>";
        
        echo "<div class=\"col-sm-6 main-wrap\" style=\"height: 100vh;\">";
      	echo "<div class=\"wrap d-lg-flex d-sm-block\" style=\"\">";
		  
		  	echo "<div class=\"main-login\">";
				echo "<div class=\"w-100\">";
					echo "<img src=\"./images/"._QXZ("logo.png")."\" class=\"Logo mb-3\" alt=\"VICIdial\" style=\"width: 150px;\">";
			      		echo "<div style=\"display: flex;justify-content: space-between;align-items: center;\">";
							echo "<h3 class=\"mb-0 text text-blue\">$placeholder1</h3>";
						echo "</div>";
				echo "</div>";
			echo "</div>";
			
	echo "<FORM  class=\"login-form\" NAME=callcenter_form ID=callcenter_form ACTION=\"$agcPAGE\" METHOD=POST>\n";
	echo "<INPUT TYPE=HIDDEN NAME=referrer VALUE=\"$referrer\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=stage VALUE=\"login\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=DB VALUE=\"$DB\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=phone_login VALUE=\"$phone_login\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=phone_pass VALUE=\"$phone_pass\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=VD_login VALUE=\"$VD_login\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=VD_pass VALUE=\"$VD_pass\">\n";
	echo "<p style='font-size:20px;'>$VDdisplayMESSAGE</p><BR>";
		echo "<div class=\"row\">";
		
			echo "<div class=\"col-lg-6 col-sm-4 custom-input\">";
				echo "<div class=\"input-group\">";
					echo "<span class=\"input-group-addon\"><img src=\"img/username.png\"></span>";
						echo "<INPUT placeholder=\"User Login\" class=\"form-control\" TYPE=TEXT NAME=user SIZE=10 MAXLENGTH=20 VALUE=\"$VD_login\">\n";
              	echo "</div>";
        	echo "</div>";
                       
        	echo "<div class=\"col-lg-6 col-sm-4 custom-input\">";
				echo "<div class=\"input-group\">";
                	echo "<span class=\"input-group-addon\"><img src=\"img/password.png\"></span>";
                	echo "<INPUT class=\"form-control\" placeholder=\"User Password\" TYPE=PASSWORD NAME=pass SIZE=10 MAXLENGTH=20 VALUE=''>";
            	echo "</div>";
			echo "</div>";  		
            
		
			echo "<div class=\"col-lg-12 custom-input\">";
				echo "<INPUT class='btn-block dial-btn login-btn' TYPE=SUBMIT NAME=SUBMIT VALUE="._QXZ("SUBMIT")."> \n";
			echo "</div>";
  
		echo "</div>";
	echo "</form>";
	echo "</div>";
	echo "</div>";
		
		echo "</div>\n\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "</body>\n\n";
		echo "</html>\n\n";
		
		exit;
		}
	else
		{
		### VALID USER/PASS, CONTINUE

		### get name and group for this user
		$stmt="SELECT full_name,user_group from vicidial_users where user='$user' and active='Y';";
		if ($DB) {echo "|$stmt|\n";}
		$rslt=mysql_to_mysqli($stmt, $link);
		$row=mysqli_fetch_row($rslt);
		$full_name =	$row[0];
		$user_group =	$row[1];
		print "<!-- vicidial_users name and group for $user:   |$full_name|$user_group| -->\n";

		### get vicidial_timeclock_status record count for this user
		$stmt="SELECT count(*) from vicidial_timeclock_status where user='$user';";
		if ($DB) {echo "|$stmt|\n";}
		$rslt=mysql_to_mysqli($stmt, $link);
		$row=mysqli_fetch_row($rslt);
		$vts_count =	$row[0];

		$last_action_sec=99;

		if ($vts_count > 0)
			{
			### vicidial_timeclock_status record found, grab status and date of last activity
			$stmt="SELECT status,event_epoch from vicidial_timeclock_status where user='$user';";
			if ($DB) {echo "|$stmt|\n";}
			$rslt=mysql_to_mysqli($stmt, $link);
			$row=mysqli_fetch_row($rslt);
			$status =		$row[0];
			$event_epoch =	$row[1];
			$last_action_date = date("Y-m-d H:i:s", $event_epoch);
			$last_action_sec = ($StarTtimE - $event_epoch);
			if ($last_action_sec > 0)
				{
				$totTIME_H = ($last_action_sec / 3600);
				$totTIME_H_int = round($totTIME_H, 2);
				$totTIME_H_int = intval("$totTIME_H");
				$totTIME_M = ($totTIME_H - $totTIME_H_int);
				$totTIME_M = ($totTIME_M * 60);
				$totTIME_M_int = round($totTIME_M, 2);
				$totTIME_M_int = intval("$totTIME_M");
				$totTIME_S = ($totTIME_M - $totTIME_M_int);
				$totTIME_S = ($totTIME_S * 60);
				$totTIME_S = round($totTIME_S, 0);
				if (strlen($totTIME_H_int) < 1) {$totTIME_H_int = "0";}
				if ($totTIME_M_int < 10) {$totTIME_M_int = "0$totTIME_M_int";}
				if ($totTIME_S < 10) {$totTIME_S = "0$totTIME_S";}
				$totTIME_HMS = "$totTIME_H_int:$totTIME_M_int:$totTIME_S";
				}
			else 
				{
				$totTIME_HMS='0:00:00';
				}

			print "<!-- vicidial_timeclock_status previous status for $user:   |$status|$event_epoch|$last_action_sec| -->\n";
			}
		else
			{
			### No vicidial_timeclock_status record found, insert one
			$stmt="INSERT INTO vicidial_timeclock_status set status='START', user='$user', user_group='$user_group', event_epoch='$StarTtimE', ip_address='$ip';";
			if ($DB) {echo "$stmt\n";}
			$rslt=mysql_to_mysqli($stmt, $link);
				$status='START';
				$totTIME_HMS='0:00:00';
			$affected_rows = mysqli_affected_rows($link);
			print "<!-- NEW vicidial_timeclock_status record inserted for $user:   |$affected_rows| -->\n";
			}
		if ( ($last_action_sec < 30) and ($status != 'START') )
			{
			### You cannot log in or out within 30 seconds of your last login/logout
			$VDdisplayMESSAGE = _QXZ("You cannot log in or out within 30 seconds of your last login or logout");

			$placeholder1 = _QXZ('Timeclock');
			echo "<title>"._QXZ("Agent web client: Campaign Login")."</title>\n";
			echo "<meta name='viewport' content='width=device-width'>";
			echo "<meta charset='utf-8'>";
			echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'>";
			echo "</head>\n";
			echo "<BODY class=\"texture\" BGCOLOR=#E8E8E8 MARGINHEIGHT=0 MARGINWIDTH=0>\n";
			
			//New design start
			echo "<div class=\"Logo container-fluid login\">";
			echo "<div class=\"row content\" style=\"padding: 0;\">";
			
			echo "<div class=\"col-sm-6 login-bg hidden-xs\">"; 
			echo "<div class=\"wrap d-lg-flex d-sm-block\" style=\"\">";
			echo "<div class=\"TextWrap  text-center d-flex align-items-center\" style=\"display: flex;flex-direction: column;justify-content: center;align-items: center;height: 100%;\">";
			echo "<img src=\"img/login-img.png\" style=\"width: 40%;\">";
			echo "<p style=\"color: #ffff;font-size: 20px;margin-top: 10px;\">Keeping you <br>always connected</p>";
			echo "</div>";
			echo "</div>";     
			echo "</div>";
			
			echo "<div class=\"col-sm-6 main-wrap\" style=\"height: 100vh;\">";
			echo "<div class=\"wrap d-lg-flex d-sm-block\" style=\"\">";
			  
				echo "<div class=\"main-login\">";
					echo "<div class=\"w-100\">";
						echo "<img src=\"./images/"._QXZ("logo.png")."\" class=\"Logo mb-3\" alt=\"VICIdial\" style=\"width: 150px;\">";
							echo "<div style=\"display: flex;justify-content: space-between;align-items: center;\">";
								echo "<h3 class=\"mb-0 text text-blue\">$placeholder1</h3>";
							echo "</div>";
					echo "</div>";
				echo "</div>";
				
		echo "<FORM  class=\"login-form\" NAME=callcenter_form ID=callcenter_form ACTION=\"$agcPAGE\" METHOD=POST>\n";
		echo "<INPUT TYPE=HIDDEN NAME=stage VALUE=\"login\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=referrer VALUE=\"$referrer\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=DB VALUE=\"$DB\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=phone_login VALUE=\"$phone_login\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=phone_pass VALUE=\"$phone_pass\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=VD_login VALUE=\"$VD_login\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=VD_pass VALUE=\"$VD_pass\">\n";
		echo "<B>$VDdisplayMESSAGE</B><BR>";
			echo "<div class=\"row\">";
			
				echo "<div class=\"col-lg-6 col-sm-4 custom-input\">";
					echo "<div class=\"input-group\">";
						echo "<span class=\"input-group-addon\"><img src=\"img/username.png\"></span>";
							echo "<INPUT placeholder=\"User Login\" class=\"form-control\" TYPE=TEXT NAME=user SIZE=10 MAXLENGTH=20 VALUE=\"$VD_login\">\n";
					echo "</div>";
				echo "</div>";
						   
				echo "<div class=\"col-lg-6 col-sm-4 custom-input\">";
					echo "<div class=\"input-group\">";
						echo "<span class=\"input-group-addon\"><img src=\"img/password.png\"></span>";
						echo "<INPUT class=\"form-control\" placeholder=\"User Password\" TYPE=PASSWORD NAME=pass SIZE=10 MAXLENGTH=20 VALUE=''>";
					echo "</div>";
				echo "</div>";  		
				
			
				echo "<div class=\"col-lg-12 custom-input\">";
					echo "<INPUT class='btn-block dial-btn login-btn' TYPE=SUBMIT NAME=SUBMIT VALUE="._QXZ("SUBMIT")."> \n";
				echo "</div>";
	  
			echo "</div>";
		echo "</form>";
		echo "</div>";
		echo "</div>";
			
			echo "</div>\n\n";
			echo "</div>\n";
			echo "</div>\n";
			echo "</div>\n";
			echo "</body>\n\n";
			echo "</html>\n\n";

			exit;
			}

		if ($commit == 'YES')
			{
			if ( ( ($status=='AUTOLOGOUT') or ($status=='START') or ($status=='LOGOUT') or ($status=='TIMEOUTLOGOUT') ) and ($stage=='login') )
				{
				$VDdisplayMESSAGE = _QXZ("You have now logged-in");
				$LOGtimeMESSAGE = _QXZ("You logged in at")." $NOW_TIME";

				### Add a record to the timeclock log
				$stmt="INSERT INTO vicidial_timeclock_log set event='LOGIN', user='$user', user_group='$user_group', event_epoch='$StarTtimE', ip_address='$ip', event_date='$NOW_TIME';";
				if ($DB) {echo "$stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$affected_rows = mysqli_affected_rows($link);
				$timeclock_id = mysqli_insert_id($link);
				print "<!-- NEW vicidial_timeclock_log record inserted for $user:   |$affected_rows|$timeclock_id| -->\n";

				### Update the user's timeclock status record
				$stmt="UPDATE vicidial_timeclock_status set status='LOGIN', user_group='$user_group', event_epoch='$StarTtimE', ip_address='$ip' where user='$user';";
				if ($DB) {echo "$stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$affected_rows = mysqli_affected_rows($link);
				print "<!-- vicidial_timeclock_status record updated for $user:   |$affected_rows| -->\n";

				### Add a record to the timeclock audit log
				$stmt="INSERT INTO vicidial_timeclock_audit_log set timeclock_id='$timeclock_id', event='LOGIN', user='$user', user_group='$user_group', event_epoch='$StarTtimE', ip_address='$ip', event_date='$NOW_TIME';";
				if ($DB) {echo "$stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$affected_rows = mysqli_affected_rows($link);
				print "<!-- NEW vicidial_timeclock_audit_log record inserted for $user:   |$affected_rows| -->\n";
				}

			if ( ($status=='LOGIN') and ($stage=='logout') )
				{
				$VDdisplayMESSAGE = _QXZ("You have now logged-out");
				$LOGtimeMESSAGE = _QXZ("You logged out at")." $NOW_TIME<BR>"._QXZ("Amount of time you were logged-in:")." $totTIME_HMS";

				### Add a record to the timeclock log
				$stmt="INSERT INTO vicidial_timeclock_log set event='LOGOUT', user='$user', user_group='$user_group', event_epoch='$StarTtimE', ip_address='$ip', login_sec='$last_action_sec', event_date='$NOW_TIME';";
				if ($DB) {echo "$stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$affected_rows = mysqli_affected_rows($link);
				$timeclock_id = mysqli_insert_id($link);
				print "<!-- NEW vicidial_timeclock_log record inserted for $user:   |$affected_rows|$timeclock_id| -->\n";

				### Update last login record in the timeclock log
				$stmt="UPDATE vicidial_timeclock_log set login_sec='$last_action_sec',tcid_link='$timeclock_id' where event='LOGIN' and user='$user' order by timeclock_id desc limit 1;";
				if ($DB) {echo "$stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$affected_rows = mysqli_affected_rows($link);
				print "<!-- vicidial_timeclock_log record updated for $user:   |$affected_rows| -->\n";

				### Update the user's timeclock status record
				$stmt="UPDATE vicidial_timeclock_status set status='LOGOUT', user_group='$user_group', event_epoch='$StarTtimE', ip_address='$ip' where user='$user';";
				if ($DB) {echo "$stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$affected_rows = mysqli_affected_rows($link);
				print "<!-- vicidial_timeclock_status record updated for $user:   |$affected_rows| -->\n";

				### Add a record to the timeclock audit log
				$stmt="INSERT INTO vicidial_timeclock_audit_log set timeclock_id='$timeclock_id', event='LOGOUT', user='$user', user_group='$user_group', event_epoch='$StarTtimE', ip_address='$ip', login_sec='$last_action_sec', event_date='$NOW_TIME';";
				if ($DB) {echo "$stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$affected_rows = mysqli_affected_rows($link);
				print "<!-- NEW vicidial_timeclock_audit_log record inserted for $user:   |$affected_rows| -->\n";

				### Update last login record in the timeclock audit log
				$stmt="UPDATE vicidial_timeclock_audit_log set login_sec='$last_action_sec',tcid_link='$timeclock_id' where event='LOGIN' and user='$user' order by timeclock_id desc limit 1;";
				if ($DB) {echo "$stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$affected_rows = mysqli_affected_rows($link);
				print "<!-- vicidial_timeclock_audit_log record updated for $user:   |$affected_rows| -->\n";
				}

			if ( ( ( ($status=='AUTOLOGOUT') or ($status=='START') or ($status=='LOGOUT') or ($status=='TIMEOUTLOGOUT') ) and ($stage=='logout') ) or ( ($status=='LOGIN') and ($stage=='login') ) )
				{echo _QXZ("ERROR: timeclock log entry already made:")." $status|$stage";  exit;}

			$BACKlink='';
			if ($referrer=='agent') 
				{$BACKlink = "<A class=\"btn btn-blue\" style='border-radius:5px;width: 100%;font-size: 20px;' HREF=\"./$SSagent_script?pl=$phone_login&pp=$phone_pass&VD_login=$user\">"._QXZ("BACK to Agent Login Screen")."</A>";}
			if ($referrer=='admin') 
				{$BACKlink = "<A class=\"btn btn-blue\" style='border-radius:5px;width: 100%;font-size: 20px;' HREF=\"/$admin_web_directory/admin.php\">"._QXZ("BACK to Administration")."</A>";}
			if ( ($referrer=='welcome') or (strlen($BACKlink) < 10) )
				{$BACKlink = "<A class=\"btn btn-blue\" style='border-radius:5px;width: 100%;font-size: 20px;' HREF=\"$welcomeURL\">"._QXZ("BACK to Welcome Screen")."</A>";}
			
			$placeholder1 = _QXZ('Timeclock');
		echo "<title>"._QXZ("Agent Timeclock")."</title>\n";
		echo "<meta name='viewport' content='width=device-width'>";
		echo "<meta charset='utf-8'>";
		echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'>";
		echo "</head>\n";
        echo "<BODY class=\"texture\" BGCOLOR=#E8E8E8 MARGINHEIGHT=0 MARGINWIDTH=0>\n";
		
		//New design start
		echo "<div class=\"Logo container-fluid login\">";
		echo "<div class=\"row content\" style=\"padding: 0;\">";
		
		echo "<div class=\"col-sm-6 login-bg hidden-xs\">"; 
		echo "<div class=\"wrap d-lg-flex d-sm-block\" style=\"\">";
        echo "<div class=\"TextWrap  text-center d-flex align-items-center\" style=\"display: flex;flex-direction: column;justify-content: center;align-items: center;height: 100%;\">";
	    echo "<img src=\"img/login-img.png\" style=\"width: 40%;\">";
	    echo "<p style=\"color: #ffff;font-size: 20px;margin-top: 10px;\">Keeping you <br>always connected</p>";
        echo "</div>";
        echo "</div>";     
        echo "</div>";
        
        echo "<div class=\"col-sm-6 main-wrap\" style=\"height: 100vh;\">";
      	echo "<div class=\"wrap d-lg-flex d-sm-block\" style=\"\">";
		  
		  	echo "<div class=\"main-login\">";
				echo "<div class=\"w-100\">";
					echo "<img src=\"./images/"._QXZ("logo.png")."\" class=\"Logo mb-3\" alt=\"VICIdial\" style=\"width: 150px;\">";
			      		echo "<div style=\"display: flex;justify-content: space-between;align-items: center;\">";
							echo "<h3 class=\"mb-0 text text-blue\">$placeholder1</h3>";
						echo "</div>";
				echo "</div>";
			echo "</div>";
			
	
	echo "<p style='font-size:20px;'>$VDdisplayMESSAGE<BR>";
	echo "$LOGtimeMESSAGE</p><BR>";
		echo "<div class=\"row\">";		
		
			echo "<div class=\"col-lg-12 custom-input\">";
				echo "$BACKlink \n";
			echo "</div>";
  
		echo "</div>";

	echo "</div>";
	echo "</div>";
		
		echo "</div>\n\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "</body>\n\n";
		echo "</html>\n\n";

			exit;
			}




		if ( ($status=='AUTOLOGOUT') or ($status=='START') or ($status=='LOGOUT') or ($status=='TIMEOUTLOGOUT') )
			{
			$VDdisplayMESSAGE = _QXZ("Time since you were last logged-in:")." $totTIME_HMS";
			$log_action = 'login';
			$button_name = _QXZ("LOGIN");;
			$LOGtimeMESSAGE = _QXZ("You last logged-out at:")." $last_action_date<BR><BR>"._QXZ("Click LOGIN below to log-in");
			}
		if ($status=='LOGIN')
			{
			$VDdisplayMESSAGE = _QXZ("Amount of time you have been logged-in:")." $totTIME_HMS";
			$log_action = 'logout';
			$button_name = _QXZ("LOGOUT");
			$LOGtimeMESSAGE = _QXZ("You logged-in at:")." $last_action_date<BR>"._QXZ("Amount of time you have been logged-in:")." $totTIME_HMS<BR><BR>"._QXZ("Click LOGOUT below to log-out");
			}

		//~ echo"<link href='js/bootstrap/dist/css/bootstrap.css' rel='stylesheet'>";
		//~ echo"<link href='css/style.css' rel='stylesheet' />";
		
		
		
		
		
		$placeholder1 = _QXZ('Timeclock');
		echo "<title>"._QXZ("Agent Timeclock")."</title>\n";
		echo "<meta name='viewport' content='width=device-width'>";
		echo "<meta charset='utf-8'>";
		echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'>";
		echo "</head>\n";
        echo "<BODY class=\"texture\" BGCOLOR=#E8E8E8 MARGINHEIGHT=0 MARGINWIDTH=0>\n";
		
		//New design start
		echo "<div class=\"Logo container-fluid login\">";
		echo "<div class=\"row content\" style=\"padding: 0;\">";
		
		echo "<div class=\"col-sm-6 login-bg hidden-xs\">"; 
		echo "<div class=\"wrap d-lg-flex d-sm-block\" style=\"\">";
        echo "<div class=\"TextWrap  text-center d-flex align-items-center\" style=\"display: flex;flex-direction: column;justify-content: center;align-items: center;height: 100%;\">";
	    echo "<img src=\"img/login-img.png\" style=\"width: 40%;\">";
	    echo "<p style=\"color: #ffff;font-size: 20px;margin-top: 10px;\">Keeping you <br>always connected</p>";
        echo "</div>";
        echo "</div>";     
        echo "</div>";
        
        echo "<div class=\"col-sm-6 main-wrap\" style=\"height: 100vh;\">";
      	echo "<div class=\"wrap d-lg-flex d-sm-block\" style=\"\">";
		  
		  	echo "<div class=\"main-login\">";
				echo "<div class=\"w-100\">";
					echo "<img src=\"./images/"._QXZ("logo.png")."\" class=\"Logo mb-3\" alt=\"VICIdial\" style=\"width: 150px;\">";
			      		echo "<div style=\"display: flex;justify-content: space-between;align-items: center;\">";
							echo "<h3 class=\"mb-0 text text-blue\">$placeholder1</h3>";
						echo "</div>";
				echo "</div>";
			echo "</div>";
			
	echo "<FORM  class=\"login-form\" NAME=callcenter_form ID=callcenter_form ACTION=\"$agcPAGE\" METHOD=POST>\n";
	echo "<INPUT TYPE=HIDDEN NAME=stage VALUE=\"$log_action\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=commit VALUE=\"YES\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=referrer VALUE=\"$referrer\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=DB VALUE=\"$DB\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=phone_login VALUE=\"$phone_login\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=phone_pass VALUE=\"$phone_pass\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=VD_login VALUE=\"$VD_login\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=VD_pass VALUE=\"$VD_pass\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=user VALUE=\"$user\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=pass VALUE=\"$pass\">\n";
	echo "<p style='font-size:20px;'>$VDdisplayMESSAGE<BR>";
	echo "$LOGtimeMESSAGE</p><BR>";
		echo "<div class=\"row\">";		
		
			echo "<div class=\"col-lg-12 custom-input\">";
				echo "<INPUT class=\"btn-block dial-btn login-btn\" TYPE=SUBMIT NAME=\"$button_name\" VALUE=\"$button_name\"> \n";
			echo "</div>";
  
		echo "</div>";
	echo "</form>";
	echo "</div>";
	echo "</div>";
		
		echo "</div>\n\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "</body>\n\n";
		echo "</html>\n\n";

		exit;
		}



	}

else
	{
	
	$placeholder1 = _QXZ('Timeclock');
		echo "<title>"._QXZ("Agent web client: Campaign Login")."</title>\n";
		echo "<meta name='viewport' content='width=device-width'>";
		echo "<meta charset='utf-8'>";
		echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'>";
		echo "</head>\n";
        echo "<BODY class=\"texture\" BGCOLOR=#E8E8E8 MARGINHEIGHT=0 MARGINWIDTH=0>\n";
		
		//New design start
		echo "<div class=\"Logo container-fluid login\">";
		echo "<div class=\"row content\" style=\"padding: 0;\">";
		
		echo "<div class=\"col-sm-6 login-bg hidden-xs\">"; 
		echo "<div class=\"wrap d-lg-flex d-sm-block\" style=\"\">";
        echo "<div class=\"TextWrap  text-center d-flex align-items-center\" style=\"display: flex;flex-direction: column;justify-content: center;align-items: center;height: 100%;\">";
	    echo "<img src=\"img/login-img.png\" style=\"width: 40%;\">";
	    echo "<p style=\"color: #ffff;font-size: 20px;margin-top: 10px;\">Keeping you <br>always connected</p>";
        echo "</div>";
        echo "</div>";     
        echo "</div>";
        
        echo "<div class=\"col-sm-6 main-wrap\" style=\"height: 100vh;\">";
      	echo "<div class=\"wrap d-lg-flex d-sm-block\" style=\"\">";
		  
		  	echo "<div class=\"main-login\">";
				echo "<div class=\"w-100\">";
					echo "<img src=\"./images/"._QXZ("logo.png")."\" class=\"Logo mb-3\" alt=\"VICIdial\" style=\"width: 150px;\">";
			      		echo "<div style=\"display: flex;justify-content: space-between;align-items: center;\">";
							echo "<h3 class=\"mb-0 text text-blue\">$placeholder1</h3>";
						echo "</div>";
				echo "</div>";
			echo "</div>";
			
	echo "<FORM  class=\"login-form\" NAME=callcenter_form ID=callcenter_form ACTION=\"$agcPAGE\" METHOD=POST>\n";
	echo "<INPUT TYPE=HIDDEN NAME=stage VALUE=\"login\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=referrer VALUE=\"$referrer\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=DB VALUE=\"$DB\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=phone_login VALUE=\"$phone_login\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=phone_pass VALUE=\"$phone_pass\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=VD_login VALUE=\"$VD_login\">\n";
	echo "<INPUT TYPE=HIDDEN NAME=VD_pass VALUE=\"$VD_pass\">\n";
	echo "<B>$VDdisplayMESSAGE</B><BR>";
		echo "<div class=\"row\">";
		
			echo "<div class=\"col-lg-6 col-sm-4 custom-input\">";
				echo "<div class=\"input-group\">";
					echo "<span class=\"input-group-addon\"><img src=\"img/username.png\"></span>";
						echo "<INPUT class=form-control placeholder=\"User Login\" TYPE=TEXT NAME=user SIZE=10 MAXLENGTH=20 VALUE=\"$VD_login\">\n";
              	echo "</div>";
        	echo "</div>";
                       
        	echo "<div class=\"col-lg-6 col-sm-4 custom-input\">";
				echo "<div class=\"input-group\">";
                	echo "<span class=\"input-group-addon\"><img src=\"img/password.png\"></span>";
                	echo "<INPUT class=form-control placeholder=\"User Password\" TYPE=PASSWORD NAME=pass SIZE=10 MAXLENGTH=20 VALUE=''>";
            	echo "</div>";
			echo "</div>";  		
            
		
			echo "<div class=\"col-lg-12 custom-input\">";
				echo "<INPUT class='btn-block dial-btn login-btn' TYPE=SUBMIT NAME=SUBMIT VALUE="._QXZ("SUBMIT")."> \n";
			echo "</div>";
  
		echo "</div>";
	echo "</form>";
	echo "</div>";
	echo "</div>";
		
		echo "</div>\n\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "</body>\n\n";
		echo "</html>\n\n";
	
	}

exit;

?>
