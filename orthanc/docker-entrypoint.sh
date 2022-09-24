#!/usr/bin/env bash
set -o errexit
logfilecommand='--logfile=/etc/orthanc/logs/'
current_time=$(date "+%Y.%m.%d-%H.%M.%S")
logfile=$logfilecommand$current_time.log
# generate the configuration file
cd /startup
python3 generateConfiguration.py

if [[ $TRACE_ENABLED == true ]]; then
	trace=--trace
fi
if [[ $VERBOSE_ENABLED == true ]]; then
	verbosity=--verbose
fi

jobs=""
if [[ $NO_JOBS == true ]]; then
	jobs=--no-jobs
fi

argv=(Orthanc $verbosity $trace $jobs $logfile "$@")
echo "Startup command: ${argv[*]}" >&2
exec "${argv[@]}"