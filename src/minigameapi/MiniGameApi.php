<?php

namespace minigameapi;

use minigameapi\command\MiniGameApiCommand;
use minigameapi\command\QuitCommand;
use minigameapi\listener\PlayerCommandPreprocessEventListener;
use minigameapi\listener\PlayerJoinEventListener;
use minigameapi\listener\PlayerQuitEventListener;
use minigameapi\listener\PlayerRespawnEventListener;
use minigameapi\task\GameManagerUpdateTask;
use pocketmine\lang\Language;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class MiniGameApi extends PluginBase {
	private $gameManager;
	private static $instance = null;
	private $baseLang;
	public function onLoad() {
		self::$instance = $this;
		$this->gameManager = new GameManager($this);
	}
	public static function getInstance() : MiniGameApi{
		return self::$instance;
	}
	public function onEnable() : void{
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder() . 'playerData');
		@mkdir($this->getDataFolder() . 'lang');
		$this->saveDefaultConfig();
		foreach ($this->getResources() as $resource) {
			if(substr($resource->getFilename(),-3) == 'ini') file_put_contents($this->getDataFolder() . 'lang' . DIRECTORY_SEPARATOR . $resource->getFilename(),file_get_contents($resource->getPathname()));
            if(substr($resource->getFilename(),-3) == 'png') file_put_contents($this->getDataFolder() . 'icon.png', file_get_contents(file_get_contents($resource->getPathname())));
		}
		
		$this->getScheduler()->scheduleRepeatingTask(new GameManagerUpdateTask($this->getGameManager(),$this->getConfig()->get('ticks-per-update-cycle', 20)), $this->getConfig()->get('ticks-per-update-cycle', 20));
		$this->getServer()->getPluginManager()->registerEvents(new PlayerQuitEventListener($this->getGameManager()), $this);
		$this->getServer()->getPluginManager()->registerEvents(new PlayerCommandPreprocessEventListener($this->getGameManager()), $this);
		$this->getServer()->getPluginManager()->registerEvents(new PlayerJoinEventListener($this),$this);
		$this->getServer()->getPluginManager()->registerEvents(new PlayerRespawnEventListener($this), $this);
		$this->baseLang = new Language($this->getConfig()->get('language', 'auto') == 'auto' ? $this->getServer()->getProperty("settings.language") : $this->getConfig()->get('language'),$this->getDataFolder() . 'lang' . DIRECTORY_SEPARATOR);
		$this->getServer()->getCommandMap()->register('minigameapi', new MiniGameApiCommand($this));
		$this->getServer()->getCommandMap()->register('quit', new QuitCommand($this));
	}
	public function getLanguage() : Language{
		return $this->baseLang;
	}
	public function getLogoImagePath() : string {
		return $this->getDataFolder() . 'logo.png';
	}
	public function getGameManager() : GameManager{
		return $this->gameManager;
	}
	public function setPlayerData(string $playerName, PlayerData $playerData) {
		file_put_contents($this->getDataFolder() . strtolower($playerName) . '.dat', serialize($playerData));
	}
	public function getPlayerData(string $playerName, bool $delete = true) : ?PlayerData {
		if(!file_exists($this->getDataFolder() . strtolower($playerName) . '.dat')) return null;
		$return = unserialize(file_get_contents($this->getDataFolder() . strtolower($playerName) . '.dat'));
        if($delete) unlink($this->getDataFolder() . strtolower($playerName) . '.dat');
        return $return;
	}
    public function getPrefix() : string {
        return TextFormat::GREEN . $this->getLanguage()->translateString('prefix') . ' ' . TextFormat::YELLOW;
    }
}
