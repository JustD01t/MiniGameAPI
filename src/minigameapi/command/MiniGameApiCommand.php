<?php
namespace minigameapi\command;

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
        $this->getBaseLang()->translateString('command.miniGameApi.kill.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.kill')]) . TextFormat::GREEN . ' : ' . TextFormat::RESET . $this->getBaseLang()->translateString('command.miniGameApi.kill.description') . TextFormat::EOL
        );
        $this->setPermission('miniGameApi.command');
        $this->setDescription($this->getBaseLang()->translateString('command.miniGameApi.description'));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        switch ($args[0]) {
            case $this->getBaseLang()->translateString('command.miniGameApi.join'):
                if (!$sender instanceof Player) {
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('commandMessage.onlyPlayers'));
                    break;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage($this->getMiniGameApi()->getServer()->getLanguage()->translateString(commands.generic.usage,[$this->getBaseLang()->translateString('command.miniGameApi.join.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.join')]) . TextFormat::RED]));
                    break;
                }
                if (!is_null($this->getMiniGameApi()->getGameManager()->getJoinedGame($sender))) {
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString('join.failed.alreadyInAnotherGame'));
                    $sender->sendMessage($this->getPrefix() . $this->getBaseLang()->translateString($this->getPrefix() . 'commandMessage.quitFirst', [$this->getBaseLang()->translateString('command.quit.usage',[$this->getBaseLang()->translateString('command.quit')]), $this->getBaseLang()->translateString('command.miniGameApi.quit.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.quit')])]));
                    break;
                }
                $game = $this->getMiniGameApi()->getGameManager()->getGame($args[3]);
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
        }
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