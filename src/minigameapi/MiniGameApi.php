<?php

namespace minigameapi;

use pocketmine\plugin\PluginBase;

class MiniGameApi extends PluginManager {
	private $gameManager;
	private $gameId;
	private static $instance = null;
	public function onLoad() {
		self::$instance = $this;
	}
	public static function getInstance() : MiniGameApi{
		return self::$instance;
	}
	public function onEnable() {
		$this->gameManager = new GameManager($this);
		$this->getScheduler()->scheduleRepeatingTask(new GameManagerUpdateTask($this->getGameManager));
	}
	public function getGameManager() : GameManager{
  		return $this->gameManager;
	}
}
