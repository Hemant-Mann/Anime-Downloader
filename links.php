<?php
require 'autoloader.php';
use \Curl\Curl as Curl;

$url = parse_url($argv[1])['path'];
$argument = explode("/", $url);
$folder = array_pop($argument);
$root = dirname(__FILE__) . '/series/' . $folder;

KissAnime\Crawler::init(['url' => $url, 'root' => $root, 'quality' => '480p']);

$last = file_get_contents($root . '/episode.txt');
$last = json_decode($last);

// these are to be downloaded
$downloadFile = $root . '/downloadlist.txt';
Shared\Utils::initFile([$downloadFile, "./list.txt"]);

$crawled = KissAnime\Utils::crawled($last, $root);
KissAnime\Utils::downloadList($crawled, $downloadFile);

Shared\Utils::putStarting($last, $downloadFile);
?>