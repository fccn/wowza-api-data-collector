#!/bin/bash
# ------------------------------------------------------------------------------
# Runs this tool in a docker container and outputs the result
# Before running make sure you pulled the latest docker image with
#   docker pull <docker-repo>/wowza-api-data-collector:latest
# Run from project base dir (i.e.):
#  bin/run_in_docker.sh connections.total
#-------------------------------------------------------------------------------

# get full path for current directory
CURRENT_DIR=$(pwd)

# import deploy config
source $CURRENT_DIR/.docker/deploy.env

docker run -v $(pwd)/app/config.php:/app/app/config.php -v $(pwd)/logs:/app/logs -v $(pwd)/tmp:/app/tmp -it wowza-api-data-collector:latest /app/bin/wowza_data_collector.sh $1
