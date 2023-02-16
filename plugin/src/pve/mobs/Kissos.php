<?php
namespace pve\mobs;

use pocketmine\network\mcpe\protocol\types\ParticleIds as Particle;
use pocketmine\block\Block;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\entity\EntityIds;
use pocketmine\world\Position;
use pocketmine\math\Vector3;

use pve\Callback;
use pve\Type;

class Kissos extends Mobs {

  const NAME = 'Kissos';
  const HP = 1000;
  const ATK = 30;
  const DEF = 53;
  const EXP = 100;
  const MONEY = 50;
  const DROPS = [43 => 4064, 44 => 664];
  const SWORD = [3 => 20];
  const ARMOR = [3 => 20];
  const ORB = [18 => 20];
  const ACC = [1 => 5];


  const TYPE = TYPE::FIRE;

  public function move1($field, $eid){
    if(mt_rand(0, 100) > 10){
      $this->plugin->getScheduler()->scheduleDelayedTask(
        new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 6);
      return false;
    }

    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE], $pos, $players);
    $this->moved1($field, $eid);
  }

  public function moved1($field, $eid){
    $skillname = '§c火炎';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 5, 5);
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 6, 40);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $pos, $poss]), 30);
  }

  public function attackmove1($field, $eid, $pos, $poss){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::DRAGON_DESTROY_BLOCK, 2, 7);
    $this->addSound($field, $pos, LevelSoundEvent::ATTACK_NODAMAGE); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      if($this->isHit($player, $poss)){
        $this->plugin->mob->mobAttack($eid, $field, $player, 2);
      }
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 20);
  }

}