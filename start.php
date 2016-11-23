<?php
require 'autoloader.php';
use Shared\Utils as Utils;

if (!isset($argv[1])) {
	die('Invalid no of args');
}
$url = $argv[1];
$argument = explode("/", $url);
$folder = array_pop($argument);
$root = './series/';
$episode = $root . $folder . '/episode.txt';

exec('mkdir -p ' . $root . $folder);
exec('mkdir -p ' . $root . $folder . '/episodes');
exec('touch ' . $episode);

$start = isset($argv[2]) ? Utils::path($argv[2]) : true;
$end = isset($argv[3]) ? Utils::path($argv[3]) : true;

$arr = [
	'start' => $start,
	'end' => $end
];
file_put_contents($episode, json_encode($arr));