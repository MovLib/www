#!/bin/sh

### BEGIN INIT INFO
# Provides:           nginx
# Required-Start:     $local_fs $remote_fs $network $syslog $named
# Required-Stop:      $local_fs $remote_fs $network $syslog $named
# Default-Start:      2 3 4 5
# Default-Stop:       0 1 6
# Short-Description:  nginx LSB init script
# Description:        nginx Linux Standards Base compliant init script.
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
# nginx Linux Standards Base compliant init script.
#
# LINK:       https://wiki.debian.org/LSBInitScripts
# AUTHOR:     Karl Blessing <http://kbeezie.com/debian-ubuntu-nginx-init-script/>
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
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


# The name of the service (must be the first variable).
NAME="nginx"

# Absolute path to the executable.
DAEMON="/usr/local/sbin/${NAME}"

# Arguments that should be passed to the executable.
DAEMON_ARGS=""

# Absolute path to the client body temporary directory. See compilation flag
# --http-client-body-temp-path
PATH_BODY="/run/shm/${NAME}/body"

# Absolute path to the FastCGI temporary directory. See compilation flag
# --http-fastcgi-temp-path
PATH_FCGI="/run/shm/${NAME}/fastcgi/temp"

# Absolute path to the PID file.
PIDFILE="/run/${NAME}.pid"


# -----------------------------------------------------------------------------
#                                                                     Bootstrap
# -----------------------------------------------------------------------------


# Always validate configuration, display problem if any and exit script.
${DAEMON} -qt
if [ ${?} -gt 0 ]; then
  log_failure_msg ${NAME} "invalid configuration"
  exit 1
fi

# Check return status of EVERY command
set -e

# Check if ${NAME} is a file and executable, if not assume it's not installed.
if [ ! -x ${DAEMON} ]; then
  log_failure_msg ${NAME} "not installed"
  exit 1
fi

# This script is only accessible for root (sudo).
if [ $(id -u) != 0 ]; then
  log_failure_msg "super user only!"
  exit 1
fi

# Create cache directories if the don't exist
test -d ${PATH_BODY} || mkdir -p ${PATH_BODY}
test -d ${FCGI_PATH} || mkdir -p ${FCGI_PATH}

# Always check if service is already running.
RUNNING=$(start-stop-daemon --start --quiet --pidfile ${PIDFILE} --exec ${DAEMON} --test && echo "false" || echo "true")


# -----------------------------------------------------------------------------
#                                                                     Functions
# -----------------------------------------------------------------------------


###
# Reloads the service.
#
# RETURN:
#   0 - successfully reloaded
#   1 - reloading failed
###
reload_service() {
  start-stop-daemon --stop --signal HUP --quiet --pidfile ${PIDFILE} --exec ${DAEMON}
}

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
###
stop_service() {
  start-stop-daemon --stop --quiet --pidfile ${PIDFILE} --name ${NAME}
}

###
# Load OCSP file into nginx's cache. Executed as a detached process as it may take some time.
###
load_ocsp_file() {
  openssl s_client -connect 127.0.0.1:443 -tls1 -tlsextdebug -status <&- 1<&- 2<&- &
}


# -----------------------------------------------------------------------------
#                                                                  Handle Input
# -----------------------------------------------------------------------------


case ${1} in

  force-reload|reload)
    if [ ${RUNNING} = "false" ]; then
      log_failure_msg ${NAME} "not running"
    else
      log_daemon_msg ${NAME} "reloading configuration"
      reload_service || log_end_msg 1
      load_ocsp_file
      log_end_msg 0
    fi
  ;;

  restart)
    if [ ${RUNNING} = "false" ]; then
      log_failure_msg ${NAME} "not running"
    else
      log_daemon_msg ${NAME} "restarting"
      stop_service || log_end_msg 1
      sleep 0.1
      start_service || log_end_msg 1
      load_ocsp_file
      log_end_msg 0
    fi
  ;;

  start)
    if [ ${RUNNING} = "true" ]; then
      log_success_msg ${NAME} "already started"
    else
      log_daemon_msg ${NAME} "starting"
      start_service || log_end_msg 1
      load_ocsp_file
      log_end_msg 0
    fi
  ;;

  status)
    status_of_proc ${DAEMON} ${NAME} && exit 0 || exit ${?}
  ;;

  stop)
    if [ ${RUNNING} = "false" ]; then
      log_success_msg ${NAME} "already stopped"
    else
      log_daemon_msg ${NAME} "stopping"
      stop_service && log_end_msg 0 || log_end_msg 1
    fi
  ;;

  *)
    echo "Usage: ${NAME} {force-reload|reload|restart|start|status|stop}" >&2
    exit 1
  ;;

esac
:

exit 0
