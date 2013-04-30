<?php

require_once dirname(__FILE__) . '/peRequest.php';

/**
 * Description of peClient does the work involving taking to the server
 *
 * @author nicolemccreary
 */
class peClient extends peRequest {
    const API_ERROR = 3;//data doesn't allow play

    private static $_instance = null;


    private static $_SERVICE_HOST = 'contest.linkrd.com';
    private static $_IS_SSL = true;
    private static $_SERVICE = 'rngapi';
    private static $_RESPONSE_TYPE = 'json';

    private static $url;
    private static $contest_adminID;
    private static $authkey;

    public function __construct() {
        parent::__construct();
    }

    public function getInstance($params = null) {
        if (self::$_instance == null) {
            self::$_instance = new peClient();
            self::_init($params);
        }

        return self::$_instance;
    }

    private function _init($params) {
        $proto = (self::$_IS_SSL) ? 'https' : 'http';
        self::$url = $proto . '://' . self::$_SERVICE_HOST . '/' . $params['client'] . '/' . $params['promo'] . '/' . self::$_SERVICE;
        self::$contest_adminID = $params['contest_adminID'];
        self::$authkey = $params['authkey'];
    }

    public function doaction($action, $userkey, $uservalue, $otherparams) {

        $params = self::_buildparams($action, $userkey, $uservalue, $otherparams);

        $rv = parent::_send(self::$url, $params);
        return $rv;
    }

    public function fetchconfig() {
        $rv = self::doaction('getconfig', 'contest_adminID', self::$contest_adminID, null);
        return $rv;
    }

    private function _buildparams($action, $userkey, $uservalue, $extraarray = null) {

        $params = array('action' => $action,
            'auth' => self::_buildauth($uservalue),
            $userkey => $uservalue,
            'response_format' => self::$_RESPONSE_TYPE
        );
        if (is_array($extraarray)) {
            unset($extraarray[$userkey]);
            foreach ($extraarray as $key => $value) {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    private function _buildauth($key) {
        return md5($key . self::$authkey);
    }

}

?>
