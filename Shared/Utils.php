<?php
namespace Shared;

class Utils {
	public static function path($url) {
		$parsed = parse_url($url);

		return $parsed['path'] . '?' . $parsed['query'];
	}

	protected static function _initFile($file) {
		@unlink($file);
		touch($file);
	}

	public static function getJson($file) {
		$file = file_get_contents($file);
		$content = json_decode($file);

		return $content;
	}

	public static function initFile($file) {
		if (is_array($file)) {
			foreach ($file as $f) {
				self::_initFile($f);
			}
		} else {
			self::_initFile($file);
		}
	}

	public static function putStarting($last, $downloadFile) {
		preg_match('/Episode-([0-9]+)|\/([0-9]+)-/i', $last->start, $matches);

		if (isset($matches[1]) && $matches[1]) {
			$startingCount = (int) $matches[1];
		} else if (isset($matches[2]) && $matches[2]) {
			$startingCount = (int) $matches[2];
		} else {
			$startingCount = 1;
		}

		$root = dirname(dirname(__FILE__));
		file_put_contents("{$root}/start.txt", $startingCount);
		copy($downloadFile, "{$root}/list.txt");
	}

	public static function isTag($node, $name) {
		if (property_exists($node, 'tagName') && $node->tagName == $name) {
			return true;
		}
		return false;
	}
}