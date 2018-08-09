<?php
namespace minigameapi;

use minigameapi\event\MiniGamePlayerJoinEvent;
use minigameapi\event\MiniGamePlayerQuitEvent;
use minigameapi\event\MiniGamePlayerRemoveEvent;
use minigameapi\event\MiniGameStartEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\WheatSeeds;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

abstract class Game {
	const END_NORMAL = 0;
	const END_TIMEOUT = 1;
	const END_NO_PLAYERS = 2;
	const END_KILLED_GAME = 3;
	const END_STARTING_ERROR = 4;
	private $name;
	private $neededPlayers;
	private $maxPlayers;
	private $runningTime;
	private $waitingRoom;
	private $waitingTime;
	private $teams = [];
	private $waitingPlayers = [];
	private $plugin;
	private $remainingWaitTime;
	private $remainingRunTime;
	private $iconItem;
	private $iconImage;
	private $allowedCommands = [];
	private $prefix;
	private $winner;
	private $description = '';
	public function __construct(Plugin $plugin, string $name,int $neededPlayers = 1,int $maxPlayers = 9999, Time $runningTime = null, Time $waitingTime = null, Position $waitingRoom = null) {
		$this->plugin = $plugin;
		$this->name = $name;
		$this->setPrefix('[' . $name . ']');
		$this->neededPlayers = $neededPlayers;
		$this->maxPlayers = $maxPlayers;
		$this->runningTime = is_null($runningTime) ? new Time(0,0,5) : $runningTime;
		$this->waitingRoom = $waitingRoom;
		$this->waitingTime = is_null($waitingTime) ? new Time(0,30) : $waitingTime;
	}
	final public function addAllowedCommand(string $command) {
		$this->allowedCommands[] = $command;
	}
	final public function addAllowedCommands(array $commands) {
		$this->allowedCommands = array_merge($this->allowedCommands, $commands);
	}
	final public function addWaitingPlayer(Player $player) : bool{
		if($this->isRunning()) return false;
		if($this->getMaxPlayers() == count($this->getPlayers())) return false;
		$ev = new MiniGamePlayerJoinEvent($this, $player);
		$this->getMiniGameApi()->getServer()->getPluginManager()->callEvent($ev);
		if($ev->isCancelled()) return false;
		$this->getMiniGameApi()->setPlayerData($player->getName(), new PlayerData($player));
		if($this->onJoin()) {
			$this->getGameManager()->removePlayer($player);
			$this->waitingPlayers[] = $player;
			if(!is_null($this->getWaitingRoom())) $player->teleport($this->getWaitingRoom());
			$player->getInventory()->clearAll();
			$player->getArmorInventory()->clearAll();
			$player->setHealth($player->getMaxHealth());
			$player->setGamemode(Player::ADVENTURE);
			if($this->getMaxPlayers() == count($this->getPlayers())) {
                $this->start();
                return true;
            }
			if($this->getNeededPlayers() == count($this->getPlayers())) $this->wait();
			$this->broadcastMessage($this->getMiniGameApi()->getPrefix() . $this->getMiniGameApi()->getLanguage()->translateString('left.players', [$this->getNeededPlayers(), count($this->getPlayers()),$this->getMaxPlayers()]));
			return true;
		}
		return false;
	}
	public function assignPlayers() {
		foreach($this->getPlayers() as $player) {
			$team = new Team($this, $player->getName(), 1, 1);
			$team->addPlayer($player);
			$this->submitTeam($team);
		}
	}
	final public function broadcastMessage(string $message){
		foreach($this->getPlayers() as $player) {
			$player->sendMessage($message);
		}
		return;
	}
	final public function end(int $endCode = self::END_NORMAL) {
		switch($endCode) {
			case self::END_NORMAL:
			case self::END_TIMEOUT:
			case self::END_NO_PLAYERS:
			case self::END_KILLED_GAME:
			case self::END_STARTING_ERROR:
				$this->onEnd($endCode);
				if (!is_null($this->getWinner())) {
				    $this->getMiniGameApi()->getServer()->broadcastMessage($this->getMiniGameApi()->getPrefix() . $this->getMiniGameApi()->getLanguage()->translateString('end.wons',[$this->getWinner()->getName(), $this->getName()]));
				} else {
				    $this->getMiniGameApi()->getServer()->broadcastMessage($this->getMiniGameApi()->getPrefix() . $this->getMiniGameApi()->getLanguage()->translateString('end',[$this->getName()]));
                }
				foreach ($this->getPlayers() as $player) {
				  $this->quitPlayer($player);
				}
				unset($this->remainingWaitTime);
				unset($this->remainingRunTime);
				$this->reset();
				break;
		}
	}
	final public function getAllowedCommands() : array {
		return $this->allowedCommands;
	}
	final public function getDescription() : string {
	    return $this->description;
    }
	final public function getMiniGameApi() : MiniGameApi {
		return $this->getPlugin()->getServer()->getPluginManager()->getPlugin('MiniGameAPI');
	}
	final public function getGameManager() : GameManager{
		return $this->getMiniGameApi()->getGameManager();
	}
	final public function getIconImage() : string {
		if(isset($this->iconImage)) return $this->iconImage;
		return $this->getMiniGameApi()->getLogoImagePath();
	}
	final public function getIconItem() : Item {
		if(isset($this->iconItem)) return $this->iconItem;
		return new WheatSeeds();
	}
	final public function getJoinedTeam(Player $player) : ?Team {
		foreach ($this->getTeams() as $team) {
			if($team->isInTeam($player)) return $team;
		}
		return null;
	}
	final public function getMaxPlayers() : int{
		return $this->maxPlayers;
	}
	final public function getName() : string{
		return $this->name;
	}
	final public function getNeededPlayers() : int{
		return $this->neededPlayers;
	}
	final public function getPrefix() : string{
		return $this->prefix;
	}
	final public function getPlayers() : array{
		if(!$this->isRunning()) return $this->waitingPlayers;
		$result = [];
		foreach($this->getTeams() as $team) {
			$result = array_merge($result,$team->getPlayers());
		}
		return $result;
	}
	final public function getPlugin() : Plugin{
		return $this->plugin;
	}
	final public function getRemainingRunTime() : ?Time {
		return isset($this->remainingRunTime) ? $this->remainingRunTime : null;
	}
	final public function getRemainingWaitTime() : ?Time {
		return isset($this->remainingWaitTime) ? $this->remainingWaitTime : null;
	}
	final public function getWaitingRoom() : ?Position {
		return $this->waitingRoom;
	}
	final public function getWinner() : ?Team {
	    return $this->winner;
    }
	final public function getRunningTime() : Time {
		return $this->runningTime;
	}
	final public function getTeam(string $teamName) : ?Team{
		foreach($this->getTeams() as $team) {
			if($teamName == $team->getName()) return $team;
		}
		return null;
	}
	final public function getTeams() : array{
		return $this->teams;
	}
	final public function getWaitingTime() : Time {
		return $this->waitingTime;
	}
	final public function isAllowedCommand(string $command) : bool{
		switch (explode('.', $command)[0]) {
			case 'minigameapi':
			case $this->getMiniGameApi()->getLanguage()->translateString('command.miniGameApi'):
			case $this->getMiniGameApi()->getLanguage()->translateString('command.quit'):
				return true;
		}
		foreach ($this->getAllowedCommands() as $allowedCommand) {
			if(explode('.', $allowedCommand) == array_splice(explode('.', $command), count(explode('.', $allowedCommand)))) return true;
		}
		return false;
	}
	final public function isInGame(Player $player) : bool {
		if(!$this->isRunning()) foreach ($this->getPlayers() as $pl) {
			if($pl->getName() == $player->getName()) return true;
		}
		if(is_null($this->getJoinedTeam($player))) return false;
		return true;
	}
	final public function isStartable() : bool{
		foreach($this->getTeams() as $team) {
			if(count($team->getPlayers()) < $team->getMinPlayers()) return false;
		}
		return true;
	}
	final public function isRunning() : bool {
		return is_null($this->getRemainingRunTime()) ? false : true;
	}
	final public function isWaiting() : bool {
		return is_null($this->getRemainingWaitTime()) ? false : true;
	}
	public function onEnd(int $endCode) {}
	public function onJoin() : bool{ return true; }
	public function onStart() : bool{ return true; }
	public function onWait() : bool{ return true; }
	public function onWaiting(int $updateCycle) {}
	public function onRunning(int $updateCycle) {}
	public function onUpdate(int $updateCycle) {}
	final public function quitPlayer(Player $player) : bool {
	    if ($this->isWaiting() or $this->isRunning()) {
            $ev = new MiniGamePlayerQuitEvent($this, $player);
            $this->getMiniGameApi()->getServer()->getPluginManager()->callEvent($ev);
            if ($ev->isCancelled()) return false;
        }
		if ($this->removePlayer($player)) {
			$this->getMiniGameApi()->getPlayerData($player->getName())->restore($player);
			return true;
		}
		return false;
	}
	final public function removePlayer(Player $player) : bool{
		if($this->isRunning()) {
			foreach ($this->getTeams() as $team) {
				if($team->removePlayer($player)) {
					return true;
				}
			}
		} else {
			foreach ($this->getPlayers() as $key => $pl) {
				//$pl instanceof Player;
				if ($player->getName() == $pl->getName()) {
					$ev = new MiniGamePlayerRemoveEvent($this,$player);
					$this->getMiniGameApi()->getServer()->getPluginManager()->callEvent($ev);
					unset($this->waitingPlayers[$key]);
					$this->waitingPlayers = array_values($this->waitingPlayers);
					if ($this->getNeededPlayers() > count($this->getPlayers())) unset($this->remainingWaitTime);
					return true;
				}
			}
		}
		return false;
	}
	final public function removeTeam(string $teamName) {
		foreach($this->getTeams() as $key => $team){
			if($team->getName() == $teamName){
				unset($this->teams[$key]);
			}
		}
		$this->teams = array_values($this->teams);
		if(count($this->getTeams()) == 0 and $this->isRunning()) $this->end(self::END_NO_PLAYERS);
		return;
	}
	final public function reset() {
		$this->resetWaitingPlayers();
		$this->resetTeams();
	}
	final public function resetTeams() {
		$this->teams = [];
	}
	final public function resetWaitingPlayers(){
		$this->waitingPlayers = [];
	}
	final public function setDescription(string $description) {
	    $this->description = $description;
    }
	final public function setIconImage(string $path) {
		if (mime_content_type($path) !== 'image/png') throw new \InvalidArgumentException($this->getGameManager()->getMiniGameApi()->getLanguage()->translateString('exception.invalidIconImagePath', [$this->getName()]));
		$this->iconImage = $path;
	}
	final public function setIconItem(Item $item) {
		if ($item->getId() == ItemIds::AIR) throw new \InvalidArgumentException($this->getGameManager()->getMiniGameApi()->getLanguage()->translateString('exception.invalidIconItem'));
		$this->iconItem = $item;
	}
	final public function setPrefix(string $prefix) {
		$this->prefix = $prefix;
	}
	final public function setWinner(Team $winner) {
	    $this->winner = $winner;
    }
	final public function start() : bool{
		unset($this->remainingWaitTime);
		$this->resetTeams();
		$this->assignPlayers();
		if(!$this->isStartable()) {
			$this->end(self::END_STARTING_ERROR);
			return false;
		}
		$ev = new MiniGameStartEvent($this);
        $this->remainingRunTime = clone $this->getRunningTime();
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($ev);
		if(!$this->onStart() or $ev->isCancelled()) {
		    unset($this->remainingRunTime);
		    return false;
        }
		$this->resetWaitingPlayers();
		foreach($this->getTeams() as $team) {
			$team->spawn();
		}
		$this->remainingRunTime = clone $this->getRunningTime();
		$this->broadcastMessage($this->getMiniGameApi()->getPrefix() . $this->getMiniGameApi()->getLanguage()->translateString('start.started'));
		return true;
	}
	final public function submitTeam(Team $team) {
		$this->removeTeam($team->getName());
		$this->teams[] = $team;
		return;
	}
	final public function update(int $updateCycle) {
	    $this->onUpdate($updateCycle);
		if($this->isWaiting()) {
			$this->getRemainingWaitTime()->reduceTime($updateCycle);
			if ($this->getMiniGameApi()->getPrefix() . $this->getMiniGameApi()->getConfig()->get('show-left-time') !== false and $this->getRemainingWaitTime()->asSec() <= $this->getMiniGameApi()->getConfig()->get('show-left-time'))$this->getMiniGameApi()->getLanguage()->translateString('left.time',[intval($this->getRemainingWaitTime()->asSec())]);
			if($this->getRemainingWaitTime()->asTick() <= 0) {
				$this->start();
				return;
			}
			$this->onWaiting($updateCycle);
		} elseif($this->isRunning()) {
			$this->getRemainingRunTime()->reduceTime($updateCycle);
            if ($this->getMiniGameApi()->getPrefix() . $this->getMiniGameApi()->getConfig()->get('show-left-time') !== false and $this->getRemainingRunTime()->asSec() <= $this->getMiniGameApi()->getConfig()->get('show-left-time'))$this->getMiniGameApi()->getLanguage()->translateString('left.time',[intval($this->getRemainingRunTime()->asSec())]);
			if($this->getRemainingRunTime()->asTick() <= 0) {
				$this->end(self::END_TIMEOUT);
				return;
			}
			$this->onRunning($updateCycle);
		}
	}
	final public function wait() {
		$this->remainingWaitTime = clone $this->getWaitingTime();
		$this->broadcastMessage($this->getMiniGameApi()->getPrefix() . $this->getMiniGameApi()->getLanguage()->translateString('left.time', [(int)$this->getRemainingWaitTime()->asSec()]));
		$this->onWait();
	}
}
