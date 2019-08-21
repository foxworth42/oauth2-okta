<?php

require __DIR__ . '/../vendor/autoload.php';

use Foxworth42\OAuth2\Client\Provider\Okta;

// Replace these with your token settings
$clientId     = 'your-client-id';
$clientSecret = 'your-client-secret';

$issuer = 'https://demo.okta.com/oauth2/default';

// Change this if you are not using the built-in PHP server
$redirectUri  = 'http://localhost:8080/';

// Start the session
session_start();

// Initialize the provider
$provider = new Okta(compact('clientId', 'clientSecret', 'redirectUri', 'issuer'));

// No HTML for demo, prevents any attempt at XSS
header('Content-Type', 'text/plain');

return $provider;

