<?php

########## Google Settings.. Client ID, Client Secret from https://cloud.google.com/console #############
$google_client_id     = '66344993133-78p9kpdvejvduedarcpch9v7ensk5ibr.apps.googleusercontent.com';
$google_client_secret   = 'fXOdeTgdo5FCywEY2FVXs1DJ';
$google_redirect_url  = 'http://localhost/COLLABORATYPE'; //path to your script
$google_developer_key   = 'AIzaSyALwQepLACz33VS2s5SECUFBHGXADdZI6U';

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
    $user         = $google_oauthV2->userinfo->get();
    $user_id        = $user['id'];
    $user_name      = filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);
    $email        = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
    $profile_url      = filter_var($user['link'], FILTER_VALIDATE_URL);
    $profile_image_url  = filter_var($user['picture'], FILTER_VALIDATE_URL);
    $personMarkup     = "$email<div><img src='$profile_image_url?sz=50'></div>";
    $_SESSION['token']  = $gClient->getAccessToken();
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
<title>Collaboratype | Dashboard</title>
</head>

<body>
  <div class="header"><!-- end .header --><img src="images/header1-02-02.png" width="819" height="190" />
  <nav class="navigasi">
  <div class="navbar">
    <ul>
      <li><a href="index.php">Home</a></li>
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

    echo '<li/><a href="../dashboard1.php">Dashboard</a></li>';
    echo '<li/><a href="'.$profile_url.'" target="_blank"><img src="'.$profile_image_url.'?sz=30" /></a></li>';
    echo '<li/><a class="logout" href="?reset=1">Logout</a></li>';
    
      
  }

      echo'
    </ul>
  </div>
  </nav>

<div class="content">
  <div class="container">
    <div class="header">
      <p><a href="#"><img src="images/logo1-01.jpg" width="124" height="58" class="header" align="left" /></a></p>
      <p align="right"> 
        <!-- end .header --><a href="https://www.google.com/docs/about/"><img src="images/button-03.png" alt="Create a new story" width="64" height="64" /></a><img src="images/button-01.png" width="64" height="64" /><img src="images/button-02.png" width="64" height="64" /><img src="images/button-04.png" width="64" height="64" /></p>
    </div>

    <div class="list">
    <h2>My Stories</h2>
      <table>
        <tr>
          <th>Judul</th>
          <th style="text-align:center;">Link</th>
        </tr>
        <tr>
          <td style="text-align:left; width: 70%;">Jadi Mahasiswa, Terus Apa?</td>
          <td style="text-align:center; width: 30%;"><a href="https://docs.google.com/document/d/14H01qJY9HlBzWNqF-3kAUm7SAyMyUSxpMmXcoPaLQzU/edit"><img src="images/button-05.png" alt="Go to full story" width="93" height="42" align="center" /></a></td>
        </tr>
        <tr>
          <td style="text-align:left; width: 70%;">Superman Juga Manusia</td>
          <td style="text-align:center; width: 30%;"><a href="https://docs.google.com/document/d/1YIZgddmDse7_3fTIkI4t8CMV7JI_dfomNA0gow7OXxs/edit"><img src="images/button-05.png" alt="Go to full story" width="93" height="42" align="center" /></a></td>
        </tr>
        <tr>
          <td style="text-align:left; width: 70%;">Harta Karun Langit</td>
          <td style="text-align:center; width: 30%;"><a href="https://docs.google.com/document/d/1lZkpIBaukBM21FWtC0INmh0ge7m1n8IwVpVuicnlIv0/edit"><img src="images/button-05.png" alt="Go to full story" width="93" height="42" align="center" /></a></td>
        </tr>
      </table>
    </div>
  </div>
  <!-- end .content-->

</div>

    <div class="footer">
    <div class="container">
      <p>&copy; 2015 Developed by &nbsp; Collaboratype Team</p>
    </div>
  </div>';

 
echo '</body></html>';
?>
