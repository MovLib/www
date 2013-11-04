#!/bin/sh

### BEGIN INIT INFO
# Provides:           memcached-server1
# Required-Start:     $local_fs $remote_fs $network $syslog $named
# Required-Stop:      $local_fs $remote_fs $network $syslog $named
# Default-Start:      2 3 4 5
# Default-Stop:       0 1 6
# Short-Description:  memcached-server1 LSB init script
# Description:        memcached-server1 Linux Standards Base compliant init script.
### END INIT INFO

# -----------------------------------------------------------------------------
# This file is part of MovLib.
#
# Copyright © 2013 MovLib.
#
# MovLib is free software: you can redistribute it and/or modify it under the
# terms of the GNU Affero General Public License as published by the Free
# Software Foundation, either version 3 of the License, or (at your option) any
# later version.
#
# MovLib is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY without even the implied warranty of MERCHANTABILITY or FITNESS FOR
# A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
# details.
#
# You should have received a copy of the GNU Affero General Public License
# along with MovLib. If not, see <http://www.gnu.org/licenses/>.
# -----------------------------------------------------------------------------

# -----------------------------------------------------------------------------
# memcached-server1 Linux Standards Base compliant init script.
#
# LINK:       https://wiki.debian.org/LSBInitScripts
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# AUTHOR:     Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINKE:      https://movlib.org/
# SINCE:      0.0.1-dev
# -----------------------------------------------------------------------------


# -----------------------------------------------------------------------------
#                                                                      Includes
# -----------------------------------------------------------------------------


# Load the LSB log_* functions.
. /lib/lsb/init-functions


# -----------------------------------------------------------------------------
#                                                                     Variables
# -----------------------------------------------------------------------------


# The name of the server (must be the first variable).
NAME="server1"

# The name of the service.
SERVICE_NAME="memcached-${NAME}"

# The user listening on the socket.
USER="www-data"

# The group listening on the socket.
GROUP="www-data"

# The amount of memory in MB.
MEMORY=64

# The number of threads to run.
THREADS=1

# Name of the executable.
EXE="memcached"

# Absolute path to the executable.
DAEMON="/usr/local/bin/${EXE}"

# Absolute path to the PID and socket directory.
PATH_DIR="/run/${EXE}"

# Absolute path to the listening socket of the memcached instance.
PATH_SOCKET="${PATH_DIR}/${NAME}.sock"

# Absolute path to the PID file.
PIDFILE="${PATH_DIR}/${NAME}.pid"

# Arguments that should be passed to the executable.
DAEMON_ARGS="-m ${MEMORY} -t ${THREADS} -d -r -u www-data -s ${PATH_SOCKET} -P ${PIDFILE}"


# -----------------------------------------------------------------------------
#                                                                     Bootstrap
# -----------------------------------------------------------------------------


# Check return status of EVERY command
set -e

# Check if ${SERVICE_NAME} is a file and executable, if not assume it's not installed.
if [ ! -x ${DAEMON} ]; then
  log_failure_msg ${SERVICE_NAME} "not installed"
  exit 1
fi

# This script is only accessible for root (sudo).
if [ $(id -u) != 0 ]; then
  log_failure_msg "super user only!"
  exit 1
fi

# Always check if service is already running.
RUNNING=$(start-stop-daemon --start --quiet --pidfile ${PIDFILE} --exec ${DAEMON} --test && echo "false" || echo "true")

# Create the directory for PID and socket files, if it doesn't exist.
if [ ! -d ${PATH_DIR} ]; then
  mkdir ${PATH_DIR}
  chown ${USER}:${GROUP} ${PATH_DIR}
fi


# -----------------------------------------------------------------------------
#                                                                     Functions
# -----------------------------------------------------------------------------


###
# Starts the service.
#
# RETURN:
#   0 - successfully started
#   1 - starting failed
###
start_service() {
  start-stop-daemon --start --quiet --pidfile ${PIDFILE} --exec ${DAEMON} -- ${DAEMON_ARGS}
}

###
# Stops the service.
#
# RETURN:
#   0 - successfully stopped
#   1 - stopping failed
#   2 - deletion of PID and/or socket failed
###
stop_service() {
  start-stop-daemon --stop --quiet --pidfile ${PIDFILE} --name ${EXE}
  if [ ${?} -eq 0 ]; then
    rm -f ${PIDFILE} ${PATH_SOCKET} && return 0 || return 2
  fi
  return 1
}


# -----------------------------------------------------------------------------
#                                                                  Handle Input
# -----------------------------------------------------------------------------


case ${1} in

  force-reload|reload|restart)
    if [ ${RUNNING} = "false" ]; then
      log_failure_msg ${SERVICE_NAME} "not running"
    else
      log_daemon_msg ${SERVICE_NAME} "restarting"
      stop_service
      case ${?} in
        1) log_failure_msg ${SERVICE_NAME} "couldn't stop" ;;
        2) log_failure_msg ${SERVICE_NAME} "couldn't delete PID and/or socket" ;;
      esac
      :
      sleep 0.1
      start_service && log_end_msg 0 || log_end_msg 1
    fi
  ;;

  start)
    if [ ${RUNNING} = "true" ]; then
      log_success_msg ${SERVICE_NAME} "already started"
    else
      log_daemon_msg ${SERVICE_NAME} "starting"
      start_service && log_end_msg 0 || log_end_msg 1
    fi
  ;;

  status)
    status_of_proc ${DAEMON} ${SERVICE_NAME} && exit 0 || exit ${?}
  ;;

  stop)
    if [ ${RUNNING} = "false" ]; then
      log_success_msg ${SERVICE_NAME} "already stopped"
    else
      log_daemon_msg ${SERVICE_NAME} "stopping"
      stop_service && log_end_msg 0 && exit 0
      case ${?} in
        1) log_failure_msg ${SERVICE_NAME} "couldn't stop" ;;
        2) log_failure_msg ${SERVICE_NAME} "couldn't delete PID and/or socket" ;;
        *) log_failure_msg ${SERVICE_NAME} "unknown error" ;;
      esac
      :
    fi
  ;;

  *)
    echo "Usage: ${SERVICE_NAME} {force-reload|reload|restart|start|status|stop}" >&2
    exit 1
  ;;

esac
:

exit 0
