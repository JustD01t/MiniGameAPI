<?php
namespace minigameapi\event;

use minigameapi\Game;
use pocketmine\Player;

class MiniGamePlayerRemoveEvent extends MiniGameEvent {
	private $player;
	public function __construct(Game $game, Player $player) {
		$this->player = $player;
		parent::__construct($game);
	}
	public function getPlayer() {
		return $this->player;
	}
}