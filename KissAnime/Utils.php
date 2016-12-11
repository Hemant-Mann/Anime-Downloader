<?php
namespace KissAnime;

class Utils {
	public static function getRoot() {
		$dir = dirname(__FILE__);
		return dirname($dir);
	}

	public static function getAnimeName($url) {
		$path = parse_url($url)['path'];
		$path = rtrim($url, "/");
		$argument = explode("/", $path);

		return array_pop($argument);
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
		$folder = self::getAnimeName($url);
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
		$i = 1;
		foreach ($links as $l) {
			if ($episodeInfo->start === true || $l == $episodeInfo->start) {
				$push = true;
			}
			// var_dump(sprintf("Episode: %d, link: %s", $i++, $l));

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
		$downloadLinks = []; $i = 1;
		foreach ($crawled as $c) {
			$body = Crawler::crawl($c, ['body' => true]);

			$downloadURL = Crawler::downloadURL($body, $quality);
			$episode = self::episodeName($c);
			if (is_null($downloadURL)) {
				printf("Null episode: %d\n", $i++);
			} else {
				printf("Crawled episode: %d, name: %s\n", $i++, $episode);
			}
			$downloadLinks[] = $downloadURL;

			file_put_contents($downloadFile, $downloadURL . "\r\n", FILE_APPEND);
		}
		return $downloadLinks;
	}
}
