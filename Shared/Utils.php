<?php
namespace Shared;

class Utils {
	public static function path($url) {
		$parsed = parse_url($url);

		return $parsed['path'] . '?' . $parsed['query'];
	}

	protected static function _initFile($file) {
		@unlink($file); touch($file);
	}

	public static function goodFileName($name) {
		return str_replace([" ", "(", ")"], "_", $name);
	}

	/**
	 * Decodes the file containing json data
	 * @param  string $file Full path to the file
	 * @return object       \stdClass
	 */
	public static function getJson($file) {
		$file = file_get_contents($file);
		$content = json_decode($file);

		return $content;
	}

	/**
	 * Makes the file (if not exists) and removes all the content in the file
	 * @param  string|array $file Path to the file
	 */
	public static function initFile($file) {
		if (is_array($file)) {
			foreach ($file as $f) {
				self::_initFile($f);
			}
		} else {
			self::_initFile($file);
		}
	}

	/**
	 * Function to assign default value if array key is not isset
	 * @param  array $arr
	 * @param  string $key     Name of the key in the array
	 * @param  mixed $default Default value to be returned in case of failure
	 * @return mixed
	 */
	public static function defaultVal($arr, $key, $default = null) {
		if (isset($arr[$key])) {
			return $arr[$key];
		}
		return $default;
	}

	public static function isTag($node, $name) {
		if (property_exists($node, 'tagName') && $node->tagName == $name) {
			return true;
		}
		return false;
	}

	public static function hasClass($node, $k) {
		if (method_exists($node, 'getAttribute') && $node->getAttribute('class') === $k) {
			return true;
		}
		return false;
	}
}