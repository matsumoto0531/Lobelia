<?php
namespace pve\animation;

use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pve\packet\AnimateEntityPacket;
use Ramsey\Uuid\Uuid;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\item\Item;

use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties as EM;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;

use pve\Callback;

class AddAnimation {

    public function __construct($plugin){
        $this->plugin = $plugin;
    }

    public function addAnimation($player, $animate, $time){
        $field = $this->plugin->fieldmanager->getField($player);
        $players = $this->plugin->fieldmanager->getPlayers($field);
        $data = [$player->getPosition(), $player->yaw, $player->pitch];
        $this->data[$player->getName()] = $data;
        $this->summon($player, $players);
        $this->backteleport($player);
        $player->setInvisible(true);
        $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'addAnimate'], [$player, $players, $animate]), 3);
        $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'remove'], [$player, $players]), $time);
        $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'tp'], [$player, $data]), $time + 5);
    }

    public function backteleport($player){
        $level = $this->plugin->fieldmanager->getLevel();
        $pos = $player->getPosition();
        $pos->y += 4;
        for($i = 0; $i < 4; $i++){
          $x = 0.5 * sin(deg2rad($player->yaw));
          $z = 0.5 * cos(deg2rad($player->yaw));
          $pos2 = new Vector3($pos->x + $x, $player->y + 4, $pos->z - $z);
		  $block = $level->getBlock($pos2);
		  if(!$block->canPassThrough()){
            $player->teleport($pos, $player->yaw, 60);
            return false;
          }
          $pos = $pos2;
        }
        $pos2->y -= 1;
        $player->teleport($pos2, $player->yaw, 60);
    }

    public function moveDummy($player, $pos, $yaw = null, $pitch = null){
        if(!isset($this->data[$player->getName()])) return false;
        $name = $player->getName();
        if(isset($yaw))
          $this->data[$name][1] = $yaw;
        if(isset($pitch))
          $this->data[$name][2] = $pitch;
        $pos->y += 1.6;
        $pk = new MovePlayerPacket();
        $pk->position = $pos;
        $pk->entityRuntimeId = $this->eid[$name];
        $pk->pitch = $this->data[$name][2];
        $pk->yaw = $this->data[$name][1];
        $pk->headYaw = $this->data[$name][1];
        $field = $this->plugin->fieldmanager->getField($player);
        $players = $this->plugin->fieldmanager->getPlayers($field);
        foreach($players as $playerd){
            $playerd->dataPacket($pk);
        }
        $pos->y -= 1.5;
        $this->pos[$name] = $pos;
    }

    public function tp($player, $data){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        $player->teleport($this->pos[$name], $data[1], $data[2]);
        $player->setInvisible(false);
        unset($this->data[$name]);
        unset($this->eid[$name]);
        unset($this->uuid[$name]);
        unset($this->pos[$name]);
    }

    public function addAnimate($player, $players, $animate){
        $pk = new AnimateEntityPacket();
        $pk->animation = $animate;
        $pk->nextState = "none";
        $pk->stopExpression = "query.is_sneaking";//"query.anim_time > 1";
        $pk->controller = "";
        $pk->blendOutTime = 0;
        $pk->actorRuntimeIds = [$this->eid[$player->getName()]];
        foreach($players as $p){
          $p->dataPacket($pk);
        }
    }

    public function summon($player, $players){
        $name = $player->getName();
        $this->uuid[$name] = UUID::fromString(md5(uniqid(mt_rand(), true)));
        $this->eid[$name] = Entity::nextRuntimeId();
        $pk = new AddPlayerPacket();
        $pk->uuid = $this->uuid[$name];
        $pk->username = $name;
        $pk->actorRuntimeId = $this->eid[$name];
        $pk->actorUniqueId = $this->eid[$name];
        $pk->position = $player->getPosition();
        $pk->motion = new Vector3(0, 0, 0);
        $pk->yaw = $player->yaw;
        $pk->pitch = $player->pitch;
        $pk->item = ItemStackWrapper::legacy(ItemStack::null());
        $pk->gameMode = 0;
        $adp = new AdventureSettingsPacket();
        $adp->targetActorUniqueId = 0;
        $pk->adventureSettingsPacket = $adp;
        $meta = new EntityMetadataCollection();
        $meta->setByte(EM::ALWAYS_SHOW_NAMETAG, 1);
        $meta->setString(EM::NAMETAG, $name);
        $meta->setLong(EM::LEAD_HOLDER_EID, -1);
        $meta->setFloat(EM::SCALE, 1);
        $meta->setFloat(EM::BOUNDING_BOX_WIDTH, 1);
        $meta->setFloat(EM::BOUNDING_BOX_HEIGHT, 2);
	    $pk->metadata = $meta->getAll();
        /*$pk->metadata =
        [
            Entity::DATA_FLAGS => 
                [
                    Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG ^ 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG
                ],
            Entity::DATA_NAMETAG => 
                [
                    Entity::DATA_TYPE_STRING, $name
                ],
            Entity::DATA_LEAD_HOLDER_EID => 
                [
                    Entity::DATA_TYPE_LONG, -1
                ],
            Entity::DATA_SCALE => 
                [
                    Entity::DATA_TYPE_FLOAT,1
          ],
        Entity::DATA_BOUNDING_BOX_WIDTH => 
                [
                    Entity::DATA_TYPE_FLOAT,1.0
          ],
        Entity::DATA_BOUNDING_BOX_HEIGHT => 
                [
                    Entity::DATA_TYPE_FLOAT,2.0
                ]
        ];*/
        $pk2 = new PlayerSkinPacket();
        $pk2->uuid = $this->uuid[$name];
        $pk2->skin = SkinAdapterSingleton::get()->toSkinData($player->getSkin());
        foreach($players as $p){
          $p->dataPacket($pk);
          $p->dataPacket($pk2);
        }
        $this->pos[$name] = $player->getPosition();
    }

    public function remove($player, $players){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->eid[$player->getName()];
        foreach($players as $p){
            $p->dataPacket($pk);
        }
    }

    public function getPos($player){
        $name = $player->getName();
        $result = $player->getPosition();
        if(isset($this->pos[$name]))
          $result = $this->pos[$name];
        return $result;
    }

    public function getYaw($player){
        $name = $player->getName();
        $result = $player->getYaw();
        if(isset($this->data[$name][1])){
            $result = $this->data[$name][1];
        }
        return $result;
    }

    public function CancelAnimation($player){
        if(!isset($this->data[$player->getName()])) return false;
        $field = $this->plugin->fieldmanager->getField($player);
        $players = $this->plugin->fieldmanager->getPlayers($field);
        $this->remove($player, $players);
        $this->tp($player, $this->data[$player->getName()]);
    }

}