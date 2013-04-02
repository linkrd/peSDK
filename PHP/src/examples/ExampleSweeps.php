<?php
require_once ('../PrizeSDK/peSDK.php');

/* Your variables for configuration will be provided by your sales representitive
 *
 *
 * demosdk-sweeps is a sweepstakes which permits one play per user per calendar day (ET)
 *
 *
 */
/* here I am creating a fake username & putting it in a cookie, you would get your username from the database here */
if (!isset($_COOKIE['username'])) {
    $username = 'scaitester_'.(microtime(true)*10000);
    setcookie('username',$username);

} else {
    $username = $_COOKIE['username'];
}



$config = array(
    'contest_adminID' => $_SERVER['SERVER_NAME'],
    'client' => 'demosdk',
    'promo' => 'sweeps',
    'authkey' => 'DEMO-SDK1-1234-5678'
);
/* Initialize your request */
try {
    $prizeSDK = new peSDK($config);
} catch (Exception $e) {
    /*Critical errors will throw an exception which contains the error message.
     *
     */
    $msg = $e->getMessage();
    echo 'Caught exception: ', $msg, "\n";
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<meta name="author" content="SCAinteractive" />
        <title>Sample Sweeps Game</title>

</head>
<body>
    <h1>Sample Sweeps Game</h1>
    <div id="bodycontent">
<?php
/* Fetch your user information from your database or session, the data below is only an example
 *
 * The username is a required vairable.
 * The username must only include characters: a-z 0-9 '_' and '-', between 3 and 25 chars
 *
 * It is best practice to maintain the same case, upper or lower for all username.
 *
 * to list all fields in the contest user: $prizeSDK->getAllFields()
 * to list the required fields: $prizeSDK->getRequiredFields()
 * If your contest doesn't have email features and does not send email to users, use alt_email
 *
 *
 */


$userdata = array('username' => $username,
                  'firstname' => 'Dave');



if ($game = $prizeSDK->enterSweeps($userdata)) {
    /* game is an array which contains: gameID, picks, conf, result_text, date_issued_short, date_issued_long*/

    ?>
        <div class="results"><p>Thank you, <?php echo $userdata['firstname']?>  (<?php echo $username ?>).</p>
        <p>You have successfully earned an entry in the sweeps today. Please come back and play again tomorrow. </p>
        <p class="small">Game ID: <?php echo $game['gameID'] ?><br />Confirmation code:  <?php echo $game['rng_conf'] ?><br />Date issued:  <?php echo $game['date_issued_short'] ?></p>
        </div>
    <?php

} else {
    if ($prizeSDK->hasErrors ()) {
       $errors = $prizeSDK->getLastError();

       ?>
        <div class="error"><?php echo  $errors ?></div>
        <?php
        $prizeSDK->flushErrors();

   }
}


?>
    </div>
</body>
</html>