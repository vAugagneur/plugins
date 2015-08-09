<?php

date_default_timezone_set('Europe/Paris');

require __DIR__ . '/../cashway_lib.php';


// Command that starts the built-in web server
$command = sprintf(
    'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
    WEB_SERVER_HOST,
    WEB_SERVER_PORT,
    WEB_SERVER_DOCROOT
);

// Execute the command and store the process ID
$output = array();
exec($command, $output);
$pid = (int) $output[0];

echo sprintf(
    '%s - Web server started on %s:%d with PID %d',
    date('r'),
    WEB_SERVER_HOST,
    WEB_SERVER_PORT,
    $pid
) . PHP_EOL;

sleep(1);

// Kill the web server when the process ends
register_shutdown_function(function() use ($pid) {
    echo sprintf('%s - Killing process with ID %d', date('r'), $pid) . PHP_EOL;
    exec('kill ' . $pid);
});

function get_conf()
{
    return array(
        'API_URL' => sprintf('http://%s:%d', WEB_SERVER_HOST, WEB_SERVER_PORT),
        'API_KEY' => 'testk-KEY',
        'API_SECRET' => 'testk-SECRET'
    );
}
