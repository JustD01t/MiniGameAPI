<?php
namespace minigameapi;

use pocketmine\Player;

class PlayerData {
    private $location;
	private $nameTag;
	private $items = [];
	private $armors = [];
	public function __construct(Player $player) {
	    $this->location = $player->getLocation();
		$this->nameTag = $player->getNameTag();
		$this->items = $player->getInventory()->getContents(true);
		$this->armors = $player->getArmorInventory()->getContents(true);
	}
	public function restore(Player $player) {
	    $player->teleport($this->location);
		$player->setNameTag($this->nameTag);
		$player->getInventory()->setContents($this->items,true);
		$player->getInventory()->setContetns($this->items,true);
	}
}
