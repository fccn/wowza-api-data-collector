<?php
/**
 * Singleton class to handle connections to the Wowza API
 *
 * @category Connectors
 * @package  Fccn\Lib;
 * @author   Paulo Costa <paulo.costa@fccn.pt>
 * @version  1.0 - initial version
 */

 namespace Fccn\Lib;

 /**
 * Handles calls to Wowza API
 *
 * @package Fccn\Lib;
 */
class WowzaApi
{

    /**
     * @var WowzaAPI $_instance The singleton instance
     */
    private static $_instance;

    /**
     * @var string $_server the server name
     */
    private $_server;

    /**
     * @var string $_vhost the virtual host name
     */
    private $_vhost;

    /**
     * @var string $_wowza_user
     */
    private $_wowza_user;

    /**
     * @var string $_wowza_pass
     */
    private $_wowza_pass;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_server = SiteConfig::getInstance()->get("wowza_default_server");
        $this->_vhost = SiteConfig::getInstance()->get("wowza_default_vhost");
        $this->_wowza_user = SiteConfig::getInstance()->get("wowza_api_user");
        $this->_wowza_pass = SiteConfig::getInstance()->get("wowza_api_pass");
    }

    /**
     * Get the singleton instance
     *
     * @return WowzaAPI singleton instance
     */
    public static function getInstance()
    {
        if (!WowzaAPI::$_instance instanceof self) {
            WowzaAPI::$_instance = new self();
        }

        return WowzaAPI::$_instance;
    }

    /**
     * Get current monitoring for the machine where the wowza instance is
     * @param $api_base_url the API server URL
     */
     public function getMachineMonitoring($api_base_url){
       $url = $this->_requestURLPreamble($api_base_url)."machine/monitoring/current";
       return $this->_makeRequest($url);
     }

    /**
     * Get current monitoring for selected application
     * @param $api_base_url the API server URL
     */
    public function getApplicationMonitoring($api_base_url, $app = "slive")
    {
        $path = "applications/$app/monitoring/current";
        $url = $this->_buildRequestURL($api_base_url, $path);
        return $this->_makeRequest($url);
    }

    /**
     * List incoming streams for selected application
     *
     */
    public function getIncomingStreams($api_base_url, $app = "slive")
    {
        $in_streams = array();
        $path = "applications/$app/instances";
        $url = $this->_buildRequestURL($api_base_url, $path);
        $response = $this->_makeRequest($url);
        if ($response && !empty($response->instanceList)) {
            foreach ($response->instanceList as $pos => $ls_instance) {
                if (!empty($ls_instance->incomingStreams)) {
                    foreach ($ls_instance->incomingStreams as $stream_pos => $incomingStream) {
                        //to filter scheduled streams with unknown source add: && $incomingStream->sourceIp != "unknown"
                        if ($incomingStream->isConnected) {
                            $in_streams[] = array(
                                "name" => $incomingStream->name,
                                "sourceIP" => $incomingStream->sourceIp,
                                "instance" => $api_base_url #TODO remove http:// and port
                            );
                        }
                    }
                }
            }
        }
        return $in_streams;
    }

     /**
     * Get current statistics for selected stream on selected application
     *
     */
    public function getStreamMonitoring($stream, $api_base_url, $app="slive")
    {
        $path = "applications/$app/instances/_definst_/incomingstreams/$stream/monitoring/current";
        $url = $this->_buildRequestURL($api_base_url, $path);
        return $this->_makeRequest($url);
    }

    //----- helper functions ---

    /**
     * Makes a request to the Wowza API
     *
     * @param string $url the url of the request
     * @param string $request the request type, defaults to GET
     * @param array $attrs list of attributes to add in the form key => value, defaults to empty
     * @param array $headers list of headers to add to the request, defaults to empty
    */
    private function _makeRequest($url, $request='GET', $attrs='', $headers = '')
    {
        if (empty($headers)) {
            $headers = array(
                'Accept:application/json; charset=utf-8',
                'Content-type:application/json; charset=utf-8',
            );
        }
        $json_attrs = '';
        if (!empty($attrs)) {
            $json_attrs = json_encode($attrs);
            array_push($headers, 'Content-Length: '.strlen($json_attrs));
        }
        $ch = curl_init();
        curl_setopt_array(
            $ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            )
        );
        if (!empty($request) && $request != 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
        }

        curl_setopt($ch, CURLOPT_USERPWD, $this->_wowza_user . ":" . $this->_wowza_pass);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);

        if (!empty($attrs)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_attrs);
        }
        if (! $contents = curl_exec($ch)) {
            error_log("cannot curl to ".print_r($url, true));
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return json_decode($contents);
    }

    /**
     * Builds the full URL for a specific request to the Wowza API
     *
     * @param string $api_base_url the base url of the API server
     * @param string $path the path for request
     */
    private function _buildRequestURL($api_base_url,$path)
    {
        return $this->_requestURLPreamble($api_base_url)."servers/$this->_server/vhosts/$this->_vhost/$path";
    }

    /**
    * Preamble for the request URL to the Wowza API
    */
    private function _requestURLPreamble($api_base_url)
    {
      return "$api_base_url/v2/";
    }
}
