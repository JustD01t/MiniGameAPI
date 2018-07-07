<?php
namespace minigameapi\command;

use minigameapi\MiniGameApi;
use pocketmine\command\PluginCommand;
use pocketmine\lang\BaseLang;
use pocketmine\utils\TextFormat;

class MiniGameApiCommand extends PluginCommand {
    private $miniGameApi;
    public function __construct(MiniGameApi $miniGameApi) {
        $this->miniGameApi = $miniGameApi;
        parent::__construct('minigameapi', $miniGameApi);
        $this->setAliases($this->getBaseLang()->translate('command.miniGameApi'));
        $this->setUsage(TextFormat::EOL .
        $this->getPrefix() . 'MiniGameAPI-' . $this->getMiniGameApi()->getDescription()->getVersion() . TextFormat::EOL .
        $this->getBaseLang()->translateString('command.miniGameApi.join.usage',[$this->getBaseLang()->translateString('command.miniGameApi'), $this->getBaseLang()->translateString('command.miniGameApi.join')]) . TextFormat::GREEN . ' : ' . TextFormat::RESET . $this->getBaseLang()->translateString('command.miniGameApi.join.description') . TextFormat::EOL);
    }
    public function getMiniGameApi() : MiniGameApi {
        return $this->miniGameApi;
    }
    public function getPrefix() : string {
        return $this->getMiniGameApi()->getBaseLang()->translateString('command.prefix') . ' ';
    }
    public function getBaseLang() : BaseLang {
        return $this->getMiniGameApi()->getBaseLang();
    }
}