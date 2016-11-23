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
	$xPath = Kissanime::crawl($url);
	$el = $xPath->query("//*[@id='leftside']/div[2]/div[2]/div[2]/table");
	$el = $el->item(0);

	$nodes = $el->childNodes;	// TR's
	$i = 1;
	
	$links = [];
	foreach ($nodes as $n) {
		if ($i++ < 3) continue;
		
		$cells = $n->childNodes;	// TD's
		foreach ($cells as $child) {
			// find the td containing link
			if (!property_exists($child, 'tagName') || $child->tagName !== 'td') {
				continue;
			}
			$a = $child->childNodes;
			foreach ($a as $value) {
				if (!property_exists($value, 'tagName') || $value->tagName !== 'a') {
					continue;
				}

				$links[] = $value->getAttribute('href');
			}
		}
	}

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
file_put_contents($downloadFile, "\n");
foreach ($crawled as $c) {
	$xPath = Kissanime::crawl($c);

	$downloadURL = Kissanime::downloadURL($xPath);
	$episode = Shared\Utils::episodeName($c);
	if (is_null($downloadURL)) {
		var_dump('Null for episode: '. $episode);
	}

	$downloadList[] = $downloadURL;
	file_put_contents($downloadFile, $downloadURL . "\r\n", FILE_APPEND);

	// exec('wget -U "'.Kissanime::$ua.'" -O '. $root . $folder . '/episodes/' . $episode . '.mp4 ' . "'" . $downloadURL . "'");
	// exec('/usr/local/aria2/bin/aria2c -s 15 -o '. $root . $folder . '/episodes/' . $episode . '.mp4 ' . "'" . $downloadURL . "'");

}
preg_match('/Episode-([0-9]+)/', $last->start, $matches);

if (isset($matches[1])) {
	$startingCount = $matches[1];
} else {
	$startingCount = 0;
}
file_put_contents("./start.txt", $startingCount);
copy($downloadFile, './list.txt');
?>