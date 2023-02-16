<?php
namespace pve\npc;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use Ramsey\Uuid\UUID;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
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
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;

use pve\QuestManager;

class Npc implements Listener{

  public function __construct($plugin){
      $this->plugin = $plugin;
      $this->spawn();
  }

  public function spawn(){
      $this->data = $this->plugin->npcData;
      foreach($this->data as $field => $data){
          
          foreach($data as $id => $d){
              $this->eid[$field][$id] = Entity::nextRuntimeId();
              $this->uuid[$field][$id] = UUID::fromString(md5(uniqid(mt_rand(), true)));
              $this->pk[$field][$id] = $this->summon($d, $id, $field);
          }
      }
  }

  public function summon($data, $id, $field){
      $pk = new AddPlayerPacket();
      $pk->uuid = $this->uuid[$field][$id];
      $pk->username = $data['name'];
      $pk->actorRuntimeId = $this->eid[$field][$id];
      $pk->position = new Vector3($data['x'], $data['y'], $data['z']);
      $pk->motion = new Vector3(0, 0, 0);
      $pk->pitch = 0;
      $pk->yaw = $data['yaw'];
      $pk->headYaw = $data['yaw'];
      $pk->item = ItemStackWrapper::legacy(ItemStack::null());
      $pk->gameMode = 0;
      $pk->syncedProperties = new PropertySyncData([1], [1.0]);
      $pk2 = UpdateAbilitiesPacket::create(0, 0, $this->eid[$field][$id], []);
      $pk->abilitiesPacket = $pk2;
      $meta = new EntityMetadataCollection();
      $meta->setByte(EM::ALWAYS_SHOW_NAMETAG, 1);
      $meta->setString(EM::NAMETAG, $data['name']);
      $meta->setLong(EM::LEAD_HOLDER_EID, -1);
      $meta->setFloat(EM::SCALE, 1);
	    $pk->metadata = $meta -> getAll();
        /*[
          Entity::DATA_FLAGS => 
              [
                  Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG ^ 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG
              ],
          Entity::DATA_NAMETAG => 
              [
                  Entity::DATA_TYPE_STRING, $data['name']
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

  public function remove($player, $field){
    if(!isset($this->data[$field])) return false;
    foreach($this->data[$field] as $id => $data){
      $pk = new RemoveActorPacket();
      $pk->actorUniqueId = $this->eid[$field][$id];
      $player->getNetworkSession()->sendDataPacket($pk);
    }
  }

  public function sendPacket($player, $field){
    if(!isset($this->data[$field])) return false;
      foreach($this->data[$field] as $id => $data){
        $player->getNetworkSession()->sendDataPacket($this->pk[$field][$id]);
        $skin = $this->plugin->mob->Skin;
        if(!isset($skin[$data['name']])) continue;
        $pk2 = new PlayerSkinPacket();
        $pk2->uuid = $this->uuid[$field][$id];
        $pk2->skin = SkinAdapterSingleton::get()->toSkinData($skin[$data['name']]);
        $player->getNetworkSession()->sendDataPacket($pk2);
      }
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
        $this->onTouch($player, $field, $eid);   
      }
    }
  }

    public function onTouch($player, $field, $eid){
        $id = array_search($eid, $this->eid[$field]);
        if($id === false) return true;
        if($this->data[$field][$id]['set'] === 'message'){
          $player->sendMessage('§a'.$this->data[$field][$id]['name'].'§f>>'.$this->data[$field][$id]['message']);
        }else{
          QuestManager::get($this->data[$field][$id]['id'])->onTouch($player, $this->data[$field][$id]['name']);
        }
    }
}
    
