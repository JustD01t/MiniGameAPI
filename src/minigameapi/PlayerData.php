<?php
namespace minigameapi;

use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class PlayerData {
	private $location;
	private $nameTag;
	private $items = [];
	private $armors = [];
	private $health;
	private $gamemode;
	private $score;
	public function __construct(Player $player) {
		$this->location['level'] = $player->getLevel()->getFolderName();
		$this->location['x'] = $player->getX();
		$this->location['y'] = $player->getY();
		$this->location['z'] = $player->getZ();
		$this->nameTag = $player->getNameTag();
		$this->items = $player->getInventory()->getContents(true);
		$this->armors = $player->getArmorInventory()->getContents(true);
		$this->health = $player->getHealth();
		$this->gamemode = $player->getGamemode();
		$this->score = $player->getScoreTag();
	}
	public function restore(Player $player) {
		$player->teleport(new Position($this->location['x'],$this->location['y'],$this->location['z'],Server::getInstance()->getLevelByName($this->location['level'])));
		$player->setNameTag($this->nameTag);
		$player->getInventory()->setContents($this->items,true);
		$player->getArmorInventory()->setContents($this->armors,true);
		$player->setHealth($this->health);
		$player->setGamemode($this->gamemode);
		$player->setScoreTag($this->score);
	}
}
