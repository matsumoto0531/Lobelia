<?php
namespace pve;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use Ramsey\Uuid\UUID;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\block\inventory\ChestInventory;
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\Chest;
use pocketmine\nbt\NBTStream;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties as EM;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;


use pve\item\ItemManager;
use pve\item\PveItem;
use market\form\forms\MenuForm;

class PointForm implements Listener {

    const CHOOSE_FORM_ID = 665;
    const SLIDE_FORM_ID = 666;

    public function __construct($plugin){
		$this->plugin = $plugin;
        $this->spawn();
        $this->choose = [];
    }

    public function sendForm($player, $data, $id){
        $pk = new ModalFormRequestPacket;
        $pk->formId = $id;
        $pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function sendChooseForm($player){
        $buttons = [];
        $data = ["??b??????", "??4??????", "??a??????", "??6??????", "??e??????", "??0??????"];
        foreach($data as $d){
          $buttons[] = ['text' => $d];
        }
        $data = [
                  'type' => 'form',
                  'title' => '??l???????????????????????????',
                  'content' => '???????????????????????????',
                  'buttons' => $buttons
        ];
        $this->sendForm($player, $data, self::CHOOSE_FORM_ID);
    }

    public function sendSlideForm($player){
        $content = [];
        $point = $this->plugin->playerData[$player->getName()]['point'];
        $content[] = ["type" => "slider", "text" => "???????????????????????????????", "min" => 0, "max" => $point];
        $data = [
              'type'=>'custom_form',
              'title'   => "??l???????????????????????????",
              'content' => $content
        ];
        $this->sendForm($player, $data, self::SLIDE_FORM_ID);
    }

    public function receiveSlideForm($data, $player){
        $choose = $this->choose[$player->getName()];
        $this->plugin->playermanager->addStatus($player, $data[0], $choose);
        $d = ["??b??????", "??4??????", "??a??????", "??6??????", "??e??????", "??0??????"];
        $player->sendMessage('??eINFO??f>>'.$d[$choose].'???'.$data[0].'???????????????????????????!');
    }

    public function onPacketReceive(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if(is_null($player)) return false;
        if($pk instanceof ModalFormResponsePacket){
            $data = json_decode($pk->formData, true);
            if(!isset($data)) return false;
            switch($pk->formId){
                case self::CHOOSE_FORM_ID:
                    $this->choose[$player->getName()] = $data;
                    $this->sendSlideForm($player);
                    break;
                case self::SLIDE_FORM_ID:
                    $this->receiveSlideForm($data, $player);
                    break;
            }
        }
    }


}