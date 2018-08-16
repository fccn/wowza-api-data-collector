# Wowza API data collector

A tool for acquisition of statistical data from Wowza API that can be used provide information to serveral services such as SNMP.
To prevent overloading the Wowza server with requests, the information is stored in temporary dump file and is only updated after a given timeout. The tool will read the information from that file instead of acquiring the information from the API each time it is called.

## Installation

To install this collection in your project clone the project into your computer and run composer install.

```
git clone https://github.com/fccn/wowza-api-data-collector.git
cd wowza-api-snmp-collector
composer install

```

## Configuration

This project  makes use of the site configuration loader from the [Webapp Tools - common](https://github.com/fccn/wt-common) project. The following key-value pairs need to be added to the application configuration file *$c* array:

```php
$c = array(
    ...
    //--------     logging            --------------------------
        "logfile_path"    => __DIR__ . '/../logs/application.log',   //path for application logs
        "logfile_level"  => "WARNING", //possible options: DEBUG, ERROR, INFO
    //--------     wowza API settings --------------------------
        "wowza_api_user"         => "api username", //the API user
        "wowza_api_pass"         => "api password",  //password of the API user
        "wowza_default_server"   => "_defaultServer_", //the default server name
        "wowza_default_vhost"    => "_defaultVHost_",  //the default host name
        "wowza_api_url"           => "http://my.wowza.url:8087",  //url to the wowza api with port number
        "wowza_apps"             => ["app1","app2"], //list of application instances to obtain statistics from
    //--------     file buffer settings  --------------------------
        "file_buffer_name"          => "file.json", //name of file where buffer data is written
        "file_buffer_timeout"       => 30, //the time to refresh the file, in seconds
     ...
);

```
For a complete example you can check the sample configuration file in **app/config.php.sample**.

## Usage

To run the tool call the **collect_wowza_stats.php** script in the *utils* folder. To show a list of all the information acquired by this tool, on the project root run:

```
php utils/collect_wowza_stats.php

```
Which outputs something like:
```
cpu.user:1
cpu.system:0
memory.heapfree:9842378832
memory.heapused:643381168
connections.total:11
connections.live.bytesratein:2839
connections.live.bytesrateout:2908
connections.live.total:11
connections.live.webm:0
connections.live.rtmp:11
connections.live.dash:0
connections.live.hls:0
connections.live.smooth:0
connections.live.rtp:0
...

```

To present the value for a specific statistic, like for example the total connections, on the project root run:
```
php utils/collect_wowza_stats.php connections.total

```
Which outputs:
```
11

```
It is also possible to use the shell script provided in **bin/wowza_data_collector.sh**, to show a list of all the information acquired:
```
 bin/wowza_data_collector.sh
```
or the value for a specific statistic
```
 bin/wowza_data_collector.sh connections.total
```

## Testing

To be done..

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/fccn/wowza-api-data-collector/tags).

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
