#!/usr/bin/env bash
# set -o errexit
# logfilecommand='--logfile=/etc/orthanc/logs/log'
# current_time=$(date "+%Y.%m.%d-%H.%M.%S")
# suffix='log'
# logfile=$logfilecommand.$current_time.$suffix
# # generate the configuration file
# cd /startup
# python3 generateConfiguration.py
# 
# if [[ $TRACE_ENABLED == true ]]; then
# 	verbosity=--trace
# elif [[ $VERBOSE_ENABLED == true ]]; then
# 	verbosity=--verbose
# fi
# 
# jobs=""
# if [[ $NO_JOBS == true ]]; then
# 	jobs=--no-jobs
# fi
# 
# unlock=""
# if [[ $UNLOCK == true ]]; then
# 	unlock=--unlock
# fi
# 
# argv=(Orthanc $verbosity $jobs $unlock $logfile "$@")
# echo "Startup command: ${argv[*]}" >&2
# exec "${argv[@]}"

cd /scripts
python3 -u  mpps.py