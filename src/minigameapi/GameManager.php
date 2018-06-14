<?php

namespace minigameapi;

class GameManager {
  private $games = [];
  private $miniGameApi;
  public function __construct(MiniGameApi $miniGameApi) {
    $this->miniGameApi = $miniGameApi;
  }
  public function broadcastMessageToGames(string $message){
    foreach($this->getGames() as $game) {
      $game->broadcastMessage($message);
    }
  }
  public function submitGame(Game $game) {
    if(!is_null($this->getGame($game->getName()))) return;
    $this->games[] = $game;
  }
  public function removeGame(string $gameName) {
    foreach($this->getGames() as $key => $game){
      if($game->getName() = $gameName) {
        if($game->isStarted()) $game->end(Game::END_KILLED_GAME);
        unset($this->games[$key]);
      }
    }
    $this->games = array_values($this->games);
  }
  public function getGames() : array{
    return $this->games()
  }
  public function getGame(string $gameName) : ?Game{
    foreach($this->getGames() as $game) {
      if($game->getName() == $gameName) return $game;
    }
  }
  public function getTeams() : array {
    $result = [];
    foreach($this->getGames() as $game) {
      $result = array_merge($result,$game->getTeams();
    }
    return $result;
  }
  public function getTeam(string $teamName) : ?Team{
    foreach($this->getGames() as $game) {
      foreach($game->getTeams() as $team) {
        if($team->getName() == $teamName) return $team;
      }
    }
  }
  public function getPlayers() : array{
    $result = [];
    foreach($this->getGames() as $game){
      $result = array_merge($result,$game->getPlayers());
    }
    return $result;
  }
  public function removePlayer(Player $player){
    foreach($this->getGames() as $game) {
      $game->removePlayer($player);
    }
  }
}
