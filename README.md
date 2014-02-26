#About the PrizeSDK#
-----

SCAi PrizeSDK can facilitate adding sweepstakes or instant win functionality to a new or existing web application. By integrating the SDK in to your user system, you can give your users the opportunity to win prizes though SCAi's game engine. SCAi's game engine can manage the business rules, such as plays per day, per user and maximum plays for a promotion.


##Configuration##

Your sales representative with provide you with the following values:

* contest_adminID (your IP or hostname, eg.) $_SERVER['SERVER_NAME'])
* client
* promo
* authkey
* ttl_seconds (optional)
* lang (optional - defaults to en_us)
Set these values in your configuration array, and initialize the class object with the config array. In the case of a configuration error, an exception is thrown.

Change permissions the folder 'includes/sitecfgs' to be globally read + write + executable (chmod 777 includes/sitecfgs). The configuration variables for your promotion will be stored locally in this directory. The files will be named using your client and promotion values, as such: cfg_$params['client']_$params['promo'].php. You may delete the file to get a new configuration for the promotion.

Also, although it is highly unlikely that the configuration for a game will change while your promotion is running, you may set the ttl_seconds parameter to poll for changes in the configuration on your specified interval. Please be aware that the request to fetch the configuration may cause your script to load slower than usual due to the time it takes to get the configuration. It is not recommended that the ttl value be set for less than one hour (3600).

##Demo Configurations##

- sweeps:
```
    $config = array(
        'contest_adminID' => $_SERVER['SERVER_NAME'],
        'client' => 'demosdk',
        'promo' => 'sweeps',
        'authkey' => 'DEMO-SDK1-1234-5678'
    );
```                       
- instant win (one level):
```
    $config = array(
        'contest_adminID' => $_SERVER['SERVER_NAME'],
        'client' => 'demosdk',
        'promo' => 'instant',
        'authkey' => 'DEMO-SDK1-1234-5678'
    );
```                       
- instant win (multi level):
```
    $config = array(
        'contest_adminID' => $_SERVER['SERVER_NAME'],
        'client' => 'demosdk',
        'promo' => 'instantmulti',
        'authkey' => 'DEMO-SDK1-1234-5678'
    );
```                      
- preselect (userPIN is required):
```
    $config = array(
        'contest_adminID' => $_SERVER['SERVER_NAME'],
        'client' => 'demosdk',
        'promo' => 'preselect',
        'authkey' => 'DEMO-SDK1-1234-5678'
    );
```                      
- preselect2 (picks is required):
```
    $config = array(
        'contest_adminID' => $_SERVER['SERVER_NAME'],
        'client' => 'demosdk',
        'promo' => 'preselect2',
        'authkey' => 'DEMO-SDK1-1234-5678'
    );
```
                        
##Examples##


All four examples in the examples folder use one of the three configs above, and generate a fake username which is stored as a session cookie.

Sweeps Entry Game (demosdk-sweeps)
Instant Win Game with One Winning Level (demosdk-instant)
Instant Win Game with Six Winning Levels (demosdk-instantmulti)
Updating a User's Profile and Fetching Game History (demosdk-instantmulti)
Live QA Example
Required User Data

At a minimum, the SDK requires a username. Depending on your own user system, the business rules of the promotion and the rules required by the prizing in the contest, you may be required to provide more than a username. The SDK is meant to integrate with a user on your own system. This value must maintain consistency throughout the promotions. If you do not use a login or screen name or your login name does not comply with our system rules (under 25 chars and alphanumeric: 'a-z' '0-9' '-' and '_') or you allow users to change their screen name, you may use your userID as a username or the database index of the user's record. If your promotion does not require business rules regarding plays per user, you may use microtime or another sufficiently unique value for each play.

Please create your own test users to use with the examples provided. If you are not ready to integrate your user system in the provided examples, a good method is to use your client name and a number joined with an underscore, eg.) scai_1328923489. An example of a username for testing follows and is provided in each included example.
```

                    if (!isset($_COOKIE['username'])) {

                        //Create a fake username using my client name and the microtime
                        $username = 'scaitester_'.(microtime(true)*10000);

                        //set it as a session cookie so I can see what it looks like for returning visitors
                        setcookie('username',$username);

                    } else {

                        //if a cookie already exists, get the fake username from the cookie
                        $username = $_COOKIE['username'];

                    }
```                
If you are using a preselected draw, you will be required to send an additional variable depending upon the type of preselect draw. The variable will be userPIN if all possible values are preloaded, or picks for a number based draw. If you would like to see if a preselected number draw works for you, please try the example configurations, preselect (requires userPIN) and preselect2 (requires picks).

