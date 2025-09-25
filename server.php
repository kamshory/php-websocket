<?php

require_once __DIR__ . '/classes/ChatServer.php';
require_once __DIR__ . '/classes/Utility.php';
require_once __DIR__ . '/classes/ChatClient.php';

$host = '0.0.0.0'; //host
$port = 8889; //port

$server = new ChatServer($host, $port);
$server->maxDataSize = 10000;
$server->run();

