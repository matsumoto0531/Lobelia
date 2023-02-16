<?php
namespace pve;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\UUID;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\entity\Skin;

use pve\WeaponManager;
use pve\ArmorManager;
use pve\weapon\Weapon;
use pve\armor\Armor;
use pve\packet\CameraShakePacket;
use pve\packet\AnimateEntityPacket;

class PVECommand extends Command{

	const NAME = "pve";
	const DESCRIPTION = "武器ショップを開くコマンドです";
	const USAGE = "";
	
	const PERMISSION = "pve";
	
	protected $plugin;

    public function __construct(Main $plugin)
    {
		parent::__construct(static::NAME, static::DESCRIPTION, static::USAGE);
		//parent::__construct("guild", static::DESCRIPTION, static::USAGE);

        $this->setPermission(static::PERMISSION);

        $this->plugin = $plugin;
    }
    
	public function execute(CommandSender $sender, string $label, array $args) : bool {
		if(!$this->plugin->isEnabled())
        {
            return false;
        }
        if(!$this->testPermission($sender))
        {
            return false;
        }
		
		if(!$sender instanceof Player){
			$sender->sendMessage('コンソールからは実行できません');
			return true;
		}
		
		if(!isset($args[0])){
		  $this->plugin->setting->sendSettingForm($sender);
		  return true;
		}
		
		if($args[0] === 'skin'){
		  if(!isset($args[1])){
		    $sender->sendMessage('モブ名を設定してください');
		    return false;
		  }
		  $this->plugin->mob->Skin[$args[1]] = $sender->getSkin();
		  $sender->sendMessage($args[1].'のスキンを設定しました');
		  return true;
		}

        if($args[0] === 'forge'){
			$field = $this->plugin->fieldmanager->getField($sender);
			$this->plugin->forgeData[$field] = ['x' => $sender->getX(), 'y' => $sender->getY(), 'z' => $sender->getZ(), 'yaw' => $sender->lastYaw];
			$this->plugin->forge->removeAll($field);
			$this->plugin->forge->spawn();
			$sender->sendMessage('設定が完了しました！');
			return true;
		}

		if($args[0] === 'shop'){
			$field = $this->plugin->fieldmanager->getField($sender);
			$this->plugin->shopperData[$field] = ['x' => $sender->getX(), 'y' => $sender->getY(), 'z' => $sender->getZ(), 'yaw' => $sender->lastYaw];
			$this->plugin->shop->removeAll($field);
			$this->plugin->shop->spawn();
			$sender->sendMessage('設定が完了しました！');
			return true;
		}

		if($args[0] === 'tp'){
			if(!isset($args[1])){
			  $sender->sendMessage('tp先のフィールドを設定してください');
			  return false;
			}
			$this->plugin->fieldtp->set($sender, $args[1]);
			$sender->sendMessage('設定したい場所をタッチしてください。');

			return true;
		}	

		if($args[0] === 'dtp'){
			if(!isset($args[1])){
			  $sender->sendMessage('tp先のフィールドを設定してください');
			  return false;
			}
			$this->plugin->dungeontp->set($sender, $args[1]);
			$sender->sendMessage('設定したい場所をタッチしてください。');

			return true;
		}
		
		if($args[0] === 'npc'){
			if(!isset($args[1])){
			  $sender->sendMessage('npcの名前を入れてください。');
			  return false;
			}
			if(!isset($args[2])){
				$sender->sendMessage('メッセージを入力してください。');
				return false;
			}
			$field = $this->plugin->fieldmanager->getField($sender);
			$this->plugin->npcData[$field][] = ['x' => $sender->getPosition()->getX(), 'y' => $sender->getPosition()->getY(), 'z' => $sender->getPosition()->getZ(), 'yaw' => $sender->getLocation()->getYaw(), 'name' => $args[1], 'message' => $args[2], 'set' => 'message'];
			$this->plugin->npc->spawn();

			$sender->sendMessage('設定が完了しました。');

			return true;
		}

		if($args[0] === 'weapon'){
			if(!isset($args[1])){
			  $sender->sendMessage('武器のIDを入れてください。');
			  return false;
			}
			if(!isset($args[2]) or !isset($args[3])){
				$sender->sendMessage('RANK_IDを入力して下さい。');
				return false;
			}
			$class = WeaponManager::getWeapon();
			$bairitu = [1000, 1000, 1000, 1000, 1000];
			$item = $class->getItem($args[1], $bairitu, $args[3]); 
			$sender->getInventory()->addItem($item);

			$sender->sendMessage('武器を送信しました。');

			return true;
		}

		if($args[0] === 'armor'){
			if(!isset($args[1])){
			  $sender->sendMessage('防具のIDを入れてください。');
			  return false;
			}
			if(!isset($args[2])){
				$sender->sendMessage('RANK_IDを入力して下さい。');
				return false;
			}
            $class = ArmorManager::getArmor();
			$item = $class->getItem($args[1], $args[2], $args[3], $args[4], $args[5]);
			if($args[2] >= 1.3)
			  $class->setOnlySkill($item, $args[1]); 
			$sender->getInventory()->addItem($item);

			$sender->sendMessage('防具を送信しました。');

			return true;
		}

		if($args[0] === 'particle'){
			if(!isset($args[1])){
			  $sender->sendMessage('防具のIDを入れてください。');
			  return false;
			}
			$pk3 = new SpawnParticleEffectPacket();
            $pk3->position = $sender->asVector3();
            $pk3->particleName = $args[1];
			
			$sender->dataPacket($pk3);


			return true;
		}

		if($args[0] === 'animation'){
			if(!isset($args[1])){
			  $sender->sendMessage('防具のIDを入れてください。');
			  return false;
			}
			$pk = new AnimateEntityPacket();
            $pk->animation = $args[1];
			$pk->nextState = "none";
			$pk->stopExpression = "query.is_sneaking";
			$pk->controller = "";
			$pk->blendOutTime = 5.0;
			$pk->actorRuntimeIds = [(int)$args[2]];
			$sender->sendMessage($sender->getId().'');
			$sender->dataPacket($pk);
			return true;
		}

		if($args[0] === 'camera'){
			if(!isset($args[1])){
			  $sender->sendMessage('防具のIDを入れてください。');
			  return false;
			}
			$pk = new CameraShakePacket();
			$pk->intensity = 1.0;
			$pk->duration = (float)$args[2];
			$pk->shakeType = (int)$args[1];
			$sender->sendMessage($sender->getId().'');
			$sender->dataPacket($pk);
			return true;
		}

		if($args[0] === 'summon'){
			$pk = new AddPlayerPacket();
			$uuid = UUID::fromString(md5(uniqid(mt_rand(), true)));
	        $pk->uuid = $uuid;
			$pk->username = 'aaa';
			$eid = Entity::$entityCount++;
	        $pk->entityRuntimeId = Entity::$entityCount++;
	        $pk->position = $sender->getPosition();
	        $pk->motion = new Vector3(0, 0, 0);
	        $pk->yaw = 0;
	        $pk->pitch = 0;
	        $pk->item = Item::get(0, 0, 0);
	        $pk->metadata =
	        [
		        Entity::DATA_FLAGS => 
				[
					Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG ^ 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG
				],
		        Entity::DATA_NAMETAG => 
				[
					Entity::DATA_TYPE_STRING, '§f[§elv.§f0]'
				],
		        Entity::DATA_LEAD_HOLDER_EID => 
				[
					Entity::DATA_TYPE_LONG, -1
				],
		        Entity::DATA_SCALE => 
				[
					Entity::DATA_TYPE_FLOAT,1
			    ]
			];
			$sender->dataPacket($pk);
			$pk2 = new PlayerSkinPacket();
            $pk2->uuid = $uuid;
            $pk2->skin = SkinAdapterSingleton::get()->toSkinData($this->plugin->entity->getSkin());
			$sender->dataPacket($pk2);
			$sender->sendMessage($eid.'');
			return true;
		}

		if($args[0] === 'customSkin'){
			if(!isset($args[1])){
				$sender->sendMessage('防具のIDを入れてください。');
				return false;
			}
			$this->plugin->mob->Skin[$args[1]] = $this->plugin->entity->getSkin($args[2], $args[3], $args[4], $args[5]);
		    $sender->sendMessage($args[1].'のスキンを設定しました');
		    return true;
		}
		if($args[0] === 'money'){
			if(!isset($args[1]) or !isset($args[2]) or !isset($args[3])){
				$sender->sendMessage('/pve money [add/take] name amount');
				return false;
			}
			$player = $this->plugin->getServer()->getPlayerExact($args[2]);
			if(!isset($player)) $sender->sendMessage('プレイヤーが存在しません。名前は正確に入力してください。');
			if($args[1] === 'add') $this->plugin->playermanager->addMoney($player, $args[3], false);
			else{
				if(!$this->plugin->playermanager->hasMoney($player, $args[3])){
					$sender->sendMessage('§eINFO>>§fお金が足りません!');
					return false;
				}
				$this->plugin->playermanager->takeMoney($player, $args[3]);
			}
			$player->sendMessage($sender->getName().'によってお金が操作されました！');
			$sender->sendMessage('処理が完了しました！');
		    return true;
		}
		if($args[0] === 'questnpc'){
			if(!isset($args[1])){
			  $sender->sendMessage('npcの名前を入れてください。');
			  return false;
			}
			if(!isset($args[2])){
				$sender->sendMessage('IDを入力してください。');
				return false;
			}
			$field = $this->plugin->fieldmanager->getField($sender);
			$this->plugin->npcData[$field][] = ['x' => $sender->getPosition()->getX(), 'y' => $sender->getPosition()->getY(), 'z' => $sender->getPosition()->getZ(), 'yaw' => $sender->getLocation()->getYaw(), 'name' => $args[1], 'id' => $args[2], 'set' => 'quest'];
			$this->plugin->npc->spawn();
	
			$sender->sendMessage('設定が完了しました。');
	
			return true;
		}
		if($args[0] === 'dungeon'){
			if(!$this->plugin->party->isLeader($sender)){
			  $sender->sendMessage('パーティーリーダー以外は実行できません。');
			  return false;
			}
			$players = $this->plugin->party->getPlayers($sender);
			$this->plugin->dungeon->onStart($players, $args[1]);
			return true;
		}
		if($args[0] === 'pt'){
			$this->plugin->playerData[$sender->getName()]['point'] += $args[1];
			return true;
		}
		if($args[0] === 'animate'){
			if(!isset($args[1])){
				$sender->sendMessage('animationの名前を入れてください。');
				return false;
			}
			$pk = new AnimateEntityPacket();
			switch($args[1]){
			  case('nakayubi'):
				$name = "animation.humanoid.nakayubi";
				break;
			  case('orbit'):
				$name = "animation.humanoid.orbit";
				break;
			}
			if(!isset($name)){
				$sender->sendMessage('何か間違っています。');
				return false;
			}
            $pk->animation = $name;
			$pk->nextState = "none";
			$pk->stopExpression = "query.is_sneaking";
			$pk->controller = "";
			$pk->blendOutTime = 0;
			$pk->actorRuntimeIds = [$sender->getId()];
			$field = $this->plugin->fieldmanager->getField($sender);
			$players = $this->plugin->fieldmanager->getPlayers($field);
			foreach($players as $player){
			  $player->dataPacket($pk);
			}
	
			return true;
		}
		if($args[0] === 'skill'){
			if(!isset($args[1]) or !isset($args[2]) or !isset($args[3])){
				$sender->sendMessage('スキルid/レベル/場所をいれてください。');
				return false;
			}
			$item = $sender->getInventory()->getItemInHand();
			$item = ArmorManager::getArmor()->setSkill($item, $args[1], $args[2], $args[3]);
			$sender->getInventory()->setItemInHand($item);
			return true;
		}
		if($args[0] === 'wsk'){
			if(!isset($args[1])){
				$sender->sendMessage('スキルidをいれてください。');
				return false;
			}
			$item = $sender->getInventory()->getItemInHand();
			$item = WeaponManager::getWeapon()->setSkill($item, $args[1]);
			$sender->getInventory()->setItemInHand($item);
			return true;
		}
		if($args[0] === 'kyouka'){
			if(!isset($args[1])){
				$sender->sendMessage('レベルを入れてください。');
				return false;
			}
			if($args[1] > 16){
				$sender->sendMessage('レベルを入れてください。');
				return false;
			}
			$item = $sender->getInventory()->getItemInHand();
			$tag = $item->getNamedTagEntry(Weapon::TAG_WEAPON);
			if(!isset($tag)) $tag = $item->getNamedTagEntry(Armor::TAG_ARMOR);
			$tag->setString(Armor::TAG_LEVEL, $args[1]);
			$item->setNamedTagEntry($tag);
			$sender->getInventory()->setItemInHand($item);
			
			return true;
		}
		if($args[0] === 'gift'){
			if(!isset($args[1])){
				$sender->sendMessage('階層を入れてください。');
				return false;
			}
			$this->plugin->dungeon->addArmor([$sender], 1, $args[1]);
			$this->plugin->dungeon->addWeapon([$sender], 1, $args[1]);
			return true;
		}
		if($args[0] === 'home'){
			$sender->sendMessage('§bSorry§f>>このコマンドは使えません！');
			if(!isset($args[1]) or !isset($args[2])){
				$sender->sendMessage('USAGE>> /pve home [name] [count]');
				return false;
			}
			$this->plugin->homeData[$args[1]][$args[2]] = [
				'x' => $sender->x,
				'y' => $sender->y,
				'z' => $sender->z,
				'field' => $this->plugin->fieldmanager->getField($sender)
 			];
			return true;
		}
		if($args[0] === 'entity'){
			$level = $sender->getLevel();
			$entities = $level->getEntities();
			foreach($entities as $entity){
				$entity->kill();
			}
		}
		if($args[0] === 'text'){
			$pk = new TextPacket();
			$pk->type = (int)$args[1];
			$pk->sourceName = 'aaa';
			$pk->message = 'aaa';
			$pk->parameters = ['aaa'];
			$sender->dataPacket($pk);
			return true;
		}
		
		$sender->sendMessage('なんかミスってます。');
		return false;
	}
}
