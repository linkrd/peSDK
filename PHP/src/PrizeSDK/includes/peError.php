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

    private static $_error_list=array();
    private static $lang= 'en_us';


    /** @type array errors, treated as stack, newsest errors will be at the top of the stack eg.) errors[0] */
    private static $_errors = array();

    public function __construct() {
        self::loadLanguage ('en_US');

    }
    public function loadLanguage ($lang) {
        if (!isset(self::$_error_list[$lang])) {
            $path= dirname(__FILE__) .'/lang/';
            if (file_exists ($path.'nls.'.$lang.'.php')) {
                include_once ($path.'nls.'.$lang.'.php');
                self::$lang=$lang;
                self::$_error_list[$lang] = $settings;
            }
        }
    }
    public static function getInstance($nls = null, $code = 4) {
        if (self::$_instance == null) {
            self::$_instance = new peError();
        }
        self::_setError($nls,$code);
        return self::$_instance;
    }
    private function _setError ($nls, $code) {
        array_unshift(self::$_errors, $nls);

        switch ($code) {
            case self::REQUEST_ERROR:
            case self::CONFIG_ERROR:
                $msg = self::getErrorString($nls);
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
    private function getErrorString($nls, $lang=null) {
        self::$lang= (isset($lang))?$lang:self::$lang;
        self::loadLanguage ($lang);

        if (isset(self::$_error_list[self::$lang][$nls])) {
            $msg = self::$_error_list[self::$lang][$nls];
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

    public function getErrors($lang = null) {
        $errors = array();
        foreach (self::$_errors as $err) {
            $errors[]= self::getErrorString($err,$lang);
        }
        return $errors;
    }

    public function getLastError($lang = null) {
        return self::getErrorString(self::$_errors[0],$lang);
    }

}

?>
