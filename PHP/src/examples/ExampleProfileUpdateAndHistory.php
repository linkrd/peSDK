<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>prizeSDK Documentation :: Sample Profile Update + Game History</title>
        <link rel="stylesheet" type="text/css" href="../pform/view.css" media="all"></link>
        <script type="text/javascript" src="../pform/view.js"></script>
    </head>
    <body id="main_body" >
        <img id="top" src="../pform/top.png" alt="" />
        <div id="content_container">
            <?php
            require_once ('../PrizeSDK/peSDK.php');


            /* Your variables for configuration will be provided by your sales representitive
             *
             *
             * demosdk-sweeps is an instant win with six winning level which permits one play per user per calendar day (ET)
             *
             * Winning levels are ordered from highest to lowest
             * Grand Prize: win_level = 6
             * Second Prize: win_level = 5
             * Third Prize: win_level = 4
             *
             */
            /* here I am creating a fake username & putting it in a cookie, you would get your username from the database here */
           function getFakeUser() {
                if (!isset($_COOKIE['username'])) {
                    $username = 'scaitester_' . (microtime(true) * 10000);
                    setcookie('username', $username);
                } else {
                    $username = $_COOKIE['username'];
                }
                return $username;
            }

            $username = getFakeUser();

            $config = array(
                'contest_adminID' => $_SERVER['SERVER_NAME'],
                'client' => 'demosdk',
                'promo' => 'instantmulti',
                'authkey' => 'DEMO-SDK1-1234-5678'
            );
            /* Initialize your request */
            try {
                $prizeSDK = new peSDK($config);
            } catch (Exception $e) {
                /* Critical errors will throw an exception which contains the error message.
                 *
                 */
                $msg = $e->getMessage();
                echo 'Caught exception: ', $msg, "\n";
            }
            ?>


            <h1>prizeSDK</h1>
            <div id="bodycontent">
                <h2>Sample Profile Update + Game History</h2>
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


$userdata = array('username' => $username);


if ($prizeSDK->authenticateOnServer($userdata)) {
    /* game is an array which contains: gameID, picks, conf, result_text, date_issued_short, date_issued_long, win_level, game_is_winner, and result */
    $profile_data = array('firstname' => 'Dave', 'lastname' => 'Murphy',
        'city' => 'San Bernardino', 'state' => 'CA', 'zip' => '92404');
    $my_profile = $prizeSDK->setUserProfile($profile_data);
    ?>
                    <h2>My Profile</h2>
                    <ul>
                    <?php
                    foreach ($my_profile as $field) {
                        if ($field['value'] != null) {
                            ?>
                                <li><?php echo $field['name'] ?> <?php echo ($field['is_required'] == 1) ? '*' : ''; ?>: <?php echo $field['value'] ?></li>
                                <?php
                            }
                        }
                        ?>
                    </ul>

                    <h2>Game Play History</h2>
                    <table>
                        <tr><th>Date</th><th>Game ID</th><th>Result</th><th>Confirmation Code</th></tr>
    <?php
    $history = $prizeSDK->getUserHistory();
    foreach ($history as $row) {
        ?>
                            <tr><td><?php echo $row['date_issued_short'] ?></td><td><?php echo $row['gameID'] ?></td><td><?php echo $row['result_text'] ?></td><td><?php echo $row['rng_conf'] ?></td></tr>
                            <?php
                        }
                        ?>
                    </table>
                        <?php
                    } else {
                        if ($prizeSDK->hasErrors()) {
                            $errors = $prizeSDK->getLastError();
                            ?>
                        <div class="error"><?php echo $errors ?></div>
                        <?php
                        $prizeSDK->flushErrors();
                    }
                }
                ?>
            </div>
        </div>
        <img id="bottom" src="../pform/bottom.png" alt="" />
    </body>
</html>