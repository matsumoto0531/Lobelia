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
use pve\inventory\ShopWindow;
use market\form\forms\MenuForm;

class Shopper implements Listener {
    const NAME = '§l店員';
    const SHOP_FORM_ID = 105;
    const COUNT_FORM_ID = 106;
    const CHOOSE_FORM_ID = 199;

    public function __construct($plugin){
		$this->plugin = $plugin;
    $this->spawn();
    $this->choose = [];
    $this->market = $this->plugin->getServer()->getPluginManager()->getPlugin('Market');
    }
    
    public function spawn(){ 
	    $data = $this->plugin->shopperData;
      foreach($data as $field => $pos){
        $this->eid[$field] = Entity::nextRuntimeId();
        $this->uuid[$field] = UUID::fromString(md5(uniqid(mt_rand(), true)));
        $this->pk[$field] = $this->summon($pos, $field);
        $this->chest[$field] = new Vector3($pos['x'], $pos['y'] - 1, $pos['z']);
      }
    }

    public function summon($pos, $field){
        $pk = new AddPlayerPacket();
        $pk->uuid = $this->uuid[$field];
        $pk->username = self::NAME;
        $pk->actorRuntimeId = $this->eid[$field];
        $pk->position = new Vector3($pos['x'], $pos['y'], $pos['z']);
        $pk->motion = new Vector3(0, 0, 0);
        $pk->yaw = $pos['yaw'];
        $pk->pitch = 0;
        $pk->item = ItemStackWrapper::legacy(ItemStack::null());
        $pk->gameMode = 0;
        $pk->syncedProperties = new PropertySyncData([1], [1.0]);
        $pk2 = UpdateAbilitiesPacket::create(0, 0, $this->eid[$field], []);
        $pk->abilitiesPacket = $pk2;
        $meta = new EntityMetadataCollection();
        $meta->setByte(EM::ALWAYS_SHOW_NAMETAG, 1);
        $meta->setString(EM::NAMETAG, self::NAME);
        $meta->setLong(EM::LEAD_HOLDER_EID, -1);
        $meta->setFloat(EM::SCALE, 1);
	      $pk->metadata = $meta -> getAll();
        //$pk->item = Item::get(0, 0, 0);
        /*$pk->metadata =
        [
          Entity::DATA_FLAGS => 
              [
                  Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG ^ 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG
              ],
          Entity::DATA_NAMETAG => 
              [
                  Entity::DATA_TYPE_STRING, self::NAME
              ],
          Entity::DATA_LEAD_HOLDER_EID => 
              [
                  Entity::DATA_TYPE_LONG, -1
              ],
          Entity::DATA_SCALE => 
              [
                  Entity::DATA_TYPE_FLOAT,1
              ]
        ];*/
       return $pk;
    }

    public function removeAll($field){
      $players = $this->plugin->fieldmanager->getPlayers($field);
      if(!isset($this->eid[$field]))return false;
      foreach($players as $player){
        $this->remove($player, $field);
      }
    }
    
