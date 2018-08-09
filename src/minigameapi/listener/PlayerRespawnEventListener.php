<?php
namespace minigameapi\listener;

use minigameapi\event\MiniGamePlayerJoinEvent;
use minigameapi\MiniGameApi;
use minigameapi\PlayerData;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class PlayerRespawnEventListener implements Listener {
    private $miniGameApi;
    public function __construct(MiniGameApi $miniGameApi) {
        $this->miniGameApi = $miniGameApi;
    }
    public function onPlayerRespawn(PlayerRespawnEvent $event) {
        if (!is_null($this->miniGameApi->getGameManager()->getJoinedGame($event->getPlayer()))) return;
        $this->miniGameApi->getScheduler()->scheduleDelayedTask(new class($this->miniGameApi, $event->getPlayer()) extends Task {
            public $miniGameApi;
            public $player;
            public function __construct(MiniGameApi $miniGameApi, Player $player) {
                $this->miniGameApi = $miniGameApi;
                $this->player = $player;
            }
            public function onRun(int $currentTick) {
                $this->miniGameApi->getPlayerData($this->player->getName())->restore($this->player);
            }
        }, 5); //TODO rewrite
    }
}