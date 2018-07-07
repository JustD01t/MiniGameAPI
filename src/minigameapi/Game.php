<?php
namespace minigameapi;

use minigameapi\event\MiniGamePlayerJoinEvent;
use minigameapi\event\MiniGameStartEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\WheatSeeds;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

abstract class Game {
	const END_NORMAL = 0;
	const END_NO_PLAYERS = 1;
	const END_KILLED_GAME = 3;
	const END_STARTING_ERROR = 4;
	private $name;
	private $neededPlayers;
	private $maxPlayers;
	private $runningTime;
	private $waitingRoom;
	private $waitingTime;
	private $teams = [];
	private $waitingPlayers;
	private $plugin;
	private $remainingWaitTime;
	private $remainingRunTime;
	private $iconItem;
	private $iconImage;
	public function __construct(Plugin $plugin, string $name,int $neededPlayers = 1,int $maxPlayers = 9999, Time $runningTime = null, Time $waitingTime = null, Position $waitingRoom = null) {
		$this->plugin = $plugin;
		$this->name = $name;
		$this->neededPlayers = $neededPlayers;
		$this->maxPlayers = $maxPlayers;
		$this->runningTime = is_null($runningTime) ? new Time(0,0,5) : $runningTime;
		$this->waitingRoom = $waitingRoom;
		$this->waitingTime = is_null($waitingTime) ? new Time(0,30) : $waitingTime;
	}
	public function addWaitingPlayer(Player $player) : bool{
		if($this->isRunning()) return false;
		$ev = new MiniGamePlayerJoinEvent($this, $player);
		$this->getMiniGameApi()->getServer()->getPluginManager()->callEvent($ev);
		if($ev->isCancelled()) return false;
		if($this->onJoin()) {
			$this->getGameManager()->removePlayer($player);
			$this->waitingPlayers[] = $player;
			if(!is_null($this->getWaitingRoom())) $player->teleport($this->getWaitingRoom());
			return true;
		}
		return false;
	}
    public function assignPlayers() {
		foreach($this->getPlayers() as $player) {
            $team = new Team($player->getName(), 1, 1);
            $team->addPlayer($player);
            $this->submitTeam($team);
        }
	}
	public function broadcastMessage(string $message){
		foreach($this->getTeams() as $team) {
			$team->broadcastMessage($message);
		}
		return;
	}
	public function end(int $endCode = self::END_NORMAL) {
		switch($endCode) {
			case self::END_NORMAL:
			case self::END_NO_PLAYERS:
			case self::END_KILLED_GAME:
			case self::END_STARTING_ERROR:
				unset($this->remainingRunTime);
				$this->onEnd($endCode);
				$this->reset();
				break;
		}
	}
	public function getMiniGameApi() : MiniGameApi {
	    return $this->getPlugin()->getServer()->getPluginManager()->getPlugin('MiniGameAPI');
    }
 	public function getGameManager() : GameManager{
		return $this->getMiniGameApi()->getGameManager();
	}
	public function getIconImage() : string {
	    if(isset($this->iconImage)) return $this->iconImage;
	    return $this->getMiniGameApi()->getLogoImagePath();
    }
    public function getIconItem() : Item {
	    if(isset($this->iconItem)) return $this->iconItem;
	    return new WheatSeeds();
    }
	public function getMaxPlayers() : int{
		return $this->maxPlayers;
	}
	public function getName() : string{
		return $this->name;
	}
	public function getNeededPlayers() : int{
		return $this->neededPlayers;
	}
	public function getPrefix() : string{
		return '[' . $this->getName() . ']';
	}
	public function getPlayers() : array{
		if(!$this->isRunning()) return $this->waitingPlayers;
		$result = [];
		foreach($this->getTeams() as $team) {
			$result = array_merge($result,$team->getPlayers());
		}
		return $result;
	}
	public function getPlugin() : Plugin{
		return $this->plugin;
	}
	public function getRemainingRunTime() : ?Time {
		return isset($this->remainingRunTime) ? $this->remainingRunTime : null;
	}
	public function getRemainingWaitTime() : ?Time {
		return isset($this->remainingWaitTime) ? $this->remainingWaitTime : null;
	}
	public function getWaitingRoom() : ?Position {
		return $this->waitingRoom;
	}
	public function getRunningTime() : Time {
		return $this->runningTime;
	}
	public function getTeam(string $teamName) : ?Team{
		foreach($this->getTeams() as $team) {
			if($teamName == $team->getName()) return $team;
		}
		return null;
	}
	public function getTeams() : array{
		return $this->teams;
	}
	public function getWaitingTime() : Time {
		return $this->waitingTime;
	}
	public function isStartable() : bool{
		foreach($this->getTeams() as $team) {
			if(count($team->getPlayers()) < $team->getMinPlayers()) return false;
		}
		return true;
	}
	public function isRunning() : bool {
	    return is_null($this->getRemainingRunTime()) ? false : true;
	}
	public function isWaiting() : bool {
		return is_null($this->getRemainingWaitTime()) ? false : true;
	}
	public function onEnd(int $endCode) {}
	public function onJoin() : bool{ return true; }
	public function onStart() : bool{ return true; }
	public function onWait() : bool{ return true; }
	public function onWaiting() {}
	public function onRunning() {}
	public function onUpdate() {}
	public function removePlayer(Player $player) : bool{
        if($this->isRunning()) {
            foreach ($this->getTeams() as $team) {
                if($team->removePlayer($player)) return true;
            }
        } else {
            foreach ($this->getPlayers() as $key => $pl) {
                //$pl instanceof Player;
                if ($player->getName() == $pl->getName()) {
                    unset($this->waitingPlayers[$key]);
                    $this->waitingPlayers = array_values($this->waitingPlayers);
                    return true;
                }
            }
        }
        return false;
	}
	public function removeTeam(string $teamName) {
		foreach($this->getTeams() as $key => $team){
			if($team->getName() == $teamName){
				unset($this->teams[$key]);
			}
		}
		$this->teams = array_values($this->teams);
		if(count($this->getTeams()) == 0 and $this->isRunning()) $this->end(self::END_NO_PLAYERS);
		return;
	}
	public function reset() {
		$this->resetWaitingPlayers();
		$this->resetTeams();
	}
	public function resetTeams() {
		$this->teams = [];
	}
	public function resetWaitingPlayers(){
		$this->waitingPlayers = [];
	}
    function setIconImage(string $path) {
        if (mime_content_type($path) !== 'image/png') throw new \InvalidArgumentException($this->getGameManager()->getMiniGameApi()->getBaseLang()->translateString('exception.invalidIconImagePath', [$this->getName()]));
        $this->iconImage = $path;
	}
	public function setIconItem(Item $item) {
	    if ($item->getId() == ItemIds::AIR) throw new \InvalidArgumentException($this->getGameManager()->getMiniGameApi()->getBaseLang()->translateString('exception.invalidIconItem'));
	    $this->iconItem = $item;
	}
	public function start() : bool{
		unset($this->remainingWaitTime);
		$this->resetTeams();
		$this->assignPlayers();
		if(!$this->isStartable()) {
			$this->end(self::END_STARTING_ERROR);
			return false;
		}
		$ev = new MiniGameStartEvent($this);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($ev);
		if($ev->isCancelled()) return false;
        if(!$this->onStart()) return false;
        $this->resetWaitingPlayers();
		foreach($this->getTeams() as $team) {
			$team->spawn();
		}
		$this->remainingRunTime = clone $this->getRunningTime();
		return true;
	}
	public function submitTeam(Team $team) {
		$this->removeTeam($team->getName());
		$team->setGame($this);
		$this->teams[] = $team;
		return;
	}
	public function update(int $updateCycle) {
		if($this->isWaiting()) {
		    if($this->getMaxPlayers() == count($this->getPlayers())) {
		        $this->start();
		        return;
            }
			$this->getRemainingWaitTime()->reduceTime($updateCycle);
			if($this->getRemainingWaitTime()->asTick() <= 0) {
				$this->start();
				return;
			}
			$this->onWaiting();
		} elseif($this->isRunning()) {
			$this->getRemainingRunTime()->reduceTime($updateCycle);
			if($this->getRemainingRunTime()->asTick() <= 0) {
				$this->end();
				return;
			}
			$this->onRunning();
		}
	}
	public function wait() {
		$this->remainingWaitTime = $this->getWaitingTime();
		$this->onWait();
	}
}
