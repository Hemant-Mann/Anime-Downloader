<?php
require 'autoloader.php';
use \Curl\Curl as Curl;

$url = parse_url($argv[1])['path'];
$argument = explode("/", $url);
$folder = array_pop($argument);
$root = dirname(__FILE__) . '/series/' . $folder;

KissAnime\Crawler::init($root, ['url' => $url, 'quality' => '480p']);
?>