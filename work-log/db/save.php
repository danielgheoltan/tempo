<?php

header('Content-Type: application/json; charset=utf-8');

if (
    !isset($_POST['index']) &&
    !isset($_POST['issue_key']) &&
    !isset($_POST['description']) &&
    !isset($_POST['time_spent']) &&
    !isset($_POST['started']) &&
    !isset($_POST['synced'])
) {
    exit('{"status": "error", "message": "Missing some parameters when trying to save work log."}');
}

// ----------------------------------------------------------------------------

include('../../includes/config.php');
include('../../includes/db.php');

// ----------------------------------------------------------------------------

$a = [
    'index'         => '',
    'id'            => '0',
    'issue_key'     => 'NULL',
    'description'   => 'NULL',
    'time_spent'    => 'NULL',
    'started'       => 'NULL',
    'synced'        => '0',
];

foreach ($a as $k => $v) {
    if (isset($_POST[$k])) {
        if (!empty($_POST[$k])) {
            $a[$k] = '"' . addslashes(rawurldecode($_POST[$k])) . '"';
        }
    } else {
        $a[$k] = $k;
    }
}

// ----------------------------------------------------------------------------

if (empty($_POST['index'])) {
    $sqlCommand = <<<MYSQL
        INSERT INTO `timesheet` (`id`, `issue_key`, `description`, `time_spent`, `started`, `synced`)
        VALUES ({$a['id']}, {$a['issue_key']}, {$a['description']}, {$a['time_spent']}, {$a['started']}, {$a['synced']});
MYSQL;
} else {
    $sqlCommand = <<<MYSQL
        UPDATE `timesheet` SET
            `id` = {$a['id']},
            `issue_key` = {$a['issue_key']},
            `description` = {$a['description']},
            `time_spent` = {$a['time_spent']},
            `synced` = {$a['synced']}
        WHERE `index` = {$a['index']};
MYSQL;
}

usleep(300 * 1000);

if ($_ENV['mysqli']->query($sqlCommand) === TRUE) {
    if (empty($_POST['index'])) {
        $a['index'] = '"' . mysqli_insert_id($_ENV['mysqli']) . '"';
    }
    echo '{"index": ' . $a['index'] . ', "status": "success", "message": "Record successfully updated in database."}';
} else {
    echo '{"status": "error", "message": "' . ('Error:' . PHP_EOL . $_ENV['mysqli']->error) . PHP_EOL . ('SQL statement:' . PHP_EOL . htmlentities($sqlCommand)) . '"}';
}
