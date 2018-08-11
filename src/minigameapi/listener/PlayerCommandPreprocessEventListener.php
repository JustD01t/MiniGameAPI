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
		if (substr($event->getMessage(),0,1) !== '/') return;
		$event->setCancelled(!$game->isAllowedCommand(substr(implode('.', array_map("stripslashes", str_getcsv($event->getMessage(), " "))),1)));
		if($event->isCancelled()) {
			$event->getPlayer()->sendMessage($game->getPrefix() . $this->gameManager->getMiniGameApi()->getLanguage()->translateString('commandMessage.commandNotAllowedInGame'));
			$event->getPlayer()->sendMessage($this->gameManager->getMiniGameApi()->getLanguage()->translateString('commandMessage.quitFirst', [$this->gameManager->getMiniGameApi()->getBaseLang()->translateString('command.quit.usage',[$this->gameManager->getMiniGameApi()->getLanguage()->translateString('command.quit')]), $this->gameManager->getMiniGameApi()->getBaseLang()->translateString('command.miniGameApi.quit.usage',[$this->gameManager->getMiniGameApi()->getBaseLang()->translateString('command.miniGameApi'), $this->gameManager->getMiniGameApi()->getBaseLang()->translateString('command.miniGameApi.quit')])]));
		}
	}
}
