<?php
/*
  * Prints statistical information about a Wowza instance.
  * The statistical information is stored in a temporary file and is refreshed
  * after a timeout, to prevent the overload of the server.
  * On refresh, several calls are made to the Wowza API to get statistical information
  * about the Wowza server instance.
  *
  */

use Fccn\Lib\WowzaApi;
use Fccn\Lib\Utils;
use Fccn\Lib\SiteConfig;
use Fccn\Lib\FileLogger;

// Autoload
require __DIR__.'/../vendor/autoload.php';
//define config file
if (!defined('CONFIG_FILE')) {
    define("CONFIG_FILE", __DIR__ . "/../app/config.php");
}

$tmp_file_path = __DIR__."/../tmp/".SiteConfig::getInstance()->get('file_buffer_name');
if (Utils::can_update($tmp_file_path)){
  Utils::update_stats_file($tmp_file_path);
}
/*
if (file_exists($tmp_file_path)) {
    #check when last modified
    $time_limit = filemtime($tmp_file_path)+SiteConfig::getInstance()->get('stats_file_timeout');
    clearstatcache();
    if (time() > $time_limit) {
        Utils::update_stats_file();
    }
} else {
    Utils::update_stats_file();
}*/

if (!file_exists($tmp_file_path)) {
    FileLogger::getInstance()->error("The statistics file $tmp_file_path was not found, cannot do anything");
    print_r("stats file $tmp_file_path not found!!");
    die();
}

$stat_ref = "";
if (sizeof($argv) > 1) {
    FileLogger::getInstance()->debug("getting statistic reference: ".$argv[1]);
    $stat_ref = $argv[1];
}
$stats_data = file_get_contents($tmp_file_path);
#output the requested data
Utils::output_result($stats_data, $stat_ref);
