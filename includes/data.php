<?php

$timesheet = [];

$sqlCommand = <<<MYSQL
    SELECT
        `index`,
        `id`,
        `issue_key`,
        `description`,
        TIME_FORMAT(`time_spent`, 'PT%HH%iM%sS') AS "time_spent_formatted",
        `started`,
        `synced`
        FROM `timesheet`
        WHERE DATE(`started`) >= "{$_ENV['beginDateTime']->format('Y-m-d')}" AND
              DATE(`started`) <= "{$_ENV['endDateTime']->format('Y-m-d')}"
        ORDER BY `index`;
MYSQL;

$result = $_ENV['mysqli']->query($sqlCommand);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $timeSpent = NULL;
        try {
            $timeSpent = new DateInterval($row['time_spent_formatted']);
        } catch (Exception $e) {}
        
        $timeSpentFormatted = ($timeSpent ? $timeSpent->format('%h:%I') : '');

        try {
            $started = new DateTime($row['started']);

            $k = $started->format('Y-m-d');

            $timesheet[$k][] = [
                'index'                => (int) $row['index'],
                'id'                   => (int) $row['id'],
                'issue_key'            => $row['issue_key'],
                'description'          => $row['description'],
                'time_spent'           => $timeSpent,
                'time_spent_formatted' => $timeSpentFormatted,
                'started'              => $started,
                'synced'               => (bool) $row['synced'],
            ];
        } catch (Exception $e) {}
    }
}
