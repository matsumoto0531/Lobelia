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


class Ezerro extends WeaponSkill {
	
  const NAME = "§0§lEz-§cERROR";
  const ID = 17;
  const ITEM_ID = 264;
  const CT = 60;
	
  const DESCRIPTION = '全身全霊の力で攻撃する。';

  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $pos = $player->getPosition();
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $atk = $this->plugin->playermanager->getAtk($player);
    $this->plugin->playermanager->addAtk($player, $atk * 4, 5);
    $this->addCustomParticle("PVE:STATUSUP", $player->getPosition(), $players);
    $type = $this->plugin->mob->getType($player->getInventory()->getItemInHand());
  }

  public function ActionTask($field, $poss, $pos, $player, $players, $atk, $type){
    $posd = array_chunk($poss, count($poss)/4);
    $this->addParticles($field, $posd[0], Particle::TYPE_DRAGON_DESTROY_BLOCK, 2);
    $this->addCustomParticle('PVE:THUNDERATTACK', $pos, $players);
    if($field == 'spawn')
      return false;
    if(!isset($this->plugin->mob->mobs[$field])) return false;
    $mobs = $this->plugin->mob->mobs[$field];
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      if($this->distance($pos, $mpos) < 3){
        $this->plugin->mob->CustomAttack($atk * 8, $player, $field, $eid, $type);
      }
    }
  }
}