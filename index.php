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
echo '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="css/bootstrap.css" />
<link rel="stylesheet" href="css/owl.carousel.css" />
<!-- <link rel="stylesheet" href="style.css" content="text/css"> -->
<link rel="stylesheet" href="css/style.css" content="text/css">

<title>Collaboratype | Home</title>
</head>

<body>

  <div class="header"><!-- end .header -->
    <img src="images/header1-02-02.png" class="img-responsive hidden-xs" />
		<nav class="navbar collaboratype-navbar">
		<div id="navbar" class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand visible-xs hidden-md">COLLABORATYPE</a>
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
      </div>
      <div class="collapse navbar-collapse collaboratype-navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav navbar-right">
        <li>
          <a>Home</a>
        </li>
        <li>
          <a>Gallery</a>
        </li>
        <li>
          <a>About us</a>
        </li>';
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
		</div>
		</nav>
	</div>

  </div>
        <div id="slideshow" class="owl-carousel owl-theme">
						<div><img src="images/langkah-01.png" alt="image01" class="img-responsive" /></div>
						<div><img src="images/langkah-02.png" alt="image02" class="img-responsive" /></div>
						<div><img src="images/langkah-03.png" alt="image03" class="img-responsive" /></div>
				</div><!-- sp-content -->

		</div><!-- sp-slideshow -->

	<section class="Awal">
		<div class="container">
		    <div class="about">
				<h1 class="text-default"> What is Collaboratype?</h1>
				<p>Sebuah website yang memungkinkan user berkolaborasi dalam menulis cerita/prosa/puisi. Sehingga dihasilkan sebuah karya dari berbagai sudut pandang kolaborator.</p>
			</div>
			<div class="join">
		    	<h1 class="text-default">Lets Collaboratype!</h1>
		    	<p>Let`s join us, then you know how your words change the world!</p>
			</div>
	    </div>
	</section>

 	<div id="most" class="content Awal">
	  <div class="container">
	  	<h1 class="text-default">Most Read</h1>
	  	<div class="panel-group">
		    <div class="panel panel-default">
          <a href="#collapse0" data-toggle="collapse">
            <div class="panel-heading">Jadi Mahasiswa, Terus Apa?</div>
          </a>
          <div id="collapse0" class="panel-collapse collapse">
            <div class="panel-body">
              <iframe src="https://docs.google.com/document/d/14H01qJY9HlBzWNqF-3kAUm7SAyMyUSxpMmXcoPaLQzU/pub?embedded=true" align="center" width="100%" frameBorder="0" height="600px"></iframe>
            </div>
          </div>
		     </div>
		    <div class="panel panel-default">
          <a href="#collapse1" data-toggle="collapse">
            <div class="panel-heading">Superman Juga Manusia</div>
          </a>
          <div id="collapse1" class="panel-collapse collapse in">
            <div class="panel-body">
              <iframe src="https://docs.google.com/document/d/1YIZgddmDse7_3fTIkI4t8CMV7JI_dfomNA0gow7OXxs/pub?embedded=true" align="center" width="100%" frameBorder="0" height="600px"></iframe>
            </div>
          </div>
		     </div>
		    <div class="panel panel-default">
          <a href="#collapse2" data-toggle="collapse">
            <div class="panel-heading">List Judul</div>
          </a>
          <div id="collapse2" class="panel-collapse collapse">
            <div class="panel-body">
              <iframe src="https://docs.google.com/document/d/1JncSPERiQQ3JxPhnl2HfvXZryIRdLV0DIZCoxOr6MI8/pub?embedded=true" align="center" width="100%" frameBorder="0"></iframe>
            </div>
          </div>
		     </div>
	    </div>
	   </div>
  </div>


  <div class="footer">
  	<div class="container text-center">';
  		echo '<p>&copy; ' . date('Y') .' Developed by Collaboratype Team</p>';
  	echo '</div>
  </div>

  <script type="text/javascript" src="js/jquery.js"></script>
  <script type="text/javascript" src="js/bootstrap.js"></script>
  <script type="text/javascript" src="js/owl.carousel.js"></script>
  <script type="text/javascript" src="js/jquery.stickyNavbar.js"></script>
  <script type="text/javascript" src="js/scripts.js"></script>
  ';


echo '</body></html>';
?>
