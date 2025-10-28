<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Jira Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Jira integration using php-jira-rest-client.
    | These settings are used to connect to your Jira instance.
    |
    */

    'host' => env('JIRA_HOST'),
    'user' => env('JIRA_USER'),
    'password' => env('JIRA_PASS'),
];
