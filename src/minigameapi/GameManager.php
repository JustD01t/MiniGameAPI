<?php

namespace minigameapi;

class GameManager {
  private $games = [];
  private $miniGameApi;
  public function __construct(MiniGameApi $miniGameApi) {
    $this->miniGameApi = $miniGameApi;
  }
  public function submitGame(Game $game) : ?int{
    if
    $game->setGameManager($this);
    $game->setGameId(array_pop(array_keys($this->games)));
    $this->games[] = $game;
    return;
  }
  public function getGames() : array{
    return $this->games()
  }
  public function getGameId(string $gameTitle) : ?int{
    foreach($this->getGames() as $gameId => $game) {
      if($game->getTitle() == $gameTitle) return $gameId;
    }
  }   
}
