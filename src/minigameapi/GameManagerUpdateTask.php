<?php
namespace minigameapi;

use pocketmine\scheduler\Task;

class GameManagerUpdateTask extends Task {
	private $gameManager;
	public function __construct(GameManager $gameManager) {
		$this->gameManager = $gameManager;
	}
	public function onRun(int $currentTick) {
		$this->gameManager->update();
	}
}
