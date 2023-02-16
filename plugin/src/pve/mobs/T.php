<?php
namespace pve\mobs;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

use pve\Callback;

class T extends Mobs {

  const NAME = 'T';
  const HP = 150000;
  const ATK = 500;
  const DEF = 32000000;
  const EXP = 99999;
  const DROPS = [0 => 1, 1 => 1];
  
  public function move1($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player){
      $this->plugin->mob->addFloatingText($player, '君？', $player->getPosition());
      $player->sendMessage('§l[T]あの植木鉢おいたの誰？'); 
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'You'], [$field, 0, $eid]), 20);
  }
  
  public function You($field, $count, $eid){
    $count ++;
    if(!$this->plugin->mob->isAlive($field, $eid))return false;
    if($count > 3){
      $pk = new LevelSoundEventPacket();
      $pk->sound = LevelSoundEventPacket::SOUND_EXPLODE;
    
      $pk2 = new LevelEventPacket;
      $pk2->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_REDSTONE & 0xFFF;
      $pk2->data = 2;
      $players = $this->plugin->fieldmanager->getPlayers($field);
      foreach($players as $player){
	if($player->isSneaking()){
	  $this->plugin->mob->addFloatingText($player, '違います', $player->getPosition());
	  $player->sendMessage('<'.$player->getName().'>違います');
	  $player->addTitle('違います');
	  continue;
	}
	$pos = $player->getPosition();
	$pk->position = $pos;
	$pk2->position = $pos;
	$this->plugin->mob->mobAttack($eid, $field, $player);
	$player->dataPacket($pk);
	$player->dataPacket($pk2);
      }
      $this->plugin->mob->Move($field, $eid);
    }else{
      $players = $this->plugin->fieldmanager->getPlayers($field);
      foreach($players as $player){
	$pos = $player->getPosition();
	$pos->y += $count;
        $this->plugin->mob->addFloatingText($player, '君？', $pos);
	$player->sendMessage('§l[T]君？'); 
	$player->addTitle('君？', '', 1, 1, 1);
      }
      $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'You'], [$field, $count, $eid]), 20);
    }
  }
  
}
?>
