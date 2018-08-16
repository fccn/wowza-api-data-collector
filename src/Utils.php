<?php
/**
 * Collection of utilities for this project
 *
 * @category Utilities
 * @package  Fccn\Lib;
 * @author   Paulo Costa <paulo.costa@fccn.pt>
 * @version  1.0 - initial version
 */

 namespace Fccn\Lib;


 /**
 * Holds a collection of static functions that are usefull for the project
 *
 * @package Fccn\Lib;
 */
 class Utils
 {

   /**
   * Checks if the statistics file can be updated
   */
   public static function can_update($file_path){
     if (file_exists($file_path)) {
         #check when last modified
         $time_limit = filemtime($file_path)+SiteConfig::getInstance()->get('file_buffer_timeout');
         clearstatcache();
         if (time() > $time_limit) {
             return true;
         }
     } else {
         return true;
     }
     return false;
   }

   /**
   * Updates the statistical data buffer file
   * @param string $file_path the path to the buffer file, defaults to empty
   */
   public static function update_stats_file($file_path)
   {
       FileLogger::getInstance()->debug("Updating stats file");
       $stats_data = array();
       $api_srv = SiteConfig::getInstance()->get('wowza_api_url');
       //get machine monitoring data for total num of connections and heap info
       $mmon_data = WowzaApi::getInstance()->getMachineMonitoring($api_srv);
       $stats_data["cpu"] = array(
       "user" => $mmon_data->cpuUser,
       "system" => $mmon_data->cpuSystem
     );
       $stats_data["memory"] = array(
       "heapfree" => $mmon_data->heapFree,
       "heapused" => $mmon_data->heapUsed
     );
       $stats_data["connections"] = array(
       "total" => $mmon_data->connectionCount
     );
       //get application monitoring data
       $applications = SiteConfig::getInstance()->get("wowza_apps");
       if (!is_array($applications)) {
           $applications = array($applications);
       }
       foreach ($applications as $key => $app) {
           #$app = "slive";
           $app_data = WowzaApi::getInstance()->getApplicationMonitoring($api_srv, $app);
           $stats_data["connections"][$app] = array(
         "bytesratein" => $app_data->bytesInRate,
         "bytesrateout" => $app_data->bytesOutRate,
         "total" => $app_data->totalConnections,
         "webm" => $app_data->connectionCount->WEBM,
         "rtmp" => $app_data->connectionCount->RTMP,
         "dash" => $app_data->connectionCount->MPEGDASH,
         "hls" => $app_data->connectionCount->CUPERTINO,
         "smooth" => $app_data->connectionCount->SMOOTH,
         "rtp" => $app_data->connectionCount->RTP
       );
       }

       if(empty($file_path)){
         //fallback to default file in tmp/wowza_stats.json
         FileLogger::getInstance()->warn("update_stats_file: no file path was provided, generating default tmp from settings");
         $file_path = __DIR__."/../tmp/".SiteConfig::getInstance()->get('file_buffer_name');
       }
       #write to file
       $dump_file = fopen($file_path, "w");
       fwrite($dump_file, json_encode($stats_data));
       fclose($dump_file);
   }

   /**
   * Outputs the statistical data
   * @param String $data a json encoded string with statistical data
   * @param String $ref the reference to print, if empty outputs all data
   */
   public static function output_result($json_data, $ref)
   {
       $data = json_decode($json_data,true);
       if (empty($ref)) {
         //print all data
         self::pretty_print_array($data);
       }else{
         $keys = explode(".",$ref);
         $value = $data;
         foreach ($keys as $key) {
           if(array_key_exists($key,$value)){
             $value = $value[$key];
           }else{
             FileLogger::getInstance()->error("Could not find $key, The statistic reference $ref does not exist, cannot do anything");
             print_r("0\n");
             die();
           }
         }
         print_r($value."\n");
       }
   }

   /**
   * turns a composite array into a collection of strings and outputs them to the
   * console as:
   *  key.subkey.subsubkey:value \n
   *  key1.subkey1.subsubkey1:value1 \n
   *  ...
   * @param array $data_array the array to print
   * @param string $parent_key the composite key to output
   */
   public static function pretty_print_array($data_array,$parent_key=""){
     $result = "";
     foreach ($data_array as $key => $value) {
       $pkey = "$parent_key.$key";
       if(empty($parent_key)){
         $pkey = $key;
       }
       if(is_array($value)){
         self::pretty_print_array($value,$pkey);
       }else{
         print_r("$pkey:$value\n");
       }
     }
   }

 }
