<?php
namespace minigameapi\event;
use minigameapi\Game;
use minigameapi\MiniGameApi;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;

abstract class MiniGameEvent extends Event implements Cancellable{
    private $game;
    public function __construct(Game $game) {
        $this->game = $game;
    }
    public function getGame() : Game {
        return $this->game;
    }
}