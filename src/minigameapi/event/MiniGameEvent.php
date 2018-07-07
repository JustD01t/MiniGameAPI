<?php
namespace minigameapi\event;
use minigameapi\Game;
use pocketmine\event\Event;

abstract class MiniGameEvent extends Event {
    private $game;
    public function __construct(Game $game) {
        $this->game = $game;
    }
    public function getGame() : Game {
        return $this->game;
    }
}