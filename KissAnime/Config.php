<?php
namespace KissAnime;

class Config {
	public $episodeInfo;
	public $downloadFile;
	public $episodeList;
	public $finalList;
	public $axelList;

	public function __construct($root) {
		$this->episodeInfo = $root . "/episode.txt";
		$this->downloadFile = $root . "/downloadList.txt";
		$this->episodeList = $root . "/list.txt";
		$this->finalList = dirname(dirname(__FILE__)) . "/list.txt";
		$this->axelList = dirname(dirname(__FILE__)) . "/axel-list.txt";
	}
}