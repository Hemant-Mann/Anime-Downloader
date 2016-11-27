<?php
namespace Shared;

class Utils {
	public static function episodeName($url) {
		$url = "http://somedomain" . $url;
		$parsed = parse_url($url);

		$path = $parsed['path'];
		$last = explode("/", $path);
		$last = array_pop($last);
		return $last;
	}

	public static function path($url) {
		$parsed = parse_url($url);

		return $parsed['path'] . '?' . $parsed['query'];
	}

	protected static function _initFile($file) {
		@unlink($file);
		touch($file);
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
}