<?php

require_once dirname(__FILE__) . '/peClient.php';

/**
 * Config
 *
 * Configuation of Server with respect to the API
 *
 * @author nicolemccreary
 */
class peConfig {

    private static $_instance = null;

    /** @type array This is the the contest configuration variable which you have received from your sales rep  */
    private static $params = array();
    private static $configuration = array();

    public function __construct() {

    }

    public function getInstance($params = null) {
        if (self::$_instance == null) {
            self::$_instance = new peConfig();
            self::_init($params);
        }

        return self::$_instance;
    }

    private function _init($params) {
        //If caching of the config is required, it could be expanded upon here
        /*ideally is config is good, we should write it out as a php object saved in the includes directory.*/
        if (isset($params['contest_adminID']) && isset($params['client']) && isset($params['promo']) && isset($params['authkey'])) {
            peClient::getInstance($params);
            self::$configuration = self::_initConfig($params);

        } else {
            peError::getInstance('missing_required_config', peError::CONFIG_ERROR);
        }
    }

    private function _initConfig($params) {

       if ($config = self::_getCache($params)) {
           //print_r ($config);
           return $config;
        }
        $request = peClient::fetchconfig();
        if (is_array($request) && is_array($request['result'])) {

            if ($request['result']['success'] == 1) {
                //only keeps them if they are good
                self::$params = $params;
                $data = $request['result']['config'];

                //write data to a file here
                self::_writeCache($params,$data);

                return $data;
            } elseif (isset($request['result']['errors'][0])) {
                $e = $request['result']['errors'][0];
            } else {
                $e = 'invalid_data';
            }
        } else {
            $e = 'invalid_data';
        }

        if ($e) {
            peError::getInstance($e, peError::CONFIG_ERROR);
        }

        return;
    }
    private function _getCache ($params) {
        $file = self::_getCfgFilename ($params);
        if (file_exists($file)) {
            if (isset($params['ttl_seconds'])) {
                $now = time();
                $expiry_time = filemtime($file) +intval($params['ttl_seconds']);
                if ($now >= $expiry_time) {
                    return null;
                }
            }
            $fp = fopen($file, 'r');
            $data = fread($fp,filesize($file));
            return unserialize($data);
        }
        return null;
    }
    private function _writeCache ($params, $data) {
        $file = self::_getCfgFilename ($params);

        $value = serialize($data);
        $fp = fopen($file, 'w') or peError::getInstance('can_not_write_cfg', peError::CONFIG_ERROR);
        fwrite($fp,$value);
        fclose($fp);


    }
    private function _getCfgFilename ($params) {
        $path= dirname(__FILE__) .'/sitecfgs/';
        $file_name = $path.'cfg_'.$params['client'].'_'.$params['promo'].'_'.md5($params['contest_adminID'].$params['authkey']).'.php';
        return $file_name;
    }

    public function isInstantWin() {
        if (self::$configuration['game_type'] == 'instantwin') {
            return true;
        }
        return false;
    }

    public function isSweeps() {
        if (self::$configuration['game_type'] == 'sweeps') {
            return true;
        }
        return false;
    }

    public function isOpen() {
        if (self::$configuration['is_open'] == 1) {
            return true;
        }
        return false;
    }

    public function getEntryPeriod() {

        $play_period = self::$configuration['user_play_period'];
        $play_period['open_unixtime'] = self::$configuration['open_unixtime'];
        $play_period['close_unixtime'] = self::$configuration['close_unixtime'];
        $play_period['is_open'] = self::$configuration['is_open'];
        $play_period['open_date'] = self::$configuration['open_date'];
        $play_period['close_date'] = self::$configuration['close_date'];
        $play_period['time_zone'] = self::$configuration['time_zone'];

        return $play_period;
    }

    public function getPrizingInfo() {
        if (self::$configuration['game_type'] == 'instantwin') {
            return self::$configuration['prizing'];
        } else {
            return null;
        }
    }

    public function getProfileFields() {
        return self::$configuration['profile_fields'];
    }
    public function getCount() {
        return self::$configuration['count'];
    }

    public function getRequiredFields() {
        $required = array();
        foreach (self::$configuration['profile_fields'] as $field) {
            if ($field['is_required'] == 1 && $field['name'] !='auth') {
                $required[] = $field['name'];
            }
        }
        return $required;
    }

    public function getAllFields() {
        $fieldnames = array();
        foreach (self::$configuration['profile_fields'] as $field) {
            if ($field['name'] !='auth') {
                $fieldnames[] = $field['name'];
            }
        }

        return $fieldnames;
    }
    public function getRequiredFieldNames() {
        $required = array();
        foreach (self::$configuration['profile_fields'] as $field) {
            if ($field['is_required'] == 1 && $field['name'] != 'auth') {
                $required[] = $field['name'];
            }
        }
        return implode(',', $required);
    }

    public function getAllFieldNames() {
        $fieldnames = array();
        foreach (self::$configuration['profile_fields'] as $field) {
            $fieldnames[] = $field['name'];
        }
        return implode(',', $fieldnames);
    }

    public function getAuthUserField() {
        return self::$configuration['user_auth_field'];
    }

    public function hasRequiredFields($params) {
        foreach (self::$configuration['profile_fields'] as $field) {
            if ($field['is_required'] == 1) {

                $name = $field['name'];
                if ((!isset($params[$name]) || empty($params[$name])) && $name != 'auth') {
                    return false;
                }
            }
        }
        return true;
    }
    public function getAll () {
        return self::$configuration;
    }

}

?>
