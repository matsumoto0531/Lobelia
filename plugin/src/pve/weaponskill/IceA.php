<?php
namespace pve\weaponskill;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pve\Callback;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;


class IceA extends WeaponSkill {
	
  const NAME = "§c§l凍壊";
  const ID = 10;
  const ITEM_ID = 264;
  const CT = 30;
	
  const DESCRIPTION = '氷の槍で攻撃する。';


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
    $poss = $this->line($pos, deg2rad($player->yaw + 90), 10);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player]), 1);
  }

  public function ActionTask($field, $poss, $pos, $player){
    $this->addDestroyParticles($field, $poss, Block::get(71));
    if($field == 'spawn')
      return false;
      if(!isset($this->plugin->mob->mobs[$field])) return false;
      $mobs = $this->plugin->mob->mobs[$field];
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      foreach($poss as $posi){
        if($this->distance($posi, $mpos) < 5){
          $atk = $this->plugin->mob->checkAtk($player, $eid);
          $this->plugin->mob->CustomAttack($atk * 5, $player, $field, $eid);
          $this->plugin->mob->Freeze($field, $eid, 3, 1);
          $player->sendMessage('§bHIT!!');
          foreach($players as $p){
            $p->removeEffect(2);
            $p->removeEffect(15);
            $this->plugin->playermanager->addAtk($player, 500, 20);
          }
        }
    }
    }
  }
}