<?php

// Set the cache limiter to 'private'
// @see https://webdock.io/en/docs/webdock-control-panel/optimizing-performance/setting-cache-control-headers-common-content-types-nginx-and-apache
session_cache_limiter('private');

// Set the session timeout for 1 day
$timeout = 24 * 60 * 60;

// Server should keep session data for AT LEAST $timeout seconds
ini_set('session.gc_maxlifetime', $timeout);

// Each client should remember their session id for EXACTLY $timeout seconds
ini_set('session.cookie_lifetime', $timeout);
session_set_cookie_params($timeout);

session_start();

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the form submission: store form data in session
    $_SESSION["form_data"] = $_POST;

    // Redirect to the same page to avoid form resubmission
    header("HTTP/1.1 303 See Other");
    header("Location: " . $_SERVER["REQUEST_URI"]);
    exit();
}
