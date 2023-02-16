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


class Fire extends WeaponSkill {
	
  const NAME = "§c爆炎";
  const ID = 3;
  const ITEM_ID = 264;
  const CT = 30;
	
  const DESCRIPTION = '燃え盛る炎で相手に継続ダメージを与える';

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
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $players, $atk, $type]), 15);
  }

  public function ActionTask($field, $poss, $pos, $player, $players, $atk, $type){
    $posd = [];
    for($i = 0; $i < 10; $i++){
      $posd[] = $poss[mt_rand(0, count($poss)-1)];
    }
    $this->addDestroyParticles($field, $posd, Block::get(213));
    $this->addParticle($field, $pos, Particle::TYPE_FLAME, 2, 7);
    $this->addCustomParticle('PVE:FIREATTACK', $pos, $players);
    if($field == 'spawn')
      return false;
      if(!isset($this->plugin->mob->mobs[$field])) return false;
      $mobs = $this->plugin->mob->mobs[$field];
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      if($this->distance($pos, $mpos) < 7){
        $this->plugin->mob->Bleed($player, $field, $eid, 10, $type, $atk * 2);
      }
    }
  }
}