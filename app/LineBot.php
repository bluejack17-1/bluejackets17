<?php

namespace App;

class LineBot {
	private $channelSecret;
	private $channelToken;
	private $bot;

	public function __construct() {
		$this->channelToken = getenv('LINE_CHANNEL_TOKEN');
		$this->channelSecret = getenv('LINE_CHANNEL_SECRET');
		$this->bot = new \LINE\LINEBot(new \LINE\LINEBot\HTTPClient\CurlHTTPClient($this->channelToken), [
			'channelSecret' => $this->channelSecret,
		]);
	}
	public function bot() {
		return $this->bot;
	}
}