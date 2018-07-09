<?php
namespace minigameapi\listener;
use minigameapi\MiniGameApi;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class PlayerJoinEventListener implements Listener {
    private $miniGameApi;
    public function __construct(MiniGameApi $miniGameApi) {
        $this->miniGameApi = $miniGameApi;
    }
    public function onPlayerJoin(PlayerJoinEvent $event) {
        $data = $this->miniGameApi->getPlayerData($event->getPlayer()->getName());
        if(is_null($data)) return;
        $data->restore($event->getPlayer());
    }
}