For the demos, one username, 'demo_infinite_plays' is permitted infinite plays. If you wish to test using this username, be aware, it does not behave like a regular user, and will not be restricted by the rules regarding plays per day. You should test with the user scheme you intend to use in your application.

The user data is shared between any promotions at a 'client' level, so any users you create and set the profile of, in demosdk-instant will have the same profile data in demosdk-sweeps. When you are ready for your promotion(s) to be set up, we will give you a new 'client' ID and none of the test users will exist in that configuration.

User fields, types and lengths

[username] [varchar](25) NOT NULL,
[firstname] [varchar](25) NULL,
[lastname] [varchar](25) NULL,
[address] [varchar](50) NULL,
[address2] [varchar](50) NULL,
[city] [varchar](30) NULL,
[state] [varchar](25) NULL,
[country] [varchar](30) NULL,
[zip] [varchar](12) NULL,
[birthdate] [smalldatetime] NULL,
[gender] [varchar](1) NULL,
[title] [varchar](100) NULL,
[company] [varchar](80) NULL,
[day_phone] [varchar](20) NULL,
[evening_phone] [varchar](20) NULL,
[fax_phone] [varchar](20) NULL,
[mobile_email] [varchar](75) NULL,
[alt_email] [varchar](75) NULL,
[answer1] [varchar](50) NULL,
[answer2] [varchar](50) NULL,
[answer3] [varchar](50) NULL,
[answer4] [varchar](50) NULL,
[answer5] [varchar](50) NULL,
[answer6] [varchar](50) NULL,
[answer7] [varchar](50) NULL,
[answer8] [varchar](50) NULL,
[answer9] [varchar](50) NULL,
[answer10] [varchar](50) NULL,


##PrizeSDK Functions##

###Class Constructor###

new PrizeSDK 
Creates a new SDK object.
*usage: $PrizeSDK = new peSDK($config);
*params: an array containing the configuration values
*returns: [object] a PrizeSDK class object
*errors: throws an exception

###Configuration Data###

**isOpen**
Checks whether or not the contest is open.
*usage: $PrizeSDK->isOpen();
*returns: [boolean] true if contest is currently open. false for closed.

**getRequiredFields**
Get the required fields from the configuration values .
*usage: $PrizeSDK->getRequiredFields();
*returns: [array] list of required fields
*errors: No errors, returns empty array

**getAllFields**
Get all possible profile fields from the configuration values .
*usage: $PrizeSDK->getAllFields();
*returns: [array] list of all fields available
*errors: No errors, array would be empty

**getPrizingInfo**
Gets the number of prize levels and the minimum and maximum level.
*usage: $PrizeSDK->getPrizingInfo();
*returns: [array] list including number of prize levels and the min and max.
*errors: Array would be empty, and error would be sent to error stack

**getEntryPeriod**
Gets the entry period data
*usage: $PrizeSDK->getEntryPeriod();
*returns: [array] list including dates the contest opens and closes, along with the play period info.
*errors: Array would be empty, and error would be sent to error stack

###Errors###

newError 
Adds an Error to the Error Stack.
usage: $PrizeSDK->newError($e, $code);
params: $e (error flag as defined in Error Class), $code (one of 5 possible error levels)
hasErrors 
Indicates if any errors are in the error stack.
usage: $PrizeSDK->hasErrors();
returns: [boolean] true if errors are on stack
getLastError 
Gets the last error on the error stack.
usage: $PrizeSDK->getLastError();
params: $lang (optional), will use previously set default config lanugage if not provided.
returns: [string] Most recent error from stack
getAllErrors 
Gets an arry of all errors on the error stack.
usage: $PrizeSDK->getAllErrors;
params: $lang (optional), will use previously set default config lanugage if not provided.
returns: [array] Array of error strong with the most recent error as the first item arr[0]
flushErrors 
Flushes the error stack. All errors are deleted.
usage: $PrizeSDK->flushErrors();
returns: [array] Empty array
User Profile Functions

For any of the following functions which require the user parameters, you may leave the parameters blank if the user information is already in place. Only the first call requiring the user's information requires the user parameters. User parameters are key/value pairs.

