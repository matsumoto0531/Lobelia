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


class Curse extends WeaponSkill {
	
  const NAME = "§0呪闇";
  const ID = 2;
  const ITEM_ID = 264;
  const CT = 30;
	
  const DESCRIPTION = '呪いをかけて敵のステータスを下げる';


  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $pos = $player->getPosition();
    $poss = $this->line($pos, deg2rad($player->yaw + 90), 10);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player]), 1);
  }

  public function ActionTask($field, $poss, $pos, $player){
    $this->addParticle($field, $pos, Particle::TYPE_SMOKE, 2, 7);
    $this->addDestroyParticles($field, $poss, Block::get(112));
    if($field == 'spawn')
      return false;
      if(!isset($this->plugin->mob->mobs[$field])) return false;
      $mobs = $this->plugin->mob->mobs[$field];
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      foreach($poss as $posi){
        if($this->distance($posi, $mpos) < 2){
          $atk = $this->plugin->mob->checkAtk($player, $eid);
          $this->plugin->mob->CustomAttack($atk * 5, $player, $field, $eid);
          $this->plugin->mob->addAtk($field, $eid, -10);
          $player->sendMessage('§e§0HIT!!');
        }
    }
    }
  }
}