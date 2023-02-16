<?php
namespace pve\mobs;

use pocketmine\network\mcpe\protocol\types\ParticleIds as Particle;
use pocketmine\block\Block;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\entity\EntityIds;
use pocketmine\world\Position;
use pocketmine\math\Vector3;

use pve\Callback;
use pve\Type;

class Dahlia extends Mobs {

  const NAME = 'Dahlia';
  const HP = 2000;
  const ATK = 50;
  const DEF = 53;
  const EXP = 200;
  const MONEY = 100;
  const DROPS = [10 => 2064, 6 => 364];
  const SKILLS = [21 => 100];
  const SWORD_RECIPE = [5 => 10];
  const ARMOR_RECIPE = [5 => 40];
  const ORB_RECIPE = [5 => 10];
  const SWORD = [5 => 20];
  const ARMOR = [5 => 20];
  const ORB = [7 => 20, 13 => 20];
  const ACC = [3 => 5];

  const moves = [
    [90, 6, 0, 4],
    [20, 10, 60, 10],
    [70, 0, 20, 10],
    [20, 60, 10, 10]
  ];



  const TYPE = TYPE::WIND;

  public function move1($field, $eid){
    if(!isset($this->final[$eid])) $this->final[$eid] = 0;
    $kakuritu = self::moves[$this->final[$eid]];
    $rand = mt_rand(0, 100);
    for($i = 0; $i < 4; $i++){
      if($rand <= $kakuritu[$i]) break;
      $rand -= $kakuritu[$i];
    }
    switch($i){
      case 0:
        $this->plugin->getScheduler()->scheduleDelayedTask(
          new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 6);
        $this->final[$eid] = 0;
        return false;
        break;
      case 1:
        $this->moved1($field, $eid);
        $this->final[$eid] = 1;
        break;
      case 2:
        $this->moved2($field, $eid);
        $this->final[$eid] = 2;
        break;
      case 3:
        $this->moved3($field, $eid);
        $this->final[$eid] = 3;
        break;
    }
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE], $pos, $players);
    $this->addAnimate($players, $eid, "animation.pve.attack");
    
  }

  public function moved1($field, $eid){
    $skillname = '§e豪風';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 5, 5);
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
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
        $this->plugin->mob->mobAttack($eid, $field, $player, 3);
      }
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 20);
  }

  public function moved2($field, $eid){
    $skillname = '§e旋風';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 10, 10);
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 4, 45);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos]), 30);
  }

  public function attackmove2($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::DRAGON_DESTROY_BLOCK, 2, 7);
    $this->addSound($field, $pos, LevelSoundEvent::ATTACK_NODAMAGE); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      if($this->isHit($player, $poss)){
        $this->plugin->mob->mobAttack($eid, $field, $player, 4);
      }
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
  }

  public function moved3($field, $eid){
    $skillname = '§e暴風';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 10, 10);
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 6, 45);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $poss, $pos]), 30);
  }

  public function attackmove3($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::DRAGON_DESTROY_BLOCK, 2, 7);
    $this->addSound($field, $pos, LevelSoundEvent::ATTACK_NODAMAGE); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      if($this->isHit($player, $poss)){
        $this->plugin->mob->mobAttack($eid, $field, $player, 4);
      }
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
  }

  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 4, true);
  }


}