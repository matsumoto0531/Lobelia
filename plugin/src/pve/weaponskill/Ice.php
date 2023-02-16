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


class Ice extends WeaponSkill {
	
  const NAME = "§b氷槍";
  const ID = 6;
  const ITEM_ID = 264;
  const CT = 30;
	
  const DESCRIPTION = '氷の槍で攻撃する。';

  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $player->getPosition();
    $poss = $this->line($pos, deg2rad($player->yaw + 90), 10);
    $this->plugin->playermanager->addCrit($player, 30, 30);
    $this->addCustomParticle("PVE:STATUSUP", $pos, $players);
    $this->plugin->playermanager->setImmobile($player, 1.5);
    $this->addAnimate($players, $player->getId(), "animation.humanoid.ice");
    $atk = $this->plugin->mob->checkAtk($player);
    $type = $this->plugin->mob->getType($player->getInventory()->getItemInHand());
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $players, $atk, $type]), 9);
  }

  public function ActionTask($field, $poss, $pos, $player, $players, $atk, $type){
    $this->addDestroyParticles($field, $poss, Block::get(71));
    $this->addCustomParticle('PVE:ICEATTACK', $pos, $players);
    if($field == 'spawn')
      return false;
      if(!isset($this->plugin->mob->mobs[$field])) return false;
      $mobs = $this->plugin->mob->mobs[$field];
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      foreach($poss as $posi){
        if($this->distance($posi, $mpos) < 2){
          $this->plugin->mob->CustomAttack($atk * 3, $player, $field, $eid, $type);
          $this->plugin->mob->Freeze($field, $eid, 3, 1);
        }
    }
    }
  }
}