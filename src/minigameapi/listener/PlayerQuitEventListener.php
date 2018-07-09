<?php
namespace minigameapi\listener;
use minigameapi\GameManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class PlayerQuitEventListener implements Listener {
	private $gameManager;
	public function __construct(GameManager $gameManager) {
		$this->gameManager = $gameManager;
	}
	public function onPlayerQuit(PlayerQuitEvent $playerQuitEvent) {
		$this->gameManager->removePlayer($playerQuitEvent->getPlayer());
	}
}