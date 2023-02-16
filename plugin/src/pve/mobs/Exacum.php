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

use pve\dungeon\DungeonManager;
use pve\Callback;
use pve\Type;

class Exacum extends Mobs {

  const NAME = 'Exacum';
  const HP = 20000;
  const ATK = 60;
  const DEF = 53;
  const EXP = 300;
  const MONEY = 100;
  const SWORD = [8 => 20];
  const ARMOR = [6 => 20];
  const ACC = [4 => 5];

  const moves = [
    [80, 7, 7, 6],
    [20, 10, 60, 10],
    [70, 0, 20, 10],
    [20, 60, 10, 10]
  ];

  const moves2 = [
    [80, 7, 7, 6, 0],
    [20, 10, 40, 10, 20],
    [50, 0, 20, 10, 20],
    [20, 40, 10, 10, 20],
    [100, 0, 0, 0, 0]
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
  }

  public function move2($field, $eid){
    if(!isset($this->final[$eid])) $this->final[$eid] = 0;
    $kakuritu = self::moves2[$this->final[$eid]];
    $rand = mt_rand(0, 100);
    for($i = 0; $i < 5; $i++){
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
      case 4:
        $this->moved4($field, $eid);
        $this->final[$eid] = 4;
        break;
    }
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE], $pos, $players);
  }

  public function moved1($field, $eid){
    $skillname = '§e翼撃';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $target = $this->plugin->mob->getTarget($field, $eid);
    $x = $target->getPosition()->getX() - $pos->x;
    $yaw = atan(($target->getPosition()->getZ() - $pos->z)/$x);
    $poss = [];
    if($x < 0) $yaw += M_PI;
    $poss = array_merge($poss, $this->line($pos, $yaw, 12));
    $poss = array_merge($poss, $this->line($pos, $yaw-(M_PI/2), 12));
    $poss = array_merge($poss, $this->line($pos, $yaw+(M_PI/2), 12));
    $poss = array_merge($poss, $this->line($pos, $yaw-(M_PI/3), 12));
    $poss = array_merge($poss, $this->line($pos, $yaw+(M_PI/3), 12));
    $poss = array_merge($poss, $this->line($pos, $yaw-(2*M_PI/3), 12));
    $poss = array_merge($poss, $this->line($pos, $yaw+(2*M_PI/3), 12));
    $posd = array_chunk($poss, count($poss)/80);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $pos, $poss]), 30);
  }

  public function attackmove1($field, $eid, $pos, $poss){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    for($i = 0; $i < 20; $i++){
      $this->addParticle($field, $poss[mt_rand(0, count($poss)-1)], Particle::DRAGON_DESTROY_BLOCK, 2, 7);
    }
    $this->makeSound($field, $pos, 'PVE:DRAGON'); 
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
    $skillname = '§e暴風';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $poss = [];
    $list = [5, 5, 6];
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
        $this->send($player, $skillname);
        $ppos = $player->getPosition();
        $temp = $this->circle(new Vector3($ppos->getX(), 3, $ppos->getZ()), 5, 5);
        $posd = array_chunk($temp, count($temp)/10);
        $chose = $list[mt_rand(0, 2)];
        foreach($posd as $p) $this->setColors($p, $this->plugin->fieldmanager->level, $chose, 45);
        $poss = array_merge($poss, $temp);
    }
    for($i = 0; $i < 6; $i++){
        $temp = $this->circle(new Vector3($pos->getX() + mt_rand(-15, 15), 3, $pos->getZ() + mt_rand(-15, 15)), 5, 5);
        $posd = array_chunk($temp, count($temp)/10);
        $chose = $list[mt_rand(0, 2)];
        foreach($posd as $p) $this->setColors($p, $this->plugin->fieldmanager->level, $chose, 45);
        $poss = array_merge($poss, $temp);
    };
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos]), 30);
  }

  public function attackmove2($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    for($i = 0; $i < 20; $i++){
      $this->addParticle($field, $poss[mt_rand(0, count($poss)-1)], Particle::DRAGON_DESTROY_BLOCK, 2, 7);
    }
    $this->makeSound($field, $pos, 'PVE:DRAGON');  
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
    $skillname = '§e爪撃';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $tpos = $this->plugin->mob->getTarget($field, $eid)->getPosition();
    $zzz = $this->tuibi($pos, $tpos);
    $kiten = end($zzz);
    $poss = [];
    for($i = 1; $i <= 12; $i++){
        $temp = $this->line($kiten, M_PI * $i/6, 12);
        $posd = array_chunk($temp, count($temp)/10);
        foreach($posd as $p) $this->setColors($p, $this->plugin->fieldmanager->level, 5, 45);
        $poss = array_merge($poss, $temp);
    }
    $temp = $this->circle($kiten, 3, 3);
    $posd = array_chunk($temp, count($temp)/3);
    foreach($posd as $p) $this->setColors($p, $this->plugin->fieldmanager->level, 4, 45);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $poss, $pos, $kiten]), 30);
  }

  public function attackmove3($field, $eid, $poss, $pos, $kiten){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    for($i = 0; $i < 20; $i++){
        $this->addParticle($field, $poss[mt_rand(0, count($poss)-1)], Particle::DRAGON_DESTROY_BLOCK, 2, 7);
    }    
    $this->makeSound($field, $pos, 'PVE:TUME');  
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      if($this->isHit($player, $poss)){
        $this->plugin->mob->mobAttack($eid, $field, $player, 4);
      }
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
  }

  public function moved4($field, $eid){
    $skillname = '§eブレス';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $target = $this->plugin->mob->getTarget($field, $eid);
    $x = $target->getPosition()->getX() - $pos->x;
    $yaw = atan(($target->getPosition()->getZ() - $pos->z)/$x);
    if($x < 0) $yaw += M_PI;
    $poss = [];
    $poss = array_merge($poss, $this->line($pos, $yaw, 17));
    for($i = 1; $i <= 2; $i++){
        $poss = array_merge($poss, $this->line($pos, $yaw-($i * M_PI/12), 17));
        $poss = array_merge($poss, $this->line($pos, $yaw+($i * M_PI/12), 17));
    }
    $posd = array_chunk($poss, count($poss)/80);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 14, 45);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove4'], [$field, $eid, $poss, $pos]), 30);
  }

  public function attackmove4($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    for($i = 0; $i < 20; $i++){
        $this->addParticle($field, $poss[mt_rand(0, count($poss)-1)], Particle::DRAGON_DESTROY_BLOCK, 2, 7);
    }    
    $this->makeSound($field, $pos, 'PVE:KAZEBURESU'); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      if($this->isHit($player, $poss)){
        $this->plugin->mob->mobAttack($eid, $field, $player, 10);
      }
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
  }

  public function movehalf($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    DungeonManager::getDungeonByPlayer($players[0])->addMobs($field, 'Dahlia', 3, 1, 1, 0.5);
    foreach($players as $player){
      $player->sendMessage('§a風は偏在する・・・');
    }
  }

  public function movequarter($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    DungeonManager::getDungeonByPlayer($players[0])->addMobs($field, 'Exacum', 1, 1, 1, 0.1);
    foreach($players as $player){
      $player->sendMessage('§aセカンド§cウィンド!!');
    }
  }


  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 4, true);
  }


}