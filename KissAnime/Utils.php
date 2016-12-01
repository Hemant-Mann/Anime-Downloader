<?php
namespace KissAnime;

class Utils {
	public static function getRoot() {
		$dir = dirname(__FILE__);
		return dirname($dir);
	}

	public static function episodeName($url) {
		$url = "http://google.com" . $url;
		$parsed = parse_url($url);

		$path = $parsed['path'];
		$last = explode("/", $path);
		$last = array_pop($last);
		return $last;
	}

	public static function start($url) {
		$argument = explode("/", $url);
		$folder = array_pop($argument);
		$root = sprintf("%s/series/%s", self::getRoot(), $folder);
		$episode = "{$root}/episode.txt";

		exec("mkdir -p {$root}"); touch($episode);
		$argv = $GLOBALS['argv'];

		$start = isset($argv[2]) ? \Shared\Utils::path($argv[2]) : true;
		$end = isset($argv[3]) ? \Shared\Utils::path($argv[3]) : true;

		$arr = [ 'start' => $start, 'end' => $end ];
		file_put_contents($episode, json_encode($arr));		
	}

	public static function linksToBeCrawled($links, $episodeInfo) {
		$crawled = []; $push = false;
		foreach ($links as $l) {
			if ($episodeInfo->start === true || $l == $episodeInfo->start) {
				$push = true;
			}

			if ($push) {
				$crawled[] = $l;
			}

			if ($l === $episodeInfo->end) {
				$push = false;
			}
		}
		return $crawled;
	}

	public static function getDownloadLink($crawled, $downloadFile, $quality = "480p") {
		$downloadLinks = [];
		foreach ($crawled as $c) {
			$xPath = Crawler::crawl($c);

			$downloadURL = Crawler::downloadURL($xPath, $quality);
			$episode = self::episodeName($c);
			if (is_null($downloadURL)) {
				printf("Null episode: %s\n", $episode);
			}
			$downloadLinks[] = $downloadURL;

			file_put_contents($downloadFile, $downloadURL . "\r\n", FILE_APPEND);
			printf("Crawled episode %s\n", $episode);
		}
		return $downloadLinks;
	}
}
