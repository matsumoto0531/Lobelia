<?php
namespace pve\mobs;

use pocketmine\network\mcpe\protocol\types\ParticleIds as Particle;
use pocketmine\world\Block;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\entity\EntityIds;
use pocketmine\world\Position;
use pocketmine\math\Vector3;

use pve\Callback;
use pve\Type;

class Akanthos extends Mobs {

  const NAME = 'Akanthos';
  const HP = 1500;
  const ATK = 40;
  const DEF = 53;
  const EXP = 150;
  const MONEY = 70;
  const DROPS = [45 => 4064, 46 => 664];
  const SWORD = [4 => 20];
  const ARMOR = [4 => 20];
  const ORB = [17 => 20];
  const ACC = [2 => 5];

  const TYPE = TYPE::ICE;

  public function move1($field, $eid){
    if(mt_rand(0, 100) > 10){
      $this->plugin->getScheduler()->scheduleDelayedTask(
        new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 6);
      return false;
    }
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE], $pos, $players);
    switch(mt_rand(0,1)){
      case 0:
        $this->moved1($field, $eid);
        break;
      case 1:
        $this->moved2($field, $eid);
        break;
    }
  }

  public function moved1($field, $eid){
    $skillname = '§b氷嵐';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 5, 5);
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 4, 40);
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

  public function moved2($field, $eid){
    $skillname = '§b氷拳';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $poss = [];
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $target = $this->plugin->mob->getTarget($field, $eid);
    $x = $target->getPosition()->getX() - $pos->x;
    $yaw = atan(($target->getPosition()->getZ() - $pos->z)/$x);
    if($x < 0) $yaw += M_PI;
    $poss = array_merge($poss, $this->line($pos, $yaw, 7));
    $poss = array_merge($poss, $this->line($pos, $yaw-(M_PI/3), 7));
    $poss = array_merge($poss, $this->line($pos, $yaw+(M_PI/3), 7));
    $posd = array_chunk($poss, count($poss)/40);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 6, 40);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos]), 24);
  }

  public function attackmove2($field, $eid, $poss, $pos){
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
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
  }
}