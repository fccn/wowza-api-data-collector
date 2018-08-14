#!/bin/sh
# ------------------------------------------------------------------------------
# SNMP output script -
# shell script for calling php script to output a statistical reference value for
# SNMP consuption
#
# Run from project base dir (i.e.):
#  bin/snmp_output.sh connections.total
# ------------------------------------------------------------------------------
php utils/collect_wowza_stats.php $1
