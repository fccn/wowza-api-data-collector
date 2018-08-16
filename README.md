# Wowza API data collector

A tool for acquisition of statistical data from Wowza API that can be used provide information to serveral services such as SNMP.
To prevent overloading the Wowza server with requests, the information is stored in temporary dump file and is only updated after a given timeout. The tool will read the information from that file instead of acquiring the information from the API each time it is called.

At the moment, the tool provides the following information:

General information:
- cpu.user
- cpu.system
- memory.heapfree
- memory.heapused
- connections.total

For each application defined in the configuration file:
- connections.[application].bytesratein
- connections.[application].bytesrateout
- connections.[application].total
- connections.[application].webm
- connections.[application].rtmp
- connections.[application].dash
- connections.[application].hls
- connections.[application].smooth
- connections.[application].rtp

## Installation

To install this collection in your project clone the project into your computer and run composer install. You will also need to initialize the configuration file in **app/config.php**. You can use the sample configuration file in **app/config.php.sample** as base.

```
git clone https://github.com/fccn/wowza-api-data-collector.git
cd wowza-api-snmp-collector
composer install
cp app/config.php.sample app/config.php
```

## Configuration

This project uses the site configuration loader from the [Webapp Tools - common](https://github.com/fccn/wt-common) project. The following key-value pairs need to be added to the application configuration file *$c* array in **app/config.php**:

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
Which outputs, for example:
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

### Docker

It is possible to create a docker image of this project using the tools available in *.docker* folder. To build an image create a **deploy.env** file using the **deploy.env.sample** sample file and run the following command:

```
make image

```

This will create a local image named wowza-api-data-collector (as defined by the *APP_NAME* variable in **deploy.env**).

To list all the possible make commands with description, in the **.docker** folder run the following command:
```
make help

```

After creating the image, you need to prepare the application configuration file (**app/config.php**), as described in the [Configuration](#Configuration) section, before running the container with this image. To import the configuration file to the docker container use the -v parameter of the docker run command to bind mount the configuration file. It is also advised to mount the tmp/ and log/ folders for persistence between container runs.

The following command runs the container with binds for the tmp/ and log/ folders to display the value for the total connections. Run the follwing command on the project root:

```
docker run -v $(pwd)/app/config.php:/app/app/config.php -v $(pwd)/tmp:/app/tmp -v $(pwd)/logs:/app/logs -it wowza-api-data-collector:latest /app/bin/wowza_data_collector.sh connections.total

```

A sample shell script in **bin/run_in_docker.sh** shows how to incorporate the run command for multiple purposes, for example to obtain the same output:
```
bin/run_in_docker.sh connections.total

```

## Testing

To be done..

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/fccn/wowza-api-data-collector/tags).

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
