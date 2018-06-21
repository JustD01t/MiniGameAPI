<?php
namespace minigameapi;

use pocketmine\scheduler\Task;

class GameManagerUpdateTask extends Task {
	private $gameManager;
	private $updateCycle;
	public function __construct(GameManager $gameManager, int $updateCycle) {
		$this->gameManager = $gameManager;
		$this->updateCycle = $updateCycle;
	}
	public function onRun(int $currentTick) {
		$this->gameManager->update($this->updateCycle);
	}
}
