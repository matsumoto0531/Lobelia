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


class Prominensu extends WeaponSkill {
	
  const NAME = "§c§lプロミネンス・サーガ";
  const ID = 14;
  const ITEM_ID = 264;
  const CT = 20;
	
  const DESCRIPTION = '太古の英雄譚。解読は難しそうだ。';

  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $player->getPosition();
    $poss = $this->circle($pos, 3, 3);
    $this->plugin->playermanager->setImmobile($player, 1.7);
    $this->addAnimate($players, $player->getId(), "animation.pve.fireplayer");
    $atk = $this->plugin->mob->checkAtk($player);
    $type = $this->plugin->mob->getType($player->getInventory()->getItemInHand());
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $players, $atk, $type, 0]), 15);
  }

  public function ActionTask($field, $poss, $pos, $player, $players, $atk, $type, $count){
    if($count > 7) return false;
    $posd = [];
    for($i = 0; $i < 10; $i++){
      $posd[] = $poss[mt_rand(0, count($poss)-1)];
    }
    $this->plugin->playermanager->addAtk($player, 200, 20);
    $this->addCustomParticle("PVE:STATUSUP", $player->getPosition(), $players);
    $this->addDestroyParticles($field, $posd, Block::get(213));
    $this->addParticle($field, $pos, Particle::TYPE_FLAME, 2, 7);
    $this->addCustomParticle('PVE:FIREATTACK', $pos, $players);
    if($field == 'spawn')
      return false;
      if(!isset($this->plugin->mob->mobs[$field])) return false;
      $mobs = $this->plugin->mob->mobs[$field];
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      if($this->distance($pos, $mpos) < 20){
        $this->plugin->mob->CustomAttack($atk, $player, $field, $eid, $type);
        $this->plugin->mob->Bleed($player, $field, $eid, 5, $type, $atk);
      }
    }
    $count ++;
    $this->plugin->getScheduler()->scheduleDelayedTask(
        new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $players, $atk, $type, $count]), 5);
  }
}