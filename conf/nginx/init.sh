#!/bin/sh

### BEGIN INIT INFO
# Provides: nginx
# Required-Start: $local_fs $remote_fs $network $syslog $named
# Required-Stop: $local_fs $remote_fs $network $syslog $named
# Default-Start: 2 3 4 5
# Default-Stop: 0 1 6
# Short-Description: starts the nginx web server
# Description: starts nginx using start-stop-daemon
### END INIT INFO

###
# AUTHOR: Karl Blessing <http://kbeezie.com/debian-ubuntu-nginx-init-script/>
# AUTHOR: Richard Fussenegger <richard@fussenegger.info>
###

NAME="nginx"
DESC="nginx web server"

BODY_PATH="/run/shm/${NAME}/body"
DAEMON="/usr/local/sbin/${NAME}"
DAEMON_ARGS=""
FASTCGI_PATH="/run/shm/${NAME}/fastcgi/temp"
PATH="/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin"
PIDFILE="/var/run/${NAME}.pid"

# Check return status of EVERY command
set -e

# Load the VERBOSE setting and other rcS variables
. /lib/init/vars.sh

# Define LSB log_* functions.
# Depend on lsb-base (>= 3.0-6) to ensure that this file is present.
. /lib/lsb/init-functions

if [ ! -x ${DAEMON} ]; then
  log_failure_msg "${NAME} not installed"
  exit 1
fi

if [ $(id -u) != 0 ]; then
  log_failure_msg "super user only!"
  exit 1
fi

# Create cache directories if the don't exist
test -d ${BODY_PATH} || mkdir -p ${BODY_PATH}
test -d ${FCGI_PATH} || mkdir -p ${FCGI_PATH}

# Always validate configuration, display problem if any and exit script.
${DAEMON} -qt

# Always check if nginx is already running (used later for proper error messages).
RUNNING=$(start-stop-daemon --start --quiet --pidfile ${PIDFILE} --exec ${DAEMON} --test && echo "false" || echo "true")

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
  start-stop-daemon --stop --quiet --pidfile ${PIDFILE} --name ${NAME}
}

###
# Load OCSP file into nginx's cache.
###
load_ocsp_file() {
  openssl s_client -connect 127.0.0.1:443 -tls1 -tlsextdebug -status <&- 1<&- 2<&- &
}

case ${1} in
  start)
    if [ ${RUNNING} = "true" ]; then
      log_daemon_msg "starting ${NAME}" "already running"
    else
      log_daemon_msg "starting ${NAME}"
      start_nginx || log_end_msg 1
    fi
    load_ocsp_file
    log_end_msg 0
  ;;

  stop)
    if [ ${RUNNING} = "false" ]; then
      log_daemon_msg "stopping ${NAME}" "not running"
    else
      log_daemon_msg "stopping ${NAME}"
      stop_nginx || log_end_msg 1
    fi
    load_ocsp_file
    log_end_msg 0
  ;;

  restart)
    if [ ${RUNNING} = "false" ]; then
      log_daemon_msg "restarting ${NAME}" "not running"
      log_end_msg 1
    else
      log_daemon_msg "restarting ${NAME}"
      stop_nginx || log_end_msg 1
      sleep 1
      start_nginx || log_end_msg 1
    fi
    load_ocsp_file
    log_end_msg 0
  ;;

  reload)
    if [ ${RUNNING} = "false" ]; then
      log_daemon_msg "reloading ${NAME} configuration" "not running"
      log_end_msg 1
    else
      log_daemon_msg "reloading ${NAME} configuration"
      start-stop-daemon --stop --signal HUP --quiet --pidfile ${PIDFILE} --exec ${DAEMON} || log_end_msg 1
    fi
    load_ocsp_file
    log_end_msg 0
  ;;

  status)
    status_of_proc "${DAEMON}" "${NAME}" && exit 0 || exit 1
  ;;

  *)
    echo "Usage: ${SCRIPTNAME} (start|stop|restart|reload|status}" >&2
    exit 1
  ;;

esac
:
