<?php

/**
 * Class to perfrom actions on the API
 *
 */
require_once dirname(__FILE__) . '/includes/peConfig.php';

class peSDK {
    /* Create an instance
     *
     * @param array of stuff for configuation into
     * @return instance
     */

    const DATA_ERROR = 2; //data from server is wrong
    const API_ERROR = 3;//data doesn't allow play
    const RESULT_ERROR = 4;//result from server is an error

    private static $_config_vars;
    private static $_userprofile = array();
    private static $_is_authenticated = false;
    private static $_params;

    public function __construct($config_vars) {
        self::$_config_vars = $config_vars;
        peConfig::getInstance($config_vars);
    }
    //Game Configuration section

    /* isOpen will tell you is a contest can be played
     *
     * @return boolean isopen
     */

    public function isOpen() {
        return peConfig::isOpen();
    }

    /* getRequiredFields will get an array of the required fields
     *
     * @return array Array of required fields
     */

    public function getRequiredFields() {
        $fields = peConfig::getRequiredFields();

        return $fields;
    }

    /* getAllFields will get an array of the fields
     *
     * @return array Array of fields
     */
    public function getAllFields() {
        $fields = peConfig::getAllFields();

        return $fields;
    }
    /* getEntryPeriod gets data relating to when the contest opens and closes
     *
     * @return array containing open & close dates
     */

    public function getEntryPeriod() {
        return peConfig::getEntryPeriod();
    }


    /* getPrizingInfo gets data relating winning levels
     *
     * @return array containing winlevel information
     */

    public function getPrizingInfo() {
         if (peConfig::isInstantWin()) {
            return peConfig::getPrizingInfo();
         } else {
             self::newError('instant_wrong_game_type', self::DATA_ERROR);
             return array ();
         }
    }

    //Error section
     //Functions to pass the recoverable errors to the main script

    public function newError ($e, $code) {
        peError::getInstance($e,$code);
    }
    public function hasErrors() {
        return (peError::getErrorCount()) ? true : false;
    }

    public function getLastError() {
        return peError::getLastError();
    }

    public function getAllErrors() {
        return peError::getErrors();
    }

    public function flushErrors() {
        return peError::flushErrors();
    }

    //Main Section

    /* isAuthenticated will tell you if your current user is authenticated
     *
     * @return boolean isopen
     */


    public function isAuthenticated() {
        return self::$_is_authenticated;
    }


    /* Set userparams
     *
     * @param $param array containing user parameters, optional is already set
     * @return bool is authentication is successful
     */

    public function setUserParams($params) {
        self::$_params = $params;
    }







    /* Performs authentication on the server
     *
     * @param $param array containing user parameters, optional is already set
     * @return bool is authentication is successful
     */

    public function authenticateOnServer($params = null) {
        if ($params != null) {
            self::$_params = $params;
        }


        self::_buildAndSend('auth_only', self::$_params);
        if (self::$_is_authenticated == 1) {

            return true;
        } else {
            return false;
        }
    }
    /* getCurrentProfileParams will get the current user's profile values as name/value pairs containing a value
     *
     * @return array Array of name value pairs containing the non null user profile fields
     */

    public function getCurrentProfileParams() {
        $params = array();
        foreach (self::$_userprofile as $field) {
            if ($name != 'auth') {
                $name = $field['name'];
                $value = $field['value'];
                if ($value) {
                    $params[$name] = $value;
                }
            }
        }
        return $params;
    }
     /* getUserProfile will get the array of current user's profile fields, each row contains, name, value, and is_required
     *
     * @return array Array of profile fields
     */

    public function getUserProfile() {
        return self::$_userprofile;
    }

    /* Sets user profile
     *
     * @param $profile_fields profile fields to update
     * @param $param array containing user parameters, optional is already set
     * @return object user profle
     */

    public function setUserProfile($profile_fields, $params = null) {
        if ($params != null) {
            self::$_params = $params;
        }
        $update_params = self::$_params;

        foreach ($profile_fields as $key => $value) {
            $update_params[$key] = $value;
        }
        if (self::isOpen()) {
            $rv = self::_buildAndSend('updateprofile', $update_params);
            $updates = array ();
            foreach ($rv['fields'] as $row) {
                if ($row['nls_string'] == 'updated_successfully') {
                    $updates[] = $row['field'];
                }
            }


            $profile = self::getUserProfile();
             foreach ($profile as &$field) {
                 $name = $field['name'];
                  $field['updated'] = false;
                 if (in_array ($name, $updates)) {
                     $field['updated'] = true;
                 }


             }

            //Should go through & determine which were merged
            return $profile ; //
        } else {
            self::newError('contest_not_open', self::API_ERROR);
        }
    }


    /* Checks if user can play in this period
     *
     * @param $param array containing user parameters, optional is already set
     * @return bool is authentication is successful
     */

    public function canEnter($params = null) {
        if ($params != null) {
            self::$_params = $params;
        }
        $rv = self::_buildAndSend('canplay', self::$_params);
        if ($rv['canplay'] == 1) {

            return true;
        } else {

            return false;
        }
    }

    /* Checks if user can play in this period
     *
     * @param $param array containing user parameters, optional is already set
     * @return bool is authentication is successful
     */

    public function nextPlay($params = null) {
        if ($params != null) {
            self::$_params = $params;
        }
        $rv = self::_buildAndSend('canplay', self::$_params);
        if ($rv['canplay'] == 1) {
            return 0;
        } else {
            return $rv['next_play'];
        }
    }

