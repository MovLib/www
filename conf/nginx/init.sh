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


# Load the VERBOSE settings and other rcS variables.
. /lib/init/vars.sh

# Load the LSB log_* functions.
. /lib/lsb/init-functions


# -----------------------------------------------------------------------------
#                                                                     Variables
# -----------------------------------------------------------------------------


# The full path to the nginx executable.
DAEMON="/usr/local/sbin/nginx"

# Arguments that should be passed to nginx.
DAEMON_ARGS=""

# See compilation flag --http-client-body-temp-path
PATH_BODY="/run/shm/nginx/body"

# See compilation flag --http-fastcgi-temp-path
PATH_FCGI="/run/shm/nginx/fastcgi/temp"

# Absolute path to the PID file.
PIDFILE="/var/run/nginx.pid"


# -----------------------------------------------------------------------------
#                                                                     Bootstrap
# -----------------------------------------------------------------------------


# Check return status of EVERY command
set -e

# Check if nginx is a file and executable, if not assume it's not installed.
if [ ! -x ${DAEMON} ]; then
  log_failure_msg "nginx not installed"
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

# Always validate configuration, display problem if any and exit script.
${DAEMON} -qt

# Always check if nginx is already running (used later for proper error messages).
RUNNING=$(start-stop-daemon --start --quiet --pidfile ${PIDFILE} --exec ${DAEMON} --test && echo "false" || echo "true")


# -----------------------------------------------------------------------------
#                                                                     Functions
# -----------------------------------------------------------------------------


###
# Starts the nginx service.
#
# RETURN:
#   0 - successfully started
#   1 - starting failed
###
start_nginx() {
  start-stop-daemon --start --quiet --pidfile ${PIDFILE} --exec ${DAEMON} -- ${DAEMON_ARGS}
}

###
# Stops the nginx service.
#
# RETURN:
#   0 - successfully stopped
#   1 - stopping failed
###
stop_nginx() {
  start-stop-daemon --stop --quiet --pidfile ${PIDFILE} --name nginx
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

  start)
    if [ ${RUNNING} = "true" ]; then
      log_daemon_msg "starting nginx" "already running"
    else
      log_daemon_msg "starting nginx"
      start_nginx || log_end_msg 1
    fi
    load_ocsp_file
    log_end_msg 0
  ;;

  stop)
    if [ ${RUNNING} = "false" ]; then
      log_daemon_msg "stopping nginx" "not running"
    else
      log_daemon_msg "stopping nginx"
      stop_nginx || log_end_msg 1
    fi
    load_ocsp_file
    log_end_msg 0
  ;;

  restart)
    if [ ${RUNNING} = "false" ]; then
      log_daemon_msg "restarting nginx" "not running"
      log_end_msg 1
    else
      log_daemon_msg "restarting nginx"
      stop_nginx || log_end_msg 1
      sleep 1
      start_nginx || log_end_msg 1
    fi
    load_ocsp_file
    log_end_msg 0
  ;;

  reload|force-reload)
    if [ ${RUNNING} = "false" ]; then
      log_daemon_msg "reloading nginx configuration" "not running"
      log_end_msg 1
    else
      log_daemon_msg "reloading nginx configuration"
      start-stop-daemon --stop --signal HUP --quiet --pidfile ${PIDFILE} --exec ${DAEMON} || log_end_msg 1
    fi
    load_ocsp_file
    log_end_msg 0
  ;;

  status)
    status_of_proc "${DAEMON}" "nginx" && exit 0 || exit 1
  ;;

  *)
    echo "Usage: ${SCRIPTNAME} (start|stop|restart|reload|force-reload|status}" >&2
    exit 1
  ;;

esac
:
