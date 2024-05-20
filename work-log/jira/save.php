<?php

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/config.php';
require_once '../../vendor/autoload.php';

Unirest\Request::auth($_ENV['JIRA_API_USERNAME'], $_ENV['JIRA_API_PASSWORD']);

// ----------------------------------------------------------------------------

$a = [
    'id'                 => '',
    'issue_key'          => '',
    'description'        => '',
    'time_spent_seconds' => '',
    'started'            => '',
];

foreach ($a as $k => $v) {
    $a[$k] = isset($_POST[$k]) ? addslashes(rawurldecode($_POST[$k])) : '';
}

// ----------------------------------------------------------------------------

$url = $_ENV['JIRA_BASE_URL'] . '/rest/api/3/issue/' . $a['issue_key'] . '/worklog';

if (!empty($a['id'])) {
    $url .= ('/' . $a['id']);
}

$headers = array(
    'Accept' => 'application/json',
    'Content-Type' => 'application/json'
);

$body = <<<REQUESTBODY
{
    "comment": {
        "content": [
            {
                "content": [
                    {
                        "text": "${a['description']}",
                        "type": "text"
                    }
                ],
                "type": "paragraph"
            }
        ],
        "type": "doc",
        "version": 1
    },
    "started": "${a['started']}",
    "timeSpentSeconds": ${a['time_spent_seconds']}
}
REQUESTBODY;

usleep(300 * 1000);

if (empty($a['id'])) {
    $response = Unirest\Request::post($url, $headers, $body);
} else {
    $response = Unirest\Request::put($url, $headers, $body);
}

if ($responseBodyId = $response->body->id) {
    echo '{"id": "' . $responseBodyId . '", "status": "success", "message": "Record successfully updated in JIRA."}';
} else {
    echo '{"status": "error", "message": "Issue does not exist or you do not have permission to see it."}';
}
