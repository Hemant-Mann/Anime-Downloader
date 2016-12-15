<?php
require 'autoloader.php';
use \Curl\Curl as Curl;

$folder = KissAnime\Utils::getAnimeName($argv[1]);
$root = dirname(__FILE__) . "/series/{$folder}";

KissAnime\Crawler::init($root, [
	'url' => $folder, 'new' => true,
	'quality' => '480p'		// Available Options (360p, 480p, 720p, 1080p). If specified quality not available then the smallest quality will be downloaded
]);
?>