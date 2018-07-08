<?php
namespace minigameapi\event;
use minigameapi\Game;
use pocketmine\event\Cancellable;
use pocketmine\Player;

class MiniGamePlayerQuitEvent extends MiniGameEvent implements Cancellable {
    private $player;
    public function __construct(Game $game, Player $player) {
        parent::__construct($game);
        $this->player = $player;
    }
    public function getPlayer() : Player {
        return $this->player;
    }
}