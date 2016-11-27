<?php
require 'autoloader.php';
require 'kissanime.php';
use \Curl\Curl as Curl;

$url = parse_url($argv[1])['path'];
$argument = explode("/", $url);
$folder = array_pop($argument);
$root = './series/';

$linksFile = $root . $folder . '/list.txt';
$links = [];
$last = file_get_contents($root . $folder . '/episode.txt');
$last = json_decode($last);

if (file_exists($linksFile)) {
	$f = file_get_contents($linksFile);
	$links = json_decode($f);
}

if (count($links) == 0) {
	$links = Kissanime::links($url);

	$links = array_reverse($links);
	file_put_contents($linksFile, json_encode($links));
} else {
	$f = file_get_contents($linksFile);
	$links = json_decode($f);	
}

$crawled = []; $push = false;
foreach ($links as $l) {
	if ($last->start === true || $l == $last->start) {
		$push = true;
	}

	if ($push) {
		$crawled[] = $l;
	}

	if ($last->end === true || $l == $last->end) {
		$push = false;
	}
}

// these are to be downloaded
$downloadFile = $root . $folder . '/downloadlist.txt';
Shared\Utils::initFile([$downloadFile, "./list.txt"]);

foreach ($crawled as $c) {
	$xPath = Kissanime::crawl($c);

	$downloadURL = Kissanime::downloadURL($xPath, "480p");
	$episode = Shared\Utils::episodeName($c);
	if (is_null($downloadURL)) {
		var_dump('Null for episode: '. $episode);
	}

	$downloadList[] = $downloadURL;
	file_put_contents($downloadFile, $downloadURL . "\r\n", FILE_APPEND);

}
preg_match('/Episode-([0-9]+)/', $last->start, $matches);

if (isset($matches[1])) {
	$startingCount = (int) $matches[1];
} else {
	$startingCount = 1;
}
file_put_contents("./start.txt", $startingCount);
copy($downloadFile, './list.txt');
?>