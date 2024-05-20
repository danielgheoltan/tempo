<?php

header('Content-Type: application/json; charset=utf-8');

if (
    !isset($_POST['index'])
) {
    exit('{"status": "error", "message": "Missing some parameters when trying to save work log."}');
}

// ----------------------------------------------------------------------------

include('../../includes/config.php');
include('../../includes/db.php');

// ----------------------------------------------------------------------------

if (!empty($_POST['index'])) {
    $sqlCommand = <<<MYSQL
        DELETE FROM `timesheet`
        WHERE `index` = {$_POST['index']};
MYSQL;
}

usleep(300 * 1000);

if ($_ENV['mysqli']->query($sqlCommand) === TRUE) {
    echo '{"status": "success", "message": "Record successfully deleted from database."}';
} else {
    echo '{"status": "error", "message": "' . ('Error:' . PHP_EOL . $_ENV['mysqli']->error) . PHP_EOL . ('SQL statement:' . PHP_EOL . htmlentities($sqlCommand)) . '"}';
}
