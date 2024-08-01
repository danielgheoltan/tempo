<?php

$_ENV['timestamp'] = md5('2024-07-31 00:00');
$_ENV['protocol'] = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
$_ENV['base_url'] = $_ENV['protocol'] . '://' . $_SERVER['HTTP_HOST'] . '/tempo';

$_ENV['free_days'] = [ // TODO
    '2024-04-22' => 'Meet Magento conference',
    '2024-04-29' => 'Time off: Vacation',
    '2024-04-30' => 'Time off: Vacation',
    '2024-05-01' => 'Public holiday: Labor Day',
    '2024-05-02' => 'Time off: Vacation',
    '2024-05-03' => 'Public holiday: Orthodox Good Friday',
    '2024-05-06' => 'Public holiday: Orthodox Easter Monday',
    '2024-06-24' => 'Orthodox Pentecost Monday',
    '2024-07-15' => 'Time off: Vacation',
    '2024-07-16' => 'Time off: Vacation',
    '2024-07-17' => 'Time off: Vacation',
    '2024-07-18' => 'Time off: Vacation',
    '2024-07-19' => 'Time off: Vacation',
];
$_ENV['locale'] = $_GET['locale'] ?? 'en_GB';
$_ENV['locale_alt'] = ($_ENV['locale'] === 'en_GB') ? 'ro_RO' : 'en_GB';
$_ENV['language'] = explode('_', $_ENV['locale'])[0];
$_ENV['language_alt'] = ($_ENV['language'] === 'en') ? 'ro' : 'en';
$_ENV['timezone'] = 'Europe/Bucharest';
$_ENV['weekends'] = ($_GET['weekends'] ?? 'false') === 'true';
$_ENV['description_rows'] = $_GET['description_rows'] ?? '1';
$_ENV['description_rows_alt'] = ($_ENV['description_rows'] === '1') ? '3' : '1';

date_default_timezone_set($_ENV['timezone']);
setlocale(LC_TIME, $_ENV['locale']);

const TRANSLATIONS = [
    'All rights reserved.' => [
        'ro' => 'Toate drepturile rezervate.',
    ],
    'Are you sure you want to delete this record?' => [
        'ro' => 'Ești sigur că dorești să ștergi această înregistrare?',
    ],
    'Delete' => [
        'ro' => 'Șterge',
    ],
    'English' => [
        'ro' => 'Engleză',
    ],
    'Expand description' => [
        'ro' => 'Extinde descriere',
    ],
    'Hide weekends' => [
        'ro' => 'Ascunde weekenduri',
    ],
    'Narrow description' => [
        'ro' => 'Restrânge descriere',
    ],
    'Romanian' => [
        'ro' => 'Română',
    ],
    'Save' => [
        'ro' => 'Salvează',
    ],
    'Show weekends' => [
        'ro' => 'Afișează weekenduri',
    ],
    'Ticket key' => [
        'ro' => 'Cheie tichet',
    ],
    'Update' => [
        'ro' => 'Actualizează',
    ],
    'Work description' => [
        'ro' => 'Descriere activitate',
    ],
];

$_ENV['i18n'] = function (string $text = '', array $translations = TRANSLATIONS): string {
    return $translations[$text][$_ENV['language']] ?? $text;
};

$_ENV['translations'] = json_encode(TRANSLATIONS, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$_ENV['url'] = function (string $path = '/', array $query = []): string {
    $result =  $_ENV['protocol'] . '://' . $_SERVER['HTTP_HOST'] . '/tempo/';
    $path = trim($path, '/');
    if (!empty($path)) {
        $result .= $path . '/';
    }
    parse_str($_SERVER['QUERY_STRING'], $q);
    foreach ($query as $k => $v) {
        $q[$k] = $v;
    }
    $q = count($q) ? '?' . http_build_query($q) : '';
    return $result . $q;
};

$_ENV['date_formatters'] = [
    /**
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     */
    new IntlDateFormatter(
        $_ENV['locale'],
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        $_ENV['timezone'],
        IntlDateFormatter::GREGORIAN,
        'EEEE, dd MMMM'
    ),
];

// ----------------------------------------------------------------------------

$beginDateTime = new DateTime();
$endDateTime = new DateTime();
$today = new DateTime();

$beginDateTime->modify('first day of this month');

$datepicker = $_SESSION['form_data']['datepicker'] ?? false;
if ($datepicker) {
    list($begin, $end) = explode(' - ', $datepicker);
    $beginDateTime = new DateTime($begin);
    $endDateTime = new DateTime($end);
}

$interval = $beginDateTime->diff($today);
if ($interval->days <= 7) {
    // Change the $beginDateTime to its previous day...
    $beginDateTime->sub(new DateInterval('P1D'));

    switch ($beginDateTime->format('w')) {
        case '6':
            // If $beginDateTime is Saturday, change it to Friday.
            $beginDateTime->sub(new DateInterval('P1D'));
            break;
        case '0':
            // If $beginDateTime is Sunday, change it to Friday.
            $beginDateTime->sub(new DateInterval('P2D'));
            break;
    }
}

$_ENV['period'] = new DatePeriod(
    $beginDateTime,
    DateInterval::createFromDateString('1 day'),
    $endDateTime,
    DatePeriod::INCLUDE_END_DATE
);

setcookie('easepick-begin-datetime', $beginDateTime->format('Y-m-d'), time() + 3600, '/', '', 0);
setcookie('easepick-end-datetime', $endDateTime->format('Y-m-d'), time() + 3600, '/', '', 0);
setcookie('easepick-lang', str_replace('_', '-', $_ENV['locale']), time() + 3600, '/', '', 0);

$_ENV['beginDateTime'] = $beginDateTime;
$_ENV['endDateTime'] = $endDateTime;

// ----------------------------------------------------------------------------

$_ENV['timesheet'] = [];

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

            $_ENV['timesheet'][$k][] = [
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
