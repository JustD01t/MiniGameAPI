<?php
namespace minigameapi;

class PlayerData {
	private $nameTag;
	private $items = [];
	private $armors = [];
	public function __construct(Player $player) {
		$this->nameTag = $player->getNameTag();
		$this->items = $player->getInventory()->getContents(true);
		$this->armors = $player->getArmorInventory()->getContents(true);
	}
	public function restore(Player $player) {
		$player->setNameTag($this->nameTag);
		$player->getInventory()->setContents($this->items,true);
		$player->getInventory()->setContetns($this->items,true);
	}
}
