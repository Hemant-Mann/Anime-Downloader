<?php
namespace KissAnime;
use \Curl\Curl;
use Shared\Utils as Util;

class Crawler {
	public static $domain = "http://kissanime.io";

	const QUALITY_REGEX = "/load_player\((.*)\);/";
	const LINKS_XPATH = '//*[@id="leftside"]/div[2]/div[2]/div[2]/div[2]';

	/**
	 * Makes the path to anime from anime name
	 * @param  string $folder Anime name
	 * @return string
	 */
	public static function animeUrl($folder) {
		return sprintf("/Anime/%s", $folder);
	}

	/**
	 * Get the config file containing cookies
	 * @return object \stdClass containing properties - ['cookie', 'ua']
	 */
	protected static function _getConf() {
		$folder = dirname(__FILE__);
		return Util::getJson("{$folder}/config.json");
	}

	public static function init($folder, $opts = []) {
		$url = $opts['url'];
		$new = Util::defaultVal($opts, 'new', false);
		$quality = Util::defaultVal($opts, 'quality', '480p');
		
		// get directory structure
		$folderConfig = new Config($folder);

		$episodeInfo = Util::getJson($folderConfig->episodeInfo);

		// check if we need new episodes or older will do
		$links = self::episodeList($folderConfig->episodeList, $url, $new);

		// check which links are to be crawled
		$crawled = Utils::linksToBeCrawled($links, $episodeInfo);

		Util::initFile([$folderConfig->downloadFile, $folderConfig->finalList, $folderConfig->axelList]);
		// now crawl these links to find download link foreach episode
		Utils::getDownloadLink($crawled, $folderConfig, $quality);
	}

	/**
	 * Return the Href and link title from the table element
	 * @return array     Assoc Array
	 */
	protected static function _linkNode($el) {
		$nodes = $el->childNodes->item(1)->childNodes;
		$h3 = (object) ['childNodes' => []];
		foreach ($nodes as $n) {
			if (Util::isTag($n, 'h3')) {
				$h3 = $n;
				break;
			}
		}
		$nodes = $h3->childNodes;
		$link= ['href' => '', 'title' => ''];
		foreach ($nodes as $n) {
			if (!Util::isTag($n, 'a')) {
				continue;
			} else {
				$url = "http:" . $n->getAttribute('href');
				$parsed = parse_url($url);
				$link['href'] = sprintf("%s?%s", $parsed['path'], $parsed['query']);
				$link['title'] = $n->nodeValue;
			}
		}
		return $link;
	}

	/**
	 * Traverses the DOM tree to find the element containing the links
	 * !!!!!!! NEED TO MODIFY THIS FUNCTION IF WEBSITE STRUCTURE CHANGES !!!!!!!!
	 * @param  string $url Path to anime
	 * @return array      Array of links
	 */
	public static function _episodeList($url) {
		$xPath = self::crawl($url);
		$el = $xPath->query(self::LINKS_XPATH);
		$el = $el->item(0);

		$nodes = $el->childNodes;	// TR's
		$i = 1; $links = [];
		foreach ($nodes as $n) {
			if (!Util::isTag($n, 'div') || Util::hasClass($n, 'head')) continue;
			$links[] = self::_linkNode($n);
		}
		return $links;
	}

	/**
	 * Get List of episodes for an anime
	 * @param string $linksFile Full path to the links file
	 * @param string $anime Name of the anime
	 */
	public static function episodeList($linksFile, $anime, $new = false) {
		$links = [];

		if (file_exists($linksFile) && $new !== false) {
			$f = file_get_contents($linksFile);
			$links = json_decode($f);
		}

		if (count($links) == 0) {
			$links = self::_episodeList(self::animeUrl($anime));

			$links = array_reverse($links);
			file_put_contents($linksFile, json_encode($links));
		} else {
			$f = file_get_contents($linksFile);
			$links = json_decode($f);	
		}
		return $links;
	}

	public static function crawl($url, $opts = []) {
		if (isset($opts['fullUrl'])) {
			$url = $url;
		} else {
			$url = self::$domain . $url;	
		}
		$conf = self::_getConf();
		$curl = new Curl();
		$curl->setHeader('Cookie', $conf->cookie);
		$curl->setHeader('User-Agent', $conf->ua);
		$curl->setHeader('Referer', self::$domain);
		
		$curl->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
		$curl->setHeader('Accept-Language', 'en-US,en;q=0.5');

		$curl->get($url);
		$body = $curl->response;
		$curl->close();

		if (isset($opts['body'])) {
			return $body;
		}
		return self::xPath($body);
		
	}

	public static function xPath($body) {
		$xmlPageDom = new \DomDocument(); // Instantiating a new DomDocument object
		@$xmlPageDom->loadHTML($body); // Loading the HTML from downloaded page
		$xPath = new \DOMXPath($xmlPageDom);
		return $xPath;
	}

	/**
	 * Get download url from the body by finding the link containing url to videos
	 * @param  string $body    HTML of the page
	 * @param  string $quality 1080p|720p|480p|360p
	 * @return string|null          Desired quality URL
	 */
	public static function downloadURL($body, $quality = "720p") {
		$url = null;
		preg_match(self::QUALITY_REGEX, $body, $matches);

		if (!isset($matches[1])) {
			return $url;
		}
		$requestUri = "http:" . str_replace("'", "", $matches[1]);
		$json = self::crawl($requestUri, [
			'body' => true, 'fullUrl' => true
		]);

		if (!is_object($json) || !property_exists($json, 'playlist')) {
			return $url;
		}
		$sources = @$json->playlist[0]->sources;
		$found = [];
		foreach ($sources as $s) {
			$q = $s->label;
			$found[$q] = $s->file;
		}

		if (array_key_exists($quality, $found)) {
			$url = $found[$quality];
		} else {
			foreach (['720p', '480p', '360p'] as $value) {
				$url = Util::defaultVal($found, $value, null);
			}
		}
		return $url;
	}
}
