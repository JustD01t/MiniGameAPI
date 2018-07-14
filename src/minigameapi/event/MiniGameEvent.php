<?php
namespace minigameapi\event;
use minigameapi\Game;
use pocketmine\event\Event;
use pocketmine\event\plugin\PluginEvent;

abstract class MiniGameEvent extends PluginEvent {
	private $game;
	public function __construct(Game $game) {
	    parent::__construct($game->getMiniGameApi());
		$this->game = $game;
	}
	public function getGame() : Game {
		return $this->game;
	}
}