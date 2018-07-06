<?php
namespace minigameapi\event;
use minigameapi\Game;
use pocketmine\Player;

class  MiniGamePlayerJoinEvent extends MiniGameEvent {
    private $player;
    public function __construct(Game $game, Player $player) {
        parent::__construct($game);
        $this->player = $player;
    }
    public function getPlayer() : Player {
        return $this->player;
    }
}