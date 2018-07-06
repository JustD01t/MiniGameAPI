<?php

namespace minigameapi;

use pocketmine\plugin\PluginBase;
use pocketmine\lang\BaseLang;

class MiniGameApi extends PluginBase {
	private $gameManager;
	private static $instance = null;
	private $baseLang;
	public function onLoad() {
		self::$instance = $this;
	}
	public static function getInstance() : MiniGameApi{
		return self::$instance;
	}
	public function onEnable() {
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder() . 'playerData');
		$this->saveDefaultConfig();
		$this->gameManager = new GameManager($this);
		$this->getScheduler()->scheduleRepeatingTask(new GameManagerUpdateTask($this->getGameManager()), 0);
		$this->baseLang = new BaseLang($this->getConfig()->get('language') == 'auto' ? $this->getServer()->getProperty("settings.language") : $this->getConfig()->get('language'),$this->getDataFolder() . 'lang' . DIRECTORY_SEPARATOR);
	}
	public function getBaseLang() : BaseLang{
		return $this->baseLang;
	}
	public function getGameManager() : GameManager{
  		return $this->gameManager;
	}
	public function setPlayerData(string $playerName, PlayerData $playerData) {
		file_put_contents($this->getDataFolder() . strtolower($playerName) . '.json', json_encode($playerData));
	}
	public function getPlayerData(string $playerName) : ?PlayerData {
		if(!file_exists($this->getDataFoler() . strtolower($playerName) . '.json')) return;
		return json_decode(file_get_contents($this->getDataFoler() . strtolower($playerName) . '.json'));
	}
}
