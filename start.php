<?php
require 'autoloader.php';
use Shared\Utils as Utils;

if (!isset($argv[1])) {
	die('Invalid no of args');
}
$url = $argv[1];

KissAnime\Utils::start($url);