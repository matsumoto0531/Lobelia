<?php
namespace pve\mobs;

use pocketmine\level\particle\Particle;
use pocketmine\block\Block;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\entity\EntityIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

use pve\Callback;
use pve\Type;

class Golem extends Mobs {

  const NAME = 'Golem';
  const HP = 75000;
  const ATK = 132;
  const DEF = 160;
  const EXP = 2000;
  const MONEY = 20000;
  const DROPS = [29 => 2764, 30 => 364];

  const TYPE = TYPE::DARK;

  public function move1($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE], $pos, $players);
    switch(mt_rand(0,2)){
      case 0:
        $this->moved1($field, $eid);
        break;
      case 1:
        $this->moved2($field, $eid);
        break;
      case 2:
        $this->moved3($field, $eid);
        break;
    }
  }

  public function moved1($field, $eid){
    $skillname = '§e地ならし';
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 10, 10);
    $posd = array_chunk($poss, count($poss)/24);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 4, 40);
    $this->addAnimate($players, $eid, "animation.golem.attacking");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $pos, $poss]), 24);
  }

  public function attackmove1($field, $eid, $pos, $poss){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
        if($this->isHit($player))
          $this->plugin->mob->mobAttack($eid, $field, $player, 9);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 50);
  }

  public function moved2($field, $eid){
    $skillname = '§eパンチ';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $poss = [];
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $target = $this->plugin->mob->getTarget($field, $eid);
    $x = $target->x - $pos->x;
    $yaw = atan(($target->z - $pos->z)/$x);
    if($x < 0) $yaw += M_PI;
    $poss = array_merge($poss, $this->line($pos, $yaw, 7));
    $poss = array_merge($poss, $this->line($pos, $yaw-(M_PI/3), 7));
    $poss = array_merge($poss, $this->line($pos, $yaw+(M_PI/3), 7));
    $posd = array_chunk($poss, count($poss)/40);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
    $this->addAnimate($players, $eid, "animation.golem.attacking");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos]), 24);
  }

  public function attackmove2($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
        if($this->isHit($player))
          $this->plugin->mob->mobAttack($eid, $field, $player, 7);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 50);
  }

  public function moved3($field, $eid){
    $skillname = '§e鉄壁';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 5, 5);
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 1, 40);
    $this->addAnimate($players, $eid, "animation.defup");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $poss, $pos]), 24);
  }

  public function attackmove3($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    $this->addCustomParticle('PVE:STATUSUP', $pos, $players);
    $this->plugin->mob->addDef($field, $eid, 100, 30);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 50);
  }

  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 13);
  }

  public function onMove($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addAnimate($players, $eid, "animation.golem.walking");
  }


}