<?php
namespace pve\weaponskill;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pve\Callback;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;


class Kamae extends WeaponSkill {
	
  const NAME = "§b剣術: 構え";
  const ID = 18;
  const ITEM_ID = 264;
  const CT = 30;
  const TP = 5;
	
  const DESCRIPTION = "基本の型をとる。\nしゅうちゅうりょくとはんだんりょくが上がる。";


  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $pos = $player->getPosition();
    $poss = $this->circle($pos, 5, 5);
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $players]), 1);
  }

  public function ActionTask($field, $poss, $pos, $player, $players){
    $this->addCustomParticle('PVE:STATUSUP', $pos, $players);
    $this->plugin->playermanager->addHan($player, 10, 10);
    $this->plugin->playermanager->addSyu($player, 10, 10);
  }
}