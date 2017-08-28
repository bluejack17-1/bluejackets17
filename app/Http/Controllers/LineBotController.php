<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\FollowEvent;
use LINE\LINEBot\Event\JoinEvent;
use LINE\LINEBot\Event\LeaveEvent;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\UnfollowEvent;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;

use App\Http\Controllers\BotEventHandlers\MessageEventHandler;
use App\Http\Controllers\BotEventHandlers\FollowEventHandler;
use App\Http\Controllers\BotEventHandlers\UnfollowEventHandler;
use App\Http\Controllers\BotEventHandlers\JoinEventHandler;
use App\Http\Controllers\BotEventHandlers\LeaveEventHandler;

class LineBotController extends Controller {
	public static function routes($app) {
		$app->post('/webhook', 'LineBotController@hook');
	}
	public function hook(Request $request) {
		$bot = app(\App\LineBot::class)->bot();
		$signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);

		if (empty($signature)) {
			return response('Bad request', 400);
		}
		
		try {
			$events = $bot->parseEventRequest($request->getContent(), $signature);
		} catch (InvalidSignatureException $e) {
			return response('Invalid signature', 400);
		} catch (InvalidEventRequestException $e) {
			return response('Invalid event request', 401);
		}
		
		foreach ($events as $event) {
			if ($event instanceof MessageEvent) {
				MessageEventHandler::handle($bot, $event);
			}
			else if ($event instanceof FollowEvent) {
				FollowEventHandler::handle($bot, $event);
			}
			else if ($event instanceof UnfollowEvent) {
				UnfollowEventHandler::handle($bot, $event);
			}
			else if ($event instanceof JoinEvent) {
				JoinEventHandler::handle($bot, $event);
			}
			else if ($event instanceof LeaveEvent) {
				LeaveEventHandler::handle($bot, $event);
			}
		}

		return response('OK', 200);
	}
}