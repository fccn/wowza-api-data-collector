#!/usr/bin/env sh
export APP_SRC=/app
cd $APP_SRC
bin/wowza_data_collector.sh $1
