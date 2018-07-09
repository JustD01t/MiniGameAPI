<?php

namespace minigameapi;

use minigameapi\command\MiniGameApiCommand;
use minigameapi\command\QuitCommand;
use minigameapi\listener\PlayerCommandPreprocessEventListener;
use minigameapi\listener\PlayerQuitEventListener;
use minigameapi\task\GameManagerUpdateTask;
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
        @mkdir($this->getDataFolder() . 'lang');
		$this->saveDefaultConfig();
        foreach ($this->getResources() as $resource) {
            if(substr($resource->getFilename(),-3) == 'ini') file_put_contents($this->getDataFolder() . 'lang' . DIRECTORY_SEPARATOR . $resource->getFilename(),file_get_contents($resource->getPathname()));
        }
		$this->gameManager = new GameManager($this);
		$this->getScheduler()->scheduleRepeatingTask(new GameManagerUpdateTask($this->getGameManager(),$this->getConfig()->get('ticks-per-update-cycle', 20)), $this->getConfig()->get('ticks-per-update-cycle', 20));
		$this->getServer()->getPluginManager()->registerEvents(new PlayerQuitEventListener($this->getGameManager()), $this);
		$this->getServer()->getPluginManager()->registerEvents(new PlayerCommandPreprocessEventListener($this->getGameManager()), $this);
		$this->baseLang = new BaseLang($this->getConfig()->get('language', 'auto') == 'auto' ? $this->getServer()->getProperty("settings.language") : $this->getConfig()->get('language'),$this->getDataFolder() . 'lang' . DIRECTORY_SEPARATOR);
		$this->getServer()->getCommandMap()->register('minigameapi', new MiniGameApiCommand($this));
		$this->getServer()->getCommandMap()->register('quit', new QuitCommand($this));
	}
	public function getBaseLang() : BaseLang{
		return $this->baseLang;
	}
	public function getLogoImagePath() : string {
	    return $this->getDataFolder() . 'logo.png';
    }
	public function getGameManager() : GameManager{
  		return $this->gameManager;
	}
	public function setPlayerData(string $playerName, PlayerData $playerData) {
		serialize($this->getDataFolder() . strtolower($playerName) . '.dat', json_encode($playerData));
	}
	public function getPlayerData(string $playerName) : ?PlayerData {
		if(!file_exists($this->getDataFolder() . strtolower($playerName) . '.json')) return null;
		return unserialize(file_get_contents($this->getDataFolder() . strtolower($playerName) . '.dat'));
	}
}
