<?php
namespace pve\weaponskill;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\math\Vector3;
use pve\Callback;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;


class Thunder extends WeaponSkill {
	
	const NAME = "§e轟雷";
	const ID = 1;
  const ITEM_ID = 264;
  const CT = 30;
	
  const DESCRIPTION = 'あたりにすさまじい雷が降り注いで敵を攻撃する';

  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $pos = $player->getPosition();
    $poss = $this->circle($pos, 5, 5);
    //$posd = array_chunk($poss, count($poss)/8);
    //foreach($posd as $p)
      //$this->setColors($p, $this->plugin->fieldmanager->level, 4);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player]), 1);
  }

  public function ActionTask($field, $poss, $pos, $player){
    $this->addParticle($field, $pos, Particle::TYPE_EXPLODE, 0, 7);
    $this->addActors($field, $poss, "minecraft:lightning_bolt", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE);
    if($field == 'spawn')
      return false;
    if(!isset($this->plugin->mob->mobs[$field])) return false;
    $mobs = $this->plugin->mob->mobs[$field];
    
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      if($this->distance($pos, $mpos) < 20){
        $atk = $this->plugin->mob->checkAtk($player, $eid);
        $this->plugin->mob->CustomAttack($atk * 20, $player, $field, $eid);
        $player->sendMessage('§e§lHIT!!');
      }
    }
  }
}