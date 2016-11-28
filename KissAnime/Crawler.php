<?php
namespace KissAnime;
use \Curl\Curl;

class Crawler {
	public static $domain = "http://kissanime.to";

	const QUALITY_XPATH = "//*[@id='selectQuality']";
	const LINKS_XPATH = "//*[@id='leftside']/div[2]/div[2]/div[2]/table";

	protected static function _getConf() {
		$folder = dirname(__FILE__);
		var_dump($folder);
		die();
		$file = file_get_contents($folder . "/config.json");
		$content = json_decode($file);

		return $content;
	}

	public static function init($opts = []) {
		$url = $opts['url']; $new = $opts['new'] ?? false;
		$quality = $opts['quality'] ?? '480p';
		// get links
		$folderConfig = new Config($opts['root']);

		$episodeInfo = \Shared\Utils::getJson($folderConfig->episodeInfo);
		$downloadFile = $folderConfig->downloadFile;
		$episodeList = $folderConfig->episodeList;

		// check if we need new episodes or older will do
		$links = self::episodeList($episodeList, $url, $new);

		// check which links are to be crawled
		$crawled = Utils::linksToBeCrawled($links, $episodeInfo);

		\Shared\Utils::initFile([$downloadFile, $folderConfig->finalList]);
		// now crawl these links to find download link foreach episode
		Utils::getDownloadLink($crawled, $downloadFile, $quality);

		\Shared\Utils::putStarting($last, $downloadFile);
	}

	public static function _episodeList($url) {
		$xPath = self::crawl($url);
		$el = $xPath->query(self::LINKS_XPATH);
		$el = $el->item(0);

		$nodes = $el->childNodes;	// TR's
		$i = 1; $links = [];
		foreach ($nodes as $n) {
			if ($i++ < 3) continue;
			
			$cells = $n->childNodes;	// TD's
			foreach ($cells as $child) {
				// find the td containing link
				if (!\Shared\Utils::isTag($child, 'td')) {
					continue;
				}
				$a = $child->childNodes;
				foreach ($a as $value) {
					if (!\Shared\Utils::isTag($value, 'td')) {
						continue;
					}

					$links[] = $value->getAttribute('href');
				}
			}
		}
		return $links;
	}

	/**
	 * Get List of episodes for an anime
	 */
	public static function episodeList($linksFile, $url, $new = false) {
		$links = [];

		if (file_exists($linksFile) && $new !== false) {
			$f = file_get_contents($linksFile);
			$links = json_decode($f);
		}

		if (count($links) == 0) {
			$links = self::_episodeList($url);

			$links = array_reverse($links);
			file_put_contents($linksFile, json_encode($links));
		} else {
			$f = file_get_contents($linksFile);
			$links = json_decode($f);	
		}
		return $links;
	}

	public static function crawl($url) {
		$url = self::$domain . $url; $conf = self::_getConf();
		$curl = new Curl();
		$curl->setHeader('Cookie', $conf->cookie);
		$curl->setHeader('User-Agent', $conf->ua);
		$curl->setHeader('Referer', self::$domain);
		
		$curl->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
		$curl->setHeader('Accept-Language', 'en-US,en;q=0.5');

		$curl->get($url);
		$body = $curl->response;
		$curl->close();
		return self::xPath($body);
	}

	public static function xPath($body) {
		$xmlPageDom = new \DomDocument(); // Instantiating a new DomDocument object
		@$xmlPageDom->loadHTML($body); // Loading the HTML from downloaded page
		$xPath = new \DOMXPath($xmlPageDom);
		return $xPath;
	}

	public static function downloadURL($xPath, $quality = "720p") {
		$el = $xPath->query(self::QUALITY_XPATH);
		$el = $el->item(0)->childNodes;

		$url = null; $found = [];
		foreach ($el as $opt) {
			$inner = $opt->nodeValue;
			$inner = preg_replace('/\s+/', '', $inner);

			$url = base64_decode($opt->getAttribute('value'));
			$found[$inner] = $url;

			if ($inner == $quality) break;
		}

		if (array_key_exists($quality, $found)) {
			$url = $found[$quality];
		} else {
			$url = $found['360p'] ?? $found['480p'] ?? $found['720p'] ?? null;
		}
		return $url;
	}
}
