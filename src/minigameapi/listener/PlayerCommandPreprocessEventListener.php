<?php
namespace minigameapi\listener;
use minigameapi\GameManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class PlayerCommandPreprocessEventListener implements Listener {
	private $gameManager;
	public function __construct(GameManager $gameManager) {
		$this->gameManager = $gameManager;
	}
	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) {
		if($event->isCancelled()) return;
		$game = $this->gameManager->getJoinedGame($event->getPlayer());
		if(is_null($game)) return;
		$event->setCancelled($game->isAllowedCommand(implode('.', array_map("stripslashes", str_getcsv($event->getMessage(), " ")))));
		if(!$game->isAllowedCommand(implode('.', array_map("stripslashes", str_getcsv($event->getMessage(), " "))))) {
			$event->getPlayer()->sendMessage($game->getPrefix() . $this->gameManager->getMiniGameApi()->getBaseLang()->translateString('commandMessage.commandNotAllowedInGame'));
			$event->getPlayer()->sendMessage($this->gameManager->getMiniGameApi()->getBaseLang()->translateString('commandMessage.quitFirst', [$this->gameManager->getMiniGameApi()->getBaseLang()->translateString('command.quit.usage',[$this->gameManager->getMiniGameApi()->getBaseLang()->translateString('command.quit')]), $this->gameManager->getMiniGameApi()->getBaseLang()->translateString('command.miniGameApi.quit.usage',[$this->gameManager->getMiniGameApi()->getBaseLang()->translateString('command.miniGameApi'), $this->gameManager->getMiniGameApi()->getBaseLang()->translateString('command.miniGameApi.quit')])]));
		}
	}
}