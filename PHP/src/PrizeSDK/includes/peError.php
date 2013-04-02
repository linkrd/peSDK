<?php

/**
 * Description of peErrors
 *
 * @author nicolemccreary
 */
class peError {

    private static $_instance = null;
    //error levels

    const REQUEST_ERROR = 0;//can't communicate with server
    const CONFIG_ERROR = 1;//can't get the config
    const DATA_ERROR = 2; //data from server is wrong
    const API_ERROR = 3;//data doesn't allow play
    const RESULT_ERROR = 4;//result from server is an error

    private static $_error_list = array(
        'missing_curl' => 'The CURL PHP extension is required.',
        'missing_json' => 'The JSON PHP extension is required.',
        'missing_certificate' => 'Missing Certificate, please ensure the linkrd crt file is in the directory.',
        'invalid_data_returned' => 'Invalid data from server, please check your configuation variables.',
        'invalid_data' => 'Invalid data from server, please check your configuation variables.',
        'restricted_contest' => 'Could not log in to contest, please check your configuration variables.',
        'missing_required_config' => 'Missing a required configuration field, you must set contest_adminID, client, promo, and authkey.',
        'missing_required_request_fields' => 'Missing required variables',
        'missing_required_user_fields' => 'Missing required user variables',
        'contest_not_open' => 'Contest is not open, please check the open & close dates',
        'sweeps_wrong_game_type' => 'Game is not of the type sweeps',
        'instant_wrong_game_type' => 'Game is not of the type instantwin',
        'too_many_plays_in_period' => 'You\'ve already played in this period',
        'canplay_failed' => 'The action canplay did not perform as expected',
        'instantwin_failed' => 'The action canplay did not perform as expected',
        'error_missing_parameters' => 'Missing required fields',
        'key_does_not_match' => 'MD5 hash does not validate',
        'invalid_login' => 'username and email do not match',
        'unknown_action' => 'action not valid',
        'username_too_small' => 'minimum size is 3 chars',
        'username_too_big' => 'max size is 25 chars',
        'username_bad_chars' => "Usernames must only contain alphanumeric characters, '@', '.', '_', or '-'.",
        'error_invalid_username' => "Usernames must only contain alphanumeric characters, '@', '.', '_', or '-'.",
        'email_bad_chars' => "Email address must only contain alphanumeric characters, '.', '_', or '-', '+', '=' or '!'.",
        'error_username_exists' => 'username is already in the system',
        'email_already_registered_in_site' => 'Email already registered in site',
        'error_get_scores' => 'Server is incorrectly configured for the winning levels',
        'can_not_write_cfg' => 'Can not write a configuration cache, please check the permission on includes/sitecfgs/',

    );

    /** @type array errors, treated as stack, newsest errors will be at the top of the stack eg.) errors[0] */
    private static $_errors = array();

    public function __construct() {

    }
    public static function getInstance($nls = null, $code = 4) {
        if (self::$_instance == null) {
            self::$_instance = new peError();
        }
        self::_setError($nls,$code);
        return self::$_instance;
    }
    private function _setError ($nls, $code) {
        $msg = self::getErrorString($nls);

        array_unshift(self::$_errors, $msg);

        switch ($code) {
            case self::REQUEST_ERROR:
            case self::CONFIG_ERROR:
                throw new Exception($msg);
                break;

            case self::DATA_ERROR:
            case self::API_ERROR:
            case self::RESULT_ERROR:
            default:
                self::logError();
                break;
        }

    }
    protected function logError() {
        //stub for logging
    }

    /**
     * getErrorString
     *
     * Function to get the user friendly error string
     *
     * @param string $nls Flag from server or from other class indicating the error
     * @return string Natural language string containing error message
     */
    private function getErrorString($nls) {

        if (isset(self::$_error_list[$nls])) {
            $msg = self::$_error_list[$nls];
        } else {
            $msg = $nls;
        }
        return $msg;
    }

    public function flushErrors() {
        self::$_errors = array();
        return self::$_errors;
    }

    public function getErrorCount() {
        return count(self::$_errors);
    }

    public function getErrors() {
        return self::$_errors;
    }

    public function getLastError() {
        return self::$_errors[0];
    }

}

?>
