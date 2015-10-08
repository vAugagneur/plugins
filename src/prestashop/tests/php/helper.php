<?php

date_default_timezone_set('Europe/Paris');

// Command that starts the built-in web server
$command = sprintf(
    'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
    TEST_SERVER_HOST,
    TEST_SERVER_PORT,
    TEST_SERVER_DOCROOT
);

// Execute the command and store the process ID
echo $command, "\n";
$output = array();
exec($command, $output);
$pid = (int) $output[0];

echo sprintf(
    '%s - Web server started on %s:%d with PID %d',
    date('r'),
    TEST_SERVER_HOST,
    TEST_SERVER_PORT,
    $pid
) . PHP_EOL;

sleep(1);

// Kill the web server when the process ends
register_shutdown_function(function () use ($pid) {
    echo sprintf('%s - Killing process with ID %d', date('r'), $pid) . PHP_EOL;
    exec('kill ' . $pid);
});
