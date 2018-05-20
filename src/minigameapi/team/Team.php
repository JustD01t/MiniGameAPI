<?php
namespace minigameapi\team;

use pocketmine\Player;

abstract class Team {
	private $maxPlayers = '0';
	private $minPlayers = '0';
	private $teamName;
	private $players = [];
	public function __construct(string $teamName, Player $firstPlayer, int $maxPlayers = 0, int $minPlayers = 0) {
		$this->maxPlayers = $maxPlayers;
		$this->minPlayers = $minPlayers;
		$this->teamName = $teamName;
		$this->players[] = $firstPlayer;
	}
}