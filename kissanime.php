<?php
use \Curl\Curl as Curl;

class Kissanime {
	public static $domain = "http://kissanime.to";

	public static $cookie = '__cfduid=d2183780ad53f8aa71df2c1505ecad2f21470975714; __atuvc=0%7C43%2C0%7C44%2C1%7C45%2C1%7C46%2C1%7C47; cf_clearance=0882228560fd93a72187a8b6f660f4cdba4294cd-1479837254-86400; idtz=14.139.251.107-348164476; __atuvs=583486660c479e21000';

	public static $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:48.0) Gecko/20100101 Firefox/48.0';

	public static function crawl($url) {
		$url = self::$domain . $url;
		$curl = new Curl();
		$curl->setHeader('Cookie', self::$cookie);
		$curl->setHeader('User-Agent', self::$ua);
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

	public static function downloadURL($xPath) {
		$el = $xPath->query("//*[@id='selectQuality']");
		$el = $el->item(0)->childNodes;

		$url = null; $found = [];
		foreach ($el as $opt) {
			$inner = $opt->nodeValue;
			$inner = preg_replace('/\s+/', '', $inner);

			if ($inner == "480p") {
				$url = base64_decode($opt->getAttribute('value'));
				$found['480p'] = $url;
				break;
			} else if ($inner == "720p") {
				$url = base64_decode($opt->getAttribute('value'));
				$found['720p'] = $url;
				break;
			}
		}

		if (array_key_exists('480p', $found)) {
			$url = $found['480p'];
		} else if (array_key_exists('720p', $found)) {
			$url = $found['720p'];
		}
		return $url;
	}
}