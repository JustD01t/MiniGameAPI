<?php
namespace seedCape;

use jojoe77777\FormAPI\Form;
use jojoe77777\FormAPI\FormAPI;
use jojoe77777\FormAPI\SimpleForm;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\entity\Skin;
use pocketmine\utils\TextFormat;

class SeedCape extends PluginBase implements Listener {
	public function onEnable() : void {
		@mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
	}
	public function onPlayerJoin(PlayerJoinEvent $event) {
	    $data = $this->getConfig()->get('data', []);
		if(!isset($data[$event->getPlayer()->getName()])) {
			$data[$event->getPlayer()->getName()] = ['https://raw.githubusercontent.com/djdisodo/seedCapes/master/cape.png',0];
			$this->getConfig()->set('data', $data);
			$this->getConfig()->save();
		}
		$this->updateCape($event->getPlayer());
	}
	public function updateCape(Player $player) {
		$url = $this->getConfig()->get('data',[])[$player->getName()][0];
		$this->getServer()->getAsyncPool()->submitTask(new UpdateSkinTask($url,$player));
	}
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
	    if (isset($args[0]) and ($args[0] == '추가')) {
	        if (!$sender->hasPermission('cape.add')) {
	            $sender->sendMessage('권한없음');
	            return true;
            }
	        if (!isset($args[1]) or !isset($args[2]) or !isset($args[3])) {
	            $sender->sendMessage('요구사항 부족');
	            return true;
            }
            $capes = $this->getConfig()->get('capes', []);
	        $capes[$args[1]] = [$args[2], $args[3]];
	        $this->getConfig()->set('capes', $capes);
	        $this->getConfig()->save();
	        $sender->sendMessage('추가했습니다');
	        return true;
        }
        $form = FormAPI::getInstance()->createSimpleForm(function (Player $player, $data) : void {
            switch ($data) {
                case null:
                    break;
                case 'buy':
                    $form = FormAPI::getInstance()->createSimpleForm(function (Player $player, $data) : void {
                        if (is_null($data)) return;
                        if ($data == 'custom') {
                            if (EconomyAPI::getInstance()->myMoney($player) < 5000) {
                                $player->sendMessage('돈이 부족합니다');
                                return;
                            }
                            $form = FormAPI::getInstance()->createCustomForm(function (Player $player, $data) : void {
                                if (is_null($data)) return;
                                $playerCapes = $this->getConfig()->get($player->getName(), []);
                                $playerCapes[$data['name']] = [$data['image'], 0];
                                $player->sendMessage('커스텀 망토를 구매했습니다');
                                EconomyAPI::getInstance()->reduceMoney($player, 5000);
                            });
                            $form->setTitle('커스텀 망토');
                            $form->addInput('같은 이름을 가진 망토를 덮어쓸 수 있습니다' . TextFormat::EOL . '이름:', '망토이름', '','name');
                            $form->addInput('이미지', '망토 이미지 URL (png)', '','image');
                            $form->sendToPlayer($player);
                            return;
                        }
                       $capes = $this->getConfig()->get('capes',[]);
                        if ($capes[$data][1] > EconomyAPI::getInstance()->myMoney($player)) {
                            $player->sendMessage('돈이 부족합니다');
                            return;
                        }
                        $playerCapes = $this->getConfig()->get($player->getName(),[]);
                        $playerCapes[$data] = $capes[$data];
                        EconomyAPI::getInstance()->reduceMoney($player,$capes[$data][1]);
                        $player->sendMessage('망토를 구매했습니다.');
                        $this->getConfig()->set($player->getName(),$playerCapes);
                        $this->getConfig()->save();
                    });
                    $form->setTitle('구매');
                    $capes = $this->getConfig()->get('capes', []);
                    foreach ($this->getConfig()->get($player->getName(), []) as $key => $data) {
                        unset($capes[$key]);
                    }
                    foreach ($capes as $key => $cape) {
                        $form->addButton($key . TextFormat::EOL . '가격:' . $cape[1],SimpleForm::IMAGE_TYPE_URL,$cape[0], $key);
                    }
                    if ($player->hasPermission('cape.custom')) $form->addButton('커스텀 망토' . TextFormat::EOL . '가격: 5000',-1,'','custom');
                    $form->sendToPlayer($player);
                    break;
                case 'set':
                    if (count($this->getConfig()->get($player->getName(), [])) === 0) {
                        $player->sendMessage('망토가 없습니다');
                    }
                    $form = FormAPI::getInstance()->createCustomForm(function (Player $player, $data) : void {
                        if (is_null($data)) return;
                        $playerCapes = $this->getConfig()->get($player->getName(),[]);
                        $data2 = $this->getConfig()->get('data',[]);
                        $data2[$player->getName()] = $playerCapes[array_keys($playerCapes)[$data['set']]];
                        $this->getConfig()->set('data', $data2);
                        $this->getConfig()->save();
                        $player->sendMessage('망토를 장착했습니다');
                        $this->updateCape($player);
                    });
                    $form->setTitle('장착');
                    $form->addDropdown('장착', array_keys($this->getConfig()->get($player->getName(),[])), 0, 'set');
                    $form->sendToPlayer($player);
                    break;
            }
        });
	    $form->setTitle('망토');
	    $form->addButton('구매', -1, '','buy');
	    $form->addButton('장착', -1,'','set');
	    $form->sendToPlayer($sender);
	    return true;
    }
}
class UpdateSkinTask extends AsyncTask{
	public $url;
	public $player;
	public $skin;
	public $skin2;
	public function __construct(string $url, Player $player){
		$this->url = $url;
		$oldSkin = $player->getSkin();
		$this->skin = [$oldSkin->getSkinId(), $oldSkin->getSkinData(), $oldSkin->getGeometryName(), $oldSkin->getGeometryData()];
		$this->player = $player->getName();
		var_dump(strlen($oldSkin->getCapeData()));
	}
	public function onRun(){
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $base = imagecreatefromstring(file_get_contents('https://raw.githubusercontent.com/djdisodo/seedCapes/master/BlankCape.png',false,stream_context_create($arrContextOptions)));
        $img = imagecreatefromstring(file_get_contents($this->url,false,stream_context_create($arrContextOptions)));
$l = getimagesizefromstring(file_get_contents('https://raw.githubusercontent.com/djdisodo/seedCapes/master/BlankCape.png',false,stream_context_create($arrContextOptions)));


$skinbytes = '';
for ($y = 0; $y < $l[1]; $y++) {
    for ($x = 0; $x < $l[0]; $x++) {
        $argb = imagecolorat($img, $x, $y);
        if($argb == false) $argb = imagecolorat($base, $x, $y);
        $a = ((~((int)($argb >> 24))) << 1) & 0xff;
        $r = ($argb >> 16) & 0xff;
        $g = ($argb >> 8) & 0xff;
        $b = $argb & 0xff;
        $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
    }
}
        //var_dump($skinbytes);
        $skin = new Skin($this->skin[0], $this->skin[1], $skinbytes, $this->skin[2], $this->skin[3]);
        $this->skin2 = serialize($skin);
	}
	public function onCompletion(Server $s){
		$p = $s->getPlayer($this->player);
		$p->setSkin(unserialize($this->skin2));
		$p->sendSkin();
	}
}