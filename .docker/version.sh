#!/bin/sh
VERSION=`git ls-remote --tags git@github.com:fccn/wowza-api-data-collector.git | awk '{print $2}' | grep -v '{}' | awk -F"/" '{print $3}' | sort -n -t. -k1,1 -k2,2 -k3,3 | tail -n 1`

echo $VERSION
