<?php
namespace pve\weaponskill;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pve\Callback;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;


class LightA extends WeaponSkill {
	
  const NAME = "§c§l煌撃";
  const ID = 8;
  const ITEM_ID = 264;
  const CT = 30;
	
  const DESCRIPTION = '聖なる力の加護を受ける';


  public function Interact($weapon, $player){
      $name = $player->getName();
      if(!array_key_exists($name, $this->data))
      $this->data[$name] = 0;
      $time = microtime(true);
      $num = $time - $this->data[$name];
      if($num > self::CT){
        $this->data[$name] = $time;
        $this->Imposition($player);
        $this->send($player);
      }
  }

  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $pos = $player->getPosition();
    $poss = $this->circle($pos, 5, 5);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $count]), 1);
  }

  public function ActionTask($field, $poss, $pos, $player, $count = 0){
    if($count > 1) return false;
    $posd = [];
    for($i = 0; $i < 10; $i++){
      $posd[] = $poss[mt_rand(0, count($poss)-1)];
    }
    $this->addDestroyParticles($field, $posd, Block::get(89));
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    if($field == 'spawn')
      return false;
      if(!isset($this->plugin->mob->mobs[$field])) return false;
      $mobs = $this->plugin->mob->mobs[$field];
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      if($this->distance($pos, $mpos) < 15){
        $this->plugin->playermanager->addAtk($player, 3000, 3);
        $this->plugin->playermanager->addDef($player, 3000, 3);
        $atk = $this->plugin->mob->checkAtk($player, $eid);
        $this->plugin->mob->CustomAttack($atk * 5, $player, $field, $eid);
        $this->plugin->mob->Attack($player, $eid);
        $player->sendMessage('§eHIT!!');
        $this->plugin->playermanager->addHp($player, 10000);
      }
    }
    $count ++;
    $this->plugin->getScheduler()->scheduleDelayedTask(
        new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $count]), 10);
  }
}