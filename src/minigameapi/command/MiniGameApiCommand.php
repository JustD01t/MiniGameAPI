<?php
namespace minigameapi\command;

use minigameapi\Game;
use minigameapi\MiniGameApi;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\lang\BaseLang;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class MiniGameApiCommand extends PluginCommand {
    private $miniGameApi;
    public function __construct(MiniGameApi $miniGameApi) {
        $this->miniGameApi = $miniGameApi;
        parent::__construct('minigameapi', $miniGameApi);
        $this->setAliases([$this->getBaseLang()->translateString('command.miniGameApi')]);
        $this->setUsage(TextFormat::EOL .
        $this->getPrefix() . 'MiniGameAPI-' . $this->getMiniGameApi()->getDescription()->getVersion() . ' by djdisodo(왕고슴도치)' . TextFormat::EOL .
        $this->getBaseLang()->translateString('command.miniGameApi.join.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.join')]) . TextFormat::GREEN . ' : ' . TextFormat::RESET . $this->getBaseLang()->translateString('command.miniGameApi.join.description') . TextFormat::EOL .
        $this->getBaseLang()->translateString('command.miniGameApi.quit.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.quit')]) . TextFormat::GREEN . ' : ' . TextFormat::RESET . $this->getBaseLang()->translateString('command.miniGameApi.quit.description') . TextFormat::EOL .
        $this->getBaseLang()->translateString('command.miniGameApi.list.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.list')]) . TextFormat::GREEN . ' : ' . TextFormat::RESET . $this->getBaseLang()->translateString('command.miniGameApi.list.description') . TextFormat::EOL .
        $this->getBaseLang()->translateString('command.miniGameApi.kill.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.kill')]) . TextFormat::GREEN . ' : ' . TextFormat::RESET . $this->getBaseLang()->translateString('command.miniGameApi.kill.description') . TextFormat::EOL
        );
        $this->setDescription($this->getBaseLang()->translateString('command.miniGameApi.description'));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!isset($args[0])) {
            $sender->sendMessage($this->getMiniGameApi()->getServer()->getLanguage()->translateString('commands.generic.usage',[$this->getUsage()]));
            return;
        }
        switch ($args[0]) {
            case $this->getBaseLang()->translateString('command.miniGameApi.join'):
                if (!$sender->hasPermission('minigameapi.join')) {
                    $sender->sendMessage($this->getMiniGameApi()->getServer()->getLanguage()->translateString('commands.generic.permission'));
                    break;
                }
                if (!$sender instanceof Player) {
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('commandMessage.onlyPlayers'));
                    break;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage($this->getMiniGameApi()->getServer()->getLanguage()->translateString('commands.generic.usage',[$this->getBaseLang()->translateString('command.miniGameApi.join.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.join')]) . TextFormat::RED]));
                    break;
                }
                if (!is_null($this->getMiniGameApi()->getGameManager()->getJoinedGame($sender))) {
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('join.failed.alreadyInAnotherGame'));
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('commandMessage.quitFirst', [$this->getBaseLang()->translateString('command.quit.usage',[$this->getBaseLang()->translateString('command.quit')]), $this->getBaseLang()->translateString('command.miniGameApi.quit.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.quit')])]));
                    break;
                }
                $game = $this->getMiniGameApi()->getGameManager()->getGame($args[1]);
                if(is_null($game)) {
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('join.failed.notExistingGame'));
                    break;
                }
                if ($game->isRunning()) {
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('join.failed.alreadyStarted'));
                    break;
                }
                if ($game->getMaxPlayers() == count($game->getPlayers())) {
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('join.failed.fullGame'));
                    break;
                }
                if ($game->addWaitingPlayer($sender)) {
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('join.success',[$game->getName()]));
                }
                break;
            case $this->getBaseLang()->translateString('command.miniGameApi.quit'):
                if (!$sender->hasPermission('minigameapi.quit')) {
                    $sender->sendMessage($this->getMiniGameApi()->getServer()->getLanguage()->translateString('commands.generic.permission'));
                    break;
                }
                if (!$sender instanceof Player) {
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('commandMessage.onlyPlayers'));
                    break;
                }
                if($this->getMiniGameApi()->getGameManager()->removePlayer($sender)) {
                    $sender->sendMessage($this->getMiniGameApi()->getBaseLang()->translateString('quit.success'));
                    break;
                }
                $sender->sendMessage($this->getBaseLang()->translateString('quit.failed.notPlaying'));
                break;
            case $this->getBaseLang()->translateString('command.miniGameApi.list'):
                if (!$sender->hasPermission('minigameapi.list')) {
                    $sender->sendMessage($this->getMiniGameApi()->getServer()->getLanguage()->translateString('commands.generic.permission'));
                    break;
                }
                $list = [];
                foreach ($this->getMiniGameApi()->getGameManager()->getGames() as $game) {
                    $list[] = $game->getName();
                }
                $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('list.message'));
                $sender->sendMessage(TextFormat::YELLOW . implode(', ', $list));
                break;
            case  $this->getBaseLang()->translateString('command.miniGameApi.kill'):
                if (!$sender->hasPermission('minigameapi.kill')) {
                    $sender->sendMessage($this->getMiniGameApi()->getServer()->getLanguage()->translateString('commands.generic.permission'));
                    break;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage($this->getMiniGameApi()->getServer()->getLanguage()->translateString('commands.generic.usage',[$this->getBaseLang()->translateString('command.miniGameApi.kill.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.kill')]) . TextFormat::GREEN . ' : ' . TextFormat::RESET . $this->getBaseLang()->translateString('command.miniGameApi.kill.description') . TextFormat::EOL]));
                    break;
                }
                $game = $this->getMiniGameApi()->getGameManager()->getGame($args[1]);
                if(is_null($game)) {
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('kill.failed.notExistingGame'));
                    break;
                }
                $game->end(Game::END_KILLED_GAME);
                $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('kill.success', [$game->getName()]));
                break;
            default:
                $sender->sendMessage($this->getMiniGameApi()->getServer()->getLanguage()->translateString('commands.generic.usage',[$this->getUsage()]));
                return;
        }
        return;
    }

    public function getMiniGameApi() : MiniGameApi {
        return $this->miniGameApi;
    }
    public function getPrefix() : string {
        return TextFormat::GREEN . $this->getMiniGameApi()->getBaseLang()->translateString('command.prefix') . ' ' . TextFormat::YELLOW;
    }
    public function getBaseLang() : BaseLang {
        return $this->getMiniGameApi()->getBaseLang();
    }
}