    /* create a sweeps entry for the current user
     *
     * @param $param array containing user parameters, optional is already set
     * @return array game data
     */

    public function enterSweeps($params = null) {
        if ($params != null) {
            self::$_params = $params;
        }
        if (self::isOpen()) {
            if (peConfig::isSweeps()) {
                $rv = self::_buildAndSend('instantwin', self::$_params);
                if (isset ($rv['game']['gameID'])) {
                    return self::_gameData($rv['game'], 'sweeps');
                }
                return null;
            } else {
                self::newError('sweeps_wrong_game_type', self::DATA_ERROR);
            }
        } else {
            self::newError('contest_not_open', self::API_ERROR);
        }
    }

    /* create an instant win entry for the current user
     *
     * @param $param array containing user parameters, optional is already set
     * @return array game data
     */

    public function enterInstantWin($params = null) {
        if ($params != null) {
            self::$_params = $params;
        }
        if (self::isOpen()) {
            if (peConfig::isInstantWin()) {
                $rv = self::_buildAndSend('instantwin', self::$_params);
                if (isset ($rv['game']['gameID'])) {
                    return self::_gameData($rv['game'], 'instant');
                } else {
                    return null;
                }
            } else {
                self::newError('instant_wrong_game_type', self::DATA_ERROR);
            }
        } else {
            self::newError('contest_not_open', self::API_ERROR);
        }
    }


    /* About this function
     *
     * @param $param
     * @return object
     */

    public function getUserHistory($params = null) {
        if ($params != null) {
            self::$_params = $params;
        }
        $rv = self::_buildAndSend('gamehistory', self::$_params);
        return self::_gameHistoryData($rv['history']);
    }

    //Private functions

    private function _setIsAuthenticated($flag) {
        self::$_is_authenticated = $flag;
    }



    /* About this function
     *
     * @param $param
     * @return object
     */

    private function _setProfileData($profile_data) {
        $profile = peConfig::getProfileFields();

        $user_profile = array ();

        foreach ($profile as $field) {
            $name = $field['name'];
            if ($name != 'auth') {
                $field['value'] = (isset($profile_data[$name])) ? $profile_data[$name] : null;
                $user_profile[]= $field;
            }
        }

        self::$_userprofile = $user_profile;
    }

    /* About this function
     *
     * @param $param
     * @return object
     */

    private function _buildAndSend($action, $params) {
        if (peConfig::hasRequiredFields($params) == true) {
            $userkey = peConfig::getAuthUserField();
            $uservalue = $params[$userkey];

            $rv = peClient::doaction($action, $userkey, $uservalue, $params);

            //print_r ($rv);
            self::_setIsAuthenticated($rv['auth']['success']);

            if ($rv['auth']['success'] == 1) {
                self::_setProfileData($rv['user_profile']);
            }

            if ($rv['result']['success'] == 1) {
                return $rv['result'];
            } else {

                if (is_array($rv['result']) && $rv['result']['success'] == 0) {
                    foreach ($rv['result']['errors'] as $e) {
                        self::newError($e, self::RESULT_ERROR);
                    }
                    return $rv['result'];
                } else if (is_array($rv['auth']) && $rv['auth']['success'] == 0) {
                    foreach ($rv['auth']['errors'] as $e) {
                        self::newError($e, self::RESULT_ERROR);
                    }
                    return $rv['auth'];
                } else {
                    //this is unexpected data of some sort...
                    $e = $action . '_failed';
                    self::newError($e, self::API_ERROR);
                }
            }
        } else {
            self::newError('missing_required_fields', self::API_ERROR);
        }
    }
    private function _gameHistoryData ($server_array) {
        $game_type = (peConfig::isSweeps())?'sweeps':'instant';
        foreach ($server_array as &$server_data) {
            $server_data['date_issued_long'] = $server_data['long_date'];
            $server_data['date_issued_short'] = $server_data['short_date'];

            $server_data = self::_gameData($server_data, $game_type);
        }
        return $server_array;



    }


    /* Clean up the game data and remove extraneous variavle that the server sends back
     *
     * @param $server_data
     * @param $game_type
     * @return object contains gameID, picks, conf, result_text, date_issued_short, date_issued_long,
     * if type is instant win, also win_level, game_is_winner, and result
     */

    private function _gameData($server_data, $game_type) {


        unset ($server_data['long_date']);
        unset ($server_data['short_date']);
        unset($server_data['comdata']);
        unset($server_data['rng_draw']);
        unset($server_data['max_win_level']);
        unset($server_data['maxlevel']);
        unset($server_data['min_win_level']);
        unset($server_data['minlevel']);
        unset($server_data['num_choose']);
        unset($server_data['wingame']);
        unset($server_data['date_issued']);
        unset($server_data['pin']);
        unset($server_data['pin1']);
        unset($server_data['score']);
        unset($server_data['numpicks']);
        unset($server_data['winlevel']);

        if ($game_type == 'sweeps') {
            unset($server_data['win_level']);
            unset($server_data['game_is_winner']);
            if (isset ($server_data['result'])) {
                $server_data['result_text'] = 'entered';
            unset($server_data['result']);

            }
        }
        if ($game_type == 'instant') {
            if ($server_data['game_is_winner'] == 0) {
                if (trim($server_data['result_text']) == "") {
                    $server_data['result_text']='loss';
                }
            }

        }

        return $server_data;
    }




}

?>
