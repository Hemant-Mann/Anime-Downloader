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
			if ($episodeInfo->start === true || $l->href == $episodeInfo->start) {
				$push = true;
			}
			// var_dump(sprintf("Episode: %d, link: %s", $i++, $l));

			if ($push) {
				$crawled[] = $l;
			}

			if ($l->href === $episodeInfo->end) {
				$push = false;
			}
		}
		return $crawled;
	}

	public static function getDownloadLink($crawled, $folderConfig, $quality = "480p") {
		$downloadLinks = [];
		foreach ($crawled as $link) {
			$body = Crawler::crawl($link->href, ['body' => true]);

			$downloadURL = Crawler::downloadURL($body, $quality);
			$title = $link->title;
			if (is_null($downloadURL)) {
				printf("Null episode: %s\n", $title);
			} else {
				printf("Crawled episode: %s\n", $title);
			}
			$downloadLinks[] = $downloadURL;

			file_put_contents($folderConfig->downloadFile, $downloadURL . "\r\n", FILE_APPEND);
			file_put_contents($folderConfig->axelList, sprintf("%s;%s", $downloadURL, \Shared\Utils::goodFileName($title)) . "\r\n", FILE_APPEND);
		}
		copy($folderConfig->downloadFile, $folderConfig->finalList);
		return $downloadLinks;
	}
}
