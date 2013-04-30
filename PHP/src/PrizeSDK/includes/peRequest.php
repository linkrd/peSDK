<?php

require_once dirname(__FILE__) . '/peError.php';

/**
 * Basic commincation - uses cult to get json enoded results
 *
 * @author nicolemccreary
 */
class peRequest {

    private static $_CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'SCAi pe PHP SDK v0.1',
    );
    private static $_use_curl = true;

    public function __construct() {

        if (!function_exists('curl_init')) {
            self::$_use_curl = false;
            //peError::getInstance('missing_curl', peError::REQUEST_ERROR);
        }
        if (!function_exists('json_decode')) {
            peError::getInstance('missing_json', peError::REQUEST_ERROR);
        }
    }

    protected function _send($url, $fields) {
        if (self::$_use_curl == true) {
            return self::_do_curl_request($url, $fields);
        } else {
            return self::_do_post_request($url, $fields);
        }
    }

    private function _do_curl_request($url, $fields) {
        $opts = self::$_CURL_OPTS;
        $fields_string = http_build_query($fields, null, '&');
        $certificate = dirname(__FILE__) . '/linkrd-curl-ca-bundle.crt';
        //we must check if the certificate exists
        if (file_exists($certificate)) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt_array($ch, $opts);

            //execute post
            $result = curl_exec($ch);
            if (curl_errno($ch) == 60) {
                curl_setopt($ch, CURLOPT_CAINFO, $certificate);
                $result = curl_exec($ch);
            }
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($result === false) {
                $e = curl_error($ch);
                peError::getInstance($e, peError::REQUEST_ERROR);
            } else {
                $result = json_decode($result, true);
                if (!is_array($result)) {
                    $e = 'invalid_data';
                    peError::getInstance($e, peError::REQUEST_ERROR);
                }
            }
            curl_close($ch);
        } else {
            $e = 'missing_certificate';
            peError::getInstance($e, peError::REQUEST_ERROR);
        }
        return $result;
    }

    private function _do_post_request($url, $fields) {
        $data = http_build_query($fields);
        $params = array('http' => array(
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                . "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
                ));

        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            $e = $php_errormsg;
            peError::getInstance($e, peError::REQUEST_ERROR);
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            $e = $php_errormsg;
            peError::getInstance($e, peError::REQUEST_ERROR);
        }

        $result = json_decode($response, true);
        if (!is_array($result)) {
            $e = 'invalid_data';
            peError::getInstance($e, peError::REQUEST_ERROR);
        }


        return $result;
    }

}