<?php

$_ENV['timestamp'] = md5('2024-05-19 00:00');
$_ENV['protocol'] = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
$_ENV['base_url'] = $_ENV['protocol'] . '://' . $_SERVER['HTTP_HOST'] . '/tempo';

$_ENV['free_days'] = [ // TODO
    '2024-04-22',
    '2024-04-29',
    '2024-04-30',
    '2024-05-01',
    '2024-05-02',
    '2024-05-03',
    '2024-05-06',
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
    'English' => [
        'ro' => 'Engleză',
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
    'Expand description' => [
        'ro' => 'Extinde descriere',
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

$beginDateTime = new DateTime('yesterday');
$endDateTime = new DateTime();

switch ($beginDateTime->format('w')) {
    case '6':
        // if yesterday is Saturday, change the $beginDateTime to Friday
        $beginDateTime->sub(new DateInterval('P1D'));
        break;
    case '0':
        // if yesterday is Sunday, change the $beginDateTime to Friday
        $beginDateTime->sub(new DateInterval('P2D'));
        break;
}

$datepicker = $_SESSION['form_data']['datepicker'] ?? false;
if ($datepicker) {
    list($begin, $end) = explode(' - ', $datepicker);
    $beginDateTime = new DateTime($begin);
    $endDateTime = new DateTime($end);
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