isAuthenticated 
Check if a user has been authenticated by the server.
usage: $PrizeSDK->isAuthenticated
returns: [boolean] true if a user is authenticated
setUserParams 
Sets the user paramater array, but does not check the values.
usage: $PrizeSDK->setUserParams($user_parameters);
params: set the user parameter array
authenticateOnServer 
Authenticates the user on the server
usage: $PrizeSDK->authenticateOnServer ($user_parameters);
params: if not already set, a user parameter array containing at minimum, all required fields
returns:[boolean] true if user is sucessfully authenticated
errors: Error would be sent to error stack
getCurrentProfileParams 
Returns the current parameters in the user's profile as key value pairs
usage: $PrizeSDK->getCurrentProfileParams ()
returns: [array] keys and values currently set in the user's profiles
errors: Array would be empty, and error would be sent to error stack
getUserProfile 
Returns the current user profile object with all possible profile fields and their current values
usage: $PrizeSDK->getUserProfile ()
params: if not already set, a user parameter array containing at minimum, all required fields
returns: [array] fields after server update, each field with name, value, and is_required
errors: Array would be empty, and error would be sent to error stack
setUserProfile 
Updates or sets the User Profile on the server.
usage: $PrizeSDK->setUserProfile ($user_parameters)
params: if not already set, a user parameter array containing at minimum, all required fields
returns: [array] fields after server update, each field with name, value, and is_required
errors: Array would be empty, and error would be sent to error stack
Entries and Plays Functions

canEnter 
Checks to see if the user is currently permitted to play / enter (use either canEnter or nextPlay - not both)
usage: $PrizeSDK->canPlay ($user_parameters)
params: if not already set, a user parameter array containing at minimum, all required fields
returns: [boolean] returns true if the user is allowed to play
errors: null value, and error would be sent to error stack
nextPlay 
Checks when the user can play next. (use either canEnter or nextPlay - not both) 
usage: $PrizeSDK->nextPlay ($user_parameters)
params: if not already set, a user parameter array containing at minimum, all required fields
returns: Formatted date of when a user can play next. '0' in the case that a user can play now.
errors: null value, and error would be sent to error stack
enterSweeps 
Enter the sweepstakes
usage: $PrizeSDK->enterSweeps ($user_parameters)
params: if not already set, a user parameter array containing at minimum, all required fields
returns: [array] A game object array containing gameID, result_text and other values (see example data)
errors: Array would be empty and error would be sent to error stack
enterInstantWin 
Enter instant win
usage: $PrizeSDK->enterInstantWin ($user_parameters)
params: if not already set, a user parameter array containing at minimum, all required fields
returns: [array] A game object array containing gameID, result_text and other values (see example data)
errors: Array would be empty and error would be sent to error stack
getUserHistory 
Get previously played games from this user.
usage: $PrizeSDK->getHistory($user_parameters)
params: if not already set, a user parameter array containing at minimum, all required fields
returns: [array] An array of game object arrays containing gameID, result_text and other values
errors: Array would be empty and error would be sent to error stack
Other Classes

includes/peRequest: handles communication to and from server via cURL
includes/peClient: wrapper for request model. Formats data for the request to the server.
includes/peConfig: fetches, caches and evaluates configuration variables for a promotion.
includes/peError: manages error levels and exceptions.
Language Files

Multilingual games can be done using the language files in the following format.

includes/lang: nls.(encoding_language).php
Example Game Data

* indicates instant win only

gameID [integer] Unique ID of one game play
picks [string] String value of picks made in game
rng_conf [hash] hexadecimal hash used to confirm draw values
date_issued_long [date] Date of game play
date_issued_long [date] Date of game play
flash_admin [bool] Game administrator play
win_level* [int] Number indicating level of win
game_is_winner* [bool] Boolean flag indicating a win or loss (0=loss/1=win)
result_text [string] Description of result values
result* [string] Winning level over Maximum winning level, eg.) 3/6
Determining a win on instant win plays

You must have a valid instant win play, where the game_is_winner value is equal to 1, and the win_level and result_text return the expected data relating to prizing.

Example to print out the value of the prize in winning result level 6.

    if ($game = $prizeSDK->enterInstantWin($userdata)) {
         if ($game['game_is_winner'] == 1) {
            if ($game['win_level'] == 6) {
                echo $game['result_text'];
            }
         }
    }
                
Known Issues

If you are using cURL, please ensure the certificate includes/linkrd-curl-ca-bundle.crt is up to date. Instructions for updating the certificate are in includes/how to update ssl cert.txt