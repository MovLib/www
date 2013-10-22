# MariaDB events
Events can help to execute tasks on a regular basis, like cron jobs. Because we are using the `--skip-grant-tables`
option the events scheduler is not available. The SQL files in this directory shall work as examples, we use cron jobs
to execute our `cron.php` file.
