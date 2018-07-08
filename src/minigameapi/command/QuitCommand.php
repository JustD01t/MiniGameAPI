<?php
namespace minigameapi\command;
use minigameapi\MiniGameApi;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

class QuitCommand extends PluginCommand {
    private $miniGameApiCommand;
    public function __construct(MiniGameApi $miniGameApi) {
        $this->miniGameApiCommand = new MiniGameApiCommand($miniGameApi);
        parent::__construct('quitgame',$miniGameApi);
        $this->setAliases([$miniGameApi->getBaseLang()->translateString('command.quit')]);
        $this->setUsage($miniGameApi->getBaseLang()->translateString('command.quit.usage'));
        $this->setDescription($miniGameApi->getBaseLang()->translateString('command.quit.description'));
        $this->setPermission('minigameapi.quit');
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        return $this->miniGameApiCommand->execute($sender,$commandLabel,['quit']);
    }
}