    public function remove($player, $field){
      $pk = new RemoveActorPacket();
      $pk->actorUniqueId = $this->eid[$field];
      $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function sendPacket($player, $field){
        $player->getNetworkSession()->sendDataPacket($this->pk[$field]);
        $skin = $this->plugin->mob->Skin;
        if(!isset($skin[self::NAME])) return false;
        $pk2 = new PlayerSkinPacket();
        $pk2->uuid = $this->uuid[$field];
        $pk2->skin = SkinAdapterSingleton::get()->toSkinData($skin[self::NAME]);
        $player->getNetworkSession()->sendDataPacket($pk2);
    }

    public function onReceive(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if(is_null($player)) return false;
        $field = $this->plugin->fieldmanager->getField($player);
        if($pk instanceof InventoryTransactionPacket){
          if($pk->trData instanceof UseItemOnEntityTransactionData){
            $eid = $pk->trData->getActorRuntimeId();
            if(!isset($this->eid[$field])) return false;
            if($eid === $this->eid[$field]){
              $this->sendChooseForm($player);
            }  
          }
        }
    }

    public function sendChooseForm($player){
    $buttons = [['text' => '倉庫を開く'], ['text' => 'shopで買い物をする'], ['text' => '装備を売却する']];
      $data = [
			    'type' => 'form',
			    'title' => '§lshop',
			    'content' => '何をしますか？',
			    'buttons' => $buttons
	   ];
      $this->sendForm($player, $data, self::CHOOSE_FORM_ID);
    }

    public function sendShopForm($player){
      $buttons = [];
      $data = $this->plugin->shopData;
      foreach($data as $d){
        $name = ItemManager::getItem($d[0])->getName();
        $buttons[] = ['text' => $name."\n".$d[1].""];
      }
      $data = [
			    'type' => 'form',
			    'title' => '§lshop',
			    'content' => '何を購入しますか？',
			    'buttons' => $buttons
	   ];
      $this->sendForm($player, $data, self::SHOP_FORM_ID);
    }

    public function sendCountForm($dat, $player){
      $this->choose[$player->getName()] = $dat;
      $content = [];
      $content[] = ["type" => "slider", "text" => "何個購入しますか?", "min" => 1, "max" => 64];
      $data = [
		    'type'=>'custom_form',
		    'title'   => "§lShop",
		    'content' => $content
	    ];
      $this->sendForm($player, $data, self::COUNT_FORM_ID);
    }

    public function receiveChooseForm($data, $player){
      if($data === 0){
        $player->sendMessage('この機能は現在使用できません。');
        /*$this->chest[$player->getName()] = true;
        $this->createSell($player);*/
      }elseif($data === 1){
        $this->sendShopForm($player);
      }elseif($data === 2){
        $inv = new ShopWindow();
        $player->setCurrentWindow($inv);
      }
    }

    public function receiveShopForm($data, $player){
       $d = $this->plugin->shopData[$this->choose[$player->getName()]];
       if($this->plugin->playermanager->takeMoney($player, $d[1] * $data[0])){
           $player->sendMessage('§dSHOP>>§f'.$d[1] * $data[0].'flになります！');
           $player->sendMessage('§dSHOP>>§fありがとうございました！');
           $item = ItemManager::getItem($d[0])->getItem();
           $item->setCount($data[0]);
           $player->getInventory()->addItem($item);
       }else{
        $player->sendMessage('§dSHOP>>§fお金が足りません。。');
       }
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
                  $this->receiveChooseForm($data, $player);
                  break;
                case self::SHOP_FORM_ID:
                  $this->sendCountForm($data, $player);
                  break;
                case self::COUNT_FORM_ID;
                  $this->receiveShopForm($data, $player);
                  break;
            }
        }
    }

    public function sendForm($player, $data, $id){
        $pk = new ModalFormRequestPacket;
        $pk->formId = $id;
        $pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function createSell($player){
      $field = $this->plugin->fieldmanager->getField($player);
      $chest = $this->plugin->fieldmanager->getLevel()->getTile($this->chest[$field]);
      //$chest = Tile::createTile(Tile::CHEST)
      if(!isset($chest)) $player->sendMessage('UUNKOO');
      //$nbt = Chest::createNBT($player->getPosition());
      $inv = new ChestInventory($chest->getPosition());
      $n = $player->setCurrentWindow($inv);
    }

    public function onClose(InventoryCloseEvent $event){
      $player = $event->getPlayer();
      $inv = $event->getInventory();
      //$name = $inv->getName();
      if($inv instanceof ChestInventory){
        $chest1 = $inv->getHolder();
        $field = $this->plugin->fieldmanager->getField($player);
        if(!isset($this->chest[$field])) return false;
        $chest2 = $this->plugin->fieldmanager->getLevel()->getTile($this->chest[$field]);
        if($chest1 === $chest2){
          /*if($this->chest[$name]){
            $this->chest[$name] = false;
            return true;
          }*/
          $items = $inv->getContents();
          $amount = 0;
          foreach($items as $item){
            $tag = $item->getNamedTagEntry(PveItem::TAG_ITEM);
            if(isset($tag)){
              $id = $tag->getTag(PveItem::TAG_ID)->getValue();  
              if(ItemManager::getItem($id)->isOre()){
                $inv->removeItem($item);
                $amount += (ItemManager::getItem($id)->getAmount()) * ($item->getCount());
                continue;
              }
            }
            $player->getInventory()->addItem($item);
          }
          $player->sendMessage('§eINFO§f>>鉱石を売却しました！');
          $this->plugin->playermanager->addMoney($player, $amount);
        }
      }
    }
}