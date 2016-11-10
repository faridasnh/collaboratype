<?php

########## Google Settings.. Client ID, Client Secret from https://cloud.google.com/console #############
$google_client_id 		= '66344993133-78p9kpdvejvduedarcpch9v7ensk5ibr.apps.googleusercontent.com';
$google_client_secret 	= 'fXOdeTgdo5FCywEY2FVXs1DJ';
$google_redirect_url 	= 'http://localhost/COLLABORATYPE'; //path to your script
$google_developer_key 	= 'AIzaSyALwQepLACz33VS2s5SECUFBHGXADdZI6U';

########## MySql details (Replace with yours) #############
$db_username = "root"; //Database Username
$db_password = ""; //Database Password
$hostname = "localhost"; //Mysql Hostname
$db_name = 'googleAccount'; //Database Name
###################################################################

//include google api files
require_once 'src/Google_Client.php';
require_once 'src/contrib/Google_Oauth2Service.php';

//start session
session_start();

$gClient = new Google_Client();
$gClient->setApplicationName('CollaboraType');
$gClient->setClientId($google_client_id);
$gClient->setClientSecret($google_client_secret);
$gClient->setRedirectUri($google_redirect_url);
$gClient->setDeveloperKey($google_developer_key);

$google_oauthV2 = new Google_Oauth2Service($gClient);

//If user wish to log out, we just unset Session variable
if (isset($_REQUEST['reset'])) 
{
  unset($_SESSION['token']);
  $gClient->revokeToken();
  header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL)); //redirect user back to page
}

//If code is empty, redirect user to google authentication page for code.
//Code is required to aquire Access Token from google
//Once we have access token, assign token to session variable
//and we can redirect user back to page and login.
if (isset($_GET['code'])) 
{ 
	$gClient->authenticate($_GET['code']);
	$_SESSION['token'] = $gClient->getAccessToken();
	header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
	return;
}


if (isset($_SESSION['token'])) 
{ 
	$gClient->setAccessToken($_SESSION['token']);
}


if ($gClient->getAccessToken()) 
{
	  //For logged in user, get details from google using access token
	  $user 				= $google_oauthV2->userinfo->get();
	  $user_id 				= $user['id'];
	  $user_name 			= filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);
	  $email 				= filter_var($user['email'], FILTER_SANITIZE_EMAIL);
	  $profile_url 			= filter_var($user['link'], FILTER_VALIDATE_URL);
	  $profile_image_url 	= filter_var($user['picture'], FILTER_VALIDATE_URL);
	  $personMarkup 		= "$email<div><img src='$profile_image_url?sz=50'></div>";
	  $_SESSION['token'] 	= $gClient->getAccessToken();
}
else 
{
	//For Guest user, get google login url
	$authUrl = $gClient->createAuthUrl();
}

//HTML page start
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css" content="text/css">
<link rel="stylesheet" href="css/style.css" content="text/css">

<title>Collaboratype | Home</title>
</head>

<body>

  <div class="header"><!-- end .header --><img src="images/header1-02-02.png" width="819" height="190" />
		<nav class="navigasi">
		<div class="navbar">
			<ul>
				<li><a href="#">Home</a></li>
				<li><a href="#">Gallery</a></li>
				<li><a href="#">About us</a></li>';
				if(isset($authUrl)) //user is not logged in, show login button
				{
					echo '<li><a href="'.$authUrl.'"><img src="images/google-login-button.png" height="30"/></a></li>';
				} 
				else // user logged in 
				{
				   /* connect to database using mysqli */
					$mysqli = new mysqli($hostname, $db_username, $db_password, $db_name);
					
					if ($mysqli->connect_error) {
						die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
					}
					
					//compare user id in our database
					$user_exist = $mysqli->query("SELECT COUNT(google_id) as usercount FROM google_users WHERE google_id=$user_id")->fetch_object()->usercount; 
					if($user_exist)
					{ 
						//user is new
						echo
						$mysqli->query("INSERT INTO google_users (google_id, google_name, google_email, google_link, google_picture_link) 
						VALUES ($user_id, '$user_name','$email','$profile_url','$profile_image_url')");
					}

					echo '<li/><a href="./dashboard.php">Dashboard</a></li>';
					echo '<li/><a href="'.$profile_url.'" target="_blank"><img src="'.$profile_image_url.'?sz=30" /></a></li>';
					echo '<li/><a class="logout" href="?reset=1">Logout</a></li>';
					
						
				}

				    echo'
			</ul>
		</div>
		</nav>
	</div>
	
  </div>
        <div class="sp-slideshow">
			
				<input id="button-1" type="radio" name="radio-set" class="sp-selector-1" checked="checked" />
				<label for="button-1" class="button-label-1"></label>
				
				<input id="button-2" type="radio" name="radio-set" class="sp-selector-2" />
				<label for="button-2" class="button-label-2"></label>
				
				<input id="button-3" type="radio" name="radio-set" class="sp-selector-3" />
				<label for="button-3" class="button-label-3"></label>
				
				<label for="button-1" class="sp-arrow sp-a1"></label>
				<label for="button-2" class="sp-arrow sp-a2"></label>
				<label for="button-3" class="sp-arrow sp-a3"></label>
				
				<div class="sp-content">
					<div class="sp-parallax-bg"></div>
					<ul class="sp-slider clearfix">
						<li><img src="images/langkah-01.png" alt="image01" /></li>
						<li><img src="images/langkah-02.png" alt="image02" /></li>
						<li><img src="images/langkah-03.png" alt="image03" /></li>
					</ul>
				</div><!-- sp-content -->
				
		</div><!-- sp-slideshow -->

	<section class="Awal">
		<div class="container">
		    <div class="about">
				<h1> What is Collaboratype?</h1>
				<p>Sebuah website yang memungkinkan user berkolaborasi dalam menulis cerita/prosa/puisi. Sehingga dihasilkan sebuah karya dari berbagai sudut pandang kolaborator.</p>
			</div>
			<div class="join">
		    	<h2>Lets Collaboratype!</h2>
		    	<p>Let`s join us, then you know how your words change the world!</p>
			</div>
	    </div>
	</section>

 	<div id="most" class="content Awal">
	  <div class="container">
	  	<h1>Most Read</h1>
	  	<div class="border">
		    <div id="box" class="content">
		      <h1><a href="#">Jadi Mahasiswa, Terus Apa?</a></h1>
		      <iframe src="https://docs.google.com/document/d/14H01qJY9HlBzWNqF-3kAUm7SAyMyUSxpMmXcoPaLQzU/pub?embedded=true" align="center" width="80%" height="600px"></iframe>
		     </div>
		    <div id="box" class="content">
		      <h1><a href="#">Superman Juga Manusia</a></h1>
		      <iframe src="https://docs.google.com/document/d/1YIZgddmDse7_3fTIkI4t8CMV7JI_dfomNA0gow7OXxs/pub?embedded=true" align="center" width="80%" height="600px"></iframe>
		      </div>
		    <div id="box" class="content">
		      <h1><a href="#">List Judul</a></h1>
		      <iframe src="https://docs.google.com/document/d/1JncSPERiQQ3JxPhnl2HfvXZryIRdLV0DIZCoxOr6MI8/pub?embedded=true"></iframe>
		      </div>
	    </div>
	   </div>
  </div>


  <div class="footer">
  	<div class="container">
  		<p>&copy; 2015 Developed by &nbsp; Collaboratype Team</p>
  	</div>
  </div>';

 
echo '</body></html>';
?>

