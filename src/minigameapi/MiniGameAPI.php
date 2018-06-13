<?php

namespace minigameapi;

use pocketmine\plugin\PluginBase;

class MiniGameAPI extends PluginManager {
  private $gameManager;
  private $gameId;
  public function setGameId(int $id){
    $this->gameId = $id;
  }
  static function getInstance(); //TODO 구현
  public function getGameManager() : GameManager{
    return $this->gameManager;
  }
}
