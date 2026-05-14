<?php

// Cli To Start PHP Development From public folder
// Get command
$command = $argv[1] ?? null;

switch ($command) {
    case 'serve':
        # code...
        serve();
        break;

    default:
        # code...
        echo "not found command";
        break;
}


function serve()
{
    echo "Starting PHP Server! \n";
    $php = proc_open(
        "php -S localhost:8001 -t public",
        [STDIN, STDOUT, STDERR],
        $pipes
    );

    echo "Starting Vite Dev Serve";
    $npm = proc_open(
        "npm run dev",
        [STDIN, STDOUT, STDERR],
        $pipes
    );

    if (!is_resource($php) || !is_resource($npm)) {
        echo "Failed to start server\n";
        return;
    }

    echo "Servers running...\n";

    while (true) {
        sleep(1);
    }
}