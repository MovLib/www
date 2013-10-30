#!/bin/sh

### BEGIN INIT INFO
# Provides:          ${NAME}
# Required-Start:    $remote_fs $network
# Required-Stop:     $remote_fs $network
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: starts ${NAME}
# Description:       Starts PHP FastCGI Process Manager Daemon
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
# ${NAME} Linux Standards Base compliant init script.
#
# LINK:       https://wiki.debian.org/LSBInitScripts
# AUTHOR:     Ondrej Sury <ondrej@debian.org>
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
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
NAME="php-fpm"

# Absolute path to the executable.
DAEMON="/usr/local/sbin/${NAME}"

# Arguments that should be passed to the executable.
DAEMON_ARGS=""

# The php-fpm group.
GROUP="www-data"

# The php-fpm log directory.
LOG_DIR="/var/log/php-fpm"

# The php-fpm error log.
LOG_ERROR="error.log"

# The php-fpm slow log.
LOG_SLOW="slow.log"

# Absolute path to the PID file.
PIDFILE="/run/${NAME}.pid"

# Absolute path to the upload temporary directory.
UPLOAD_TMP_DIR="/tmp/${NAME}"

# The php-fpm user.
USER="www-data"


# -----------------------------------------------------------------------------
#                                                                     Bootstrap
# -----------------------------------------------------------------------------


# Validate the configuration file.
FPM_ERROR=$(${DAEMON} ${DAEMON_ARGS} -t 2>&1 | grep -cs "ERROR")
if [ ${FPM_ERROR} -gt 0 ]; then
  ${DAEMON} ${DAEMON_ARGS} -t
  log_failure_msg ${NAME} "invalid configuration"
  exit 1
fi

# Check return status of EVERY command
set -e

# Check if we have a file and that it's executable, if not assume it's not installed.
if [ ! -x ${DAEMON} ]; then
  log_failure_msg ${NAME} "not installed"
  exit 1
fi

# Create the temporary upload directory if it's missing.
if [ ! -d ${UPLOAD_TMP_DIR} ]; then
  mkdir -p ${UPLOAD_TMP_DIR}
  chmod 2770 ${UPLOAD_TMP_DIR}
  chown ${USER}:${GROUP} ${UPLOAD_TMP_DIR}
fi

if [ ! -d ${LOG_DIR} ]; then
  mkdir ${LOG_DIR}
fi
touch "${LOG_DIR}/${ERROR_LOG}"
touch "${LOG_DIR}/${SLOW_LOG}"
chmod 0770 /var/log/php-fpm
chmod 0660 /var/log/php-fpm/*

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
  start-stop-daemon --stop --signal USR2 --quiet --pidfile ${PIDFILE} --exec ${DAEMON}
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


# -----------------------------------------------------------------------------
#                                                                  Handle Input
# -----------------------------------------------------------------------------


case ${1} in

  force-reload|reload)
    if [ ${RUNNING} = "false" ]; then
      log_failure_msg ${NAME} "not running"
    else
      log_daemon_msg ${NAME} "reloading configuration"
      reload_service && log_end_msg 0 || log_end_msg 1
    fi
  ;;

  restart)
    if [ ${RUNNING} = "false" ]; then
      log_success_msg ${NAME} "not running"
    else
      log_daemon_msg ${NAME} "restarting"
      stop_service || log_end_msg 1
      sleep 0.1
      start_service && log_end_msg 0 || log_end_msg 1
    fi
  ;;

  start)
    if [ ${RUNNING} = "true" ]; then
      log_success_msg ${NAME} "already started"
    else
      log_daemon_msg ${NAME} "starting"
      start_service && log_end_msg 0 || log_end_msg 1
    fi
  ;;

  stop)
    if [ ${RUNNING} = "false" ]; then
      log_success_msg ${NAME} "already stopped"
    else
      log_daemon_msg ${NAME} "stopping"
      stop_service && log_end_msg 0 || log_end_msg 1
    fi
  ;;

  status)
    status_of_proc ${DAEMON} ${NAME} && exit 0 || exit ${?}
  ;;

  *)
    echo "Usage: ${NAME} {force-reload|reload|restart|start|status|stop}" >&2
    exit 1
  ;;

esac
:

exit 0
