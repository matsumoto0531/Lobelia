<?php
namespace pve;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\permission\DefaultPermissions;

use pocketmine\inventory\PlayerInventory;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\NBT;

use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;

use pve\weapon\Weapon;
use pve\armor\Armor;
use pve\accessory\Accessory;
use pve\skill\Skill;
use pve\ArmorManager;
use pve\item\ItemManager;
use pve\scoreboard\ScoreboardManager as SCM;
use pve\dungeon\Dungeon;
use pve\Callback;
use pve\SetBonusManager as SBM;
use pve\dungeon\DungeonManager;
use pve\prefix\Prefix;

class PlayerManager implements Listener {
	
	const NAME_ATK = 'atk';
	const NAME_DEF = 'def';
	const NAME_HP = 'hp';
	const NAME_MHP = 'mhp';
	const NAME_NOWHP = 'nhp';
	const NAME_SHARP = 'sharp';
	const NAME_CRIT = 'crit';
	const NAME_CRIT_PER = 'critper';
	const NAME_MINE = 'mine';
	const NAME_POW = 'pow';
	const NAME_AGI = 'agi';
	const NAME_HAN = 'han';
	const NAME_BODY = 'body';
	const NAME_SYU = 'syu';
	const NAME_MAGIC = 'magic';
	const NAME_STYLE = 'style';
	const NAME_ATKMULTI = 'amul';
	const NAME_DEFMULTI = 'dmul';
	const NAME_ATKFINAL = 'afin';
	const NAME_DEFFINAL = 'dfin';
	const NAME_ATKSPEED = 'aspeed';
	const NAME_ATKSPEEDMULTI = 'asmul';
    const NAME_TYPEMULTI = 'tmul';
	const NAME_TYPEDEFMULTI = 'tmuldef';
	
	const ATK_CONST = 10;
	const DEF_CONST = 10;
	const HP_CONST = 1000;
	
	const DATA_FIRST = ["slv" => 1, "sexp" => 0, "plv" => 1, "pexp" => 0, "blv" => 1, "bexp" => 0, "tlv" => 1, "texp" => 0, "money" => 0, "guild" => "", "atk" => 1, "def" => 1, "mhp" => 100, 'title' => [], 'rank' => 1, 'quest' => [],
						'pow' => 1, 'agi' => 1, 'han' => 1, 'body' => 1, 'syu' => 1, 'magic' => 1, 'style' => 'sword', 'point' => 0, 'skills' => [-1, -1, -1], 'shoji' => [], 'job' => [0, 0, 0]];
	
	const STATUS = ['pow', 'agi', 'han', 'body', 'syu', 'magic'];

	const STYLE_SWORD = 'sword';
	const STYLE_PI = 'pi';
	const STYLE_BOW = 'bow';
	const STYLE_MAGIC = 'stick';
	public static $class;
	
	public function __construct($plugin){
		$this->plugin = $plugin;
		$this->defdata = [];
		$this->damagedata = [];
		$this->cooltime = [];
		$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'TickTask'], []), 5);
		$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'setDpsdata'], []), 5);
		$this->TPTask();
		self::$class = $this;
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$inv = $player->getArmorInventory();
		$inv->getListeners()->add($this);
		if(!isset($this->plugin->playerData[$name])){
			$this->plugin->playerData[$name] = self::DATA_FIRST;
			$this->onFirst($player);
		}
		if(!isset($this->plugin->eventData[$name])){
			$this->plugin->eventData[$name] = true;
			$this->plugin->playerData[$name]['guild'] = '';
		}
		if($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
			if($this->plugin->playerData[$name]['slv'] >= 30){
				$this->plugin->playerData[$name]['slv'] = 1;
				$this->plugin->playerData[$name]['sexp'] = 0;
				$this->plugin->playerData[$name]['quest'] = [];
				$this->plugin->playerData[$name]['atk'] = 1;
				$this->plugin->playerData[$name]['def'] = 1;
				$this->plugin->playerData[$name]['mhp'] = 100;
				$this->plugin->playerData[$name]['money'] = 10000;
 			}
		}
		//if(!isset($this->data[$name]))
		
		$this->setdefData($this->plugin->playerData[$name], $player);
		//$data = $this->Calc($player);
		$body = $this->defdata[$name]['body'];
		$hp = floor($this->defdata[$name]['mhp']+$body/2);
		$this->setData([self::NAME_ATK => 0, self::NAME_DEF => 0, self::NAME_HP => $hp, self::NAME_MHP => $hp], $player);
		$this->checkAllArmor($player);
		$this->checkAllAcc($player);
	    $this->scoreUpdate($player);
		if(!isset($this->damagedata[$name])) $this->damagedata[$name] = 0;
		$this->dps[$name] = 0;
		$this->setName($player);
		
		$this->checkSubPrefix($player, $player->getInventory()->getItemInHand());
		$this->addWeaponAtk($player, $player->getInventory()->getItemInHand());
	}
	
	public function Calc($player){
		$data = [
		  self::NAME_ATK => 0,
		  self::NAME_DEF => 0,
		  self::NAME_HP => 0
		];
		$lv = $this->plugin->playerData[$player->getName()]['lv'];
		$data[self::NAME_ATK] = self::ATK_CONST * $lv;
		$data[self::NAME_DEF] = self::DEF_CONST * $lv;
		$data[self::NAME_HP] = self::HP_CONST * $lv;
		$data = $this->checkArmor($data, $player);
		return $data;
	}
	
	public function checkAllArmor($player){
		$name = $player->getName();
		$armor = $player->getArmorInventory()->getContents();
	    foreach($armor as $item){
			$this->setArmor($player, $item);
		}
	}

	public function removeAllArmor($player){
		$name = $player->getName();
		$armor = $player->getArmorInventory()->getContents();
	    foreach($armor as $item){
			$this->resetArmor($player, $item);
		}
	}

	public function setArmor($player, $item){
		$tag = $item->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
		if(!isset($tag)) return true;
		$def = $tag->getTag(Armor::TAG_DEF)->getValue();
		$lv = $tag->getTag(Armor::TAG_LEVEL)->getValue();
		//$def *= (1+$lv/10);
		$this->setDef($player, $def);
		for($i = 0; $i < 3; $i++){
			$skill = $tag->getTag(Armor::TAG_SKILLS[$i]);//->getValue();
			if(!isset($skill)) continue;
			$id = $skill->getValue();
			if(is_null($tag->getTag(Armor::TAG_SKILL_LV[$i]))){
				var_dump($skill);
				continue;
			}
			$lv = $tag->getTag(Armor::TAG_SKILL_LV[$i])->getValue();
			SkillManager::getSkill($id)->onSet($player, $lv);
			$name = SkillManager::getSkill($id)->getName();
			$player->sendMessage($name.'§r[§cLv§r:'.$lv.']が発動しました！');
		}
		$type = $tag->getTag(Armor::TAG_PROPERTY)->getValue();
		SBM::get($type)->onSet($player);
		$this->checkSubPrefixArmor($player, $item);
		$this->addWeaponAtk($player, $item);
	}

	public function resetArmor($player, $item){
		$tag = $item->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
		if(!isset($tag)) return true;
		$def = $tag->getTag(Armor::TAG_DEF)->getValue();
		$lv = $tag->getTag(Armor::TAG_LEVEL)->getValue();
		//$def *= (1+$lv/10);
		$this->setDef($player, -1 * $def);
		for($i = 0; $i < 3; $i++){
			$skill = $tag->getTag(Armor::TAG_SKILLS[$i]);//->getValue();
			if(!isset($skill)) continue;
			$id = $skill->getValue();
			if(is_null($tag->getTag(Armor::TAG_SKILL_LV[$i]))){
				var_dump($skill);
				continue;
			}
			$lv = $tag->getTag(Armor::TAG_SKILL_LV[$i])->getValue();
			SkillManager::getSkill($id)->onReset($player, $lv);
			$name = SkillManager::getSkill($id)->getName();
			$player->sendMessage($name.'§r[§cLv§r:'.$lv.']が解除されました！');
		}
		$type = $tag->getTag(Armor::TAG_PROPERTY)->getValue();
		SBM::get($type)->onReset($player);
		$this->checkSubPrefixArmor($player, $item, -1);
		$this->addWeaponAtk($player, $item, -1);
	}

	public function checkAllAcc($player){
		if(!isset($this->plugin->accessory->contents)) return false;
		if(!isset($this->plugin->accessory->contents[$player->getName()])) return false;
		$contents = $this->plugin->accessory->contents[$player->getName()];
		if(!isset($contents)) return true;
		foreach($contents as $item) $this->setAcc($player, $item);
	}

	public function setAcc($player, $item){
		$tag = $item->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
		if(!isset($tag)) return true;
		$def = $tag->getTag(Armor::TAG_DEF)->getValue();
		$atk = $tag->getTag(Armor::TAG_ATK)->getValue();
		$this->setDef($player, $def);
		$this->setAtk($player, $atk);
		for($i = 0; $i < 3; $i++){
			$skill = $tag->getTag(Armor::TAG_SKILLS[$i]);//->getValue();
			if(!isset($skill)) continue;
			$id = $skill->getValue();
			if(is_null($tag->getTag(Armor::TAG_SKILL_LV[$i]))){
				var_dump($skill);
				continue;
			}
			$lv = $tag->getTag(Armor::TAG_SKILL_LV[$i])->getValue();
			SkillManager::getSkill($id)->onSet($player, $lv);
			$name = SkillManager::getSkill($id)->getName();
			$player->sendMessage($name.'§r[§cLv§r:'.$lv.']が発動しました！');
		}
		$this->checkSubPrefixAcc($player, $item);
		$this->addWeaponAtk($player, $item);
	}

	public function resetAcc($player, $item){
		$tag = $item->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
		if(!isset($tag)) return true;
		$def = $tag->getTag(Armor::TAG_DEF)->getValue();
		$atk = $tag->getTag(Armor::TAG_ATK)->getValue();
		$this->setDef($player, -1 * $def);
		$this->setAtk($player, -1 * $atk);
		for($i = 0; $i < 3; $i++){
			$skill = $tag->getTag(Armor::TAG_SKILLS[$i]);//->getValue();
			if(!isset($skill)) continue;
			$id = $skill->getValue();
			if(is_null($tag->getTag(Armor::TAG_SKILL_LV[$i]))){
				var_dump($skill);
				continue;
			}
			$lv = $tag->getTag(Armor::TAG_SKILL_LV[$i])->getValue();
			SkillManager::getSkill($id)->onReset($player, $lv);
			$name = SkillManager::getSkill($id)->getName();
			$player->sendMessage($name.'§r[§cLv§r:'.$lv.']が解除されました！');
		}
		$this->checkSubPrefixAcc($player, $item, -1);
		$this->addWeaponAtk($player, $item, -1);
	}
	
	public function setData($data, $player){
		$name = $player->getName();
		$this->data[$name] = [
	      self::NAME_ATK => $data[self::NAME_ATK],
	      self::NAME_DEF => $data[self::NAME_DEF],
		  self::NAME_HP => $data[self::NAME_HP],
		  self::NAME_MHP => $data[self::NAME_MHP],
		  self::NAME_SHARP => 0,
		  self::NAME_CRIT => 0,
		  self::NAME_CRIT_PER => 1.4,
		  self::NAME_MINE => 0,
		  self::NAME_POW => 0,
		  self::NAME_AGI => 0,
		  self::NAME_HAN => 0,
		  self::NAME_BODY => 0,
		  self::NAME_SYU => 0,
		  self::NAME_MAGIC => 0,
		  self::NAME_ATKMULTI => 1,
		  self::NAME_DEFMULTI => 1,
		  self::NAME_ATKFINAL => 1,
		  self::NAME_DEFFINAL => 0,
		  self::NAME_ATKSPEEDMULTI => 1
	    ];
		for($i = 0; $i < 7; $i++){
			$this->data[$name][self::NAME_TYPEMULTI][$i] = 1;
			$this->data[$name][self::NAME_TYPEDEFMULTI][$i] = 0;
		}
	    if($data[self::NAME_HP] > $this->data[$name][self::NAME_MHP]){
			$this->data[$name][self::NAME_HP] = $this->data[$name][self::NAME_MHP];
		}
	}

	public function setdefData($data, $player){
	  $name = $player->getName();
	  $this->defdata[$name] = [
		self::NAME_ATK => $data[self::NAME_ATK],
		self::NAME_DEF => $data[self::NAME_DEF],
		self::NAME_MHP => $data[self::NAME_MHP],
		self::NAME_POW => $data[self::NAME_POW],
		self::NAME_AGI => $data[self::NAME_AGI],
		self::NAME_HAN => $data[self::NAME_HAN],
		self::NAME_BODY => $data[self::NAME_BODY],
		self::NAME_SYU => $data[self::NAME_SYU],
		self::NAME_MAGIC => $data[self::NAME_MAGIC],
		self::NAME_STYLE => $data[self::NAME_STYLE]
	  ];
	  //$this->scoreUpdate($data, $player);
	  $this->plugin->playerData[$name][self::NAME_ATK] = $data[self::NAME_ATK];
	  $this->plugin->playerData[$name][self::NAME_DEF] = $data[self::NAME_DEF];
	  $this->plugin->playerData[$name][self::NAME_MHP] = $data[self::NAME_MHP];
	  $this->plugin->playerData[$name][self::NAME_POW] = $data[self::NAME_POW];
	  $this->plugin->playerData[$name][self::NAME_AGI] = $data[self::NAME_AGI];
	  $this->plugin->playerData[$name][self::NAME_HAN] = $data[self::NAME_HAN];
	  $this->plugin->playerData[$name][self::NAME_BODY] = $data[self::NAME_BODY];
	  $this->plugin->playerData[$name][self::NAME_SYU] = $data[self::NAME_SYU];
	  $this->plugin->playerData[$name][self::NAME_MAGIC] = $data[self::NAME_MAGIC];
	  $this->plugin->playerData[$name][self::NAME_STYLE] = $data[self::NAME_STYLE];
	}
	
	public function onSlotChange($inv, $index, $before){
		$player = $inv->getHolder();
		$new = $inv->getItem($index);
		$old = $before;
		$this->resetArmor($player, $old);
		$this->setArmor($player, $new);
		$this->setNameTag($player);
		//$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'ChangeTask'], [$player]), 5);
	}
	
/*	public function onMove($event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$bar = $this->getHpBar($player);
		$atk = $this->getAtk($player);
		$def = $this->getDef($player);
		$per = round($this->getHealthPer($player)*20);
		$message = "§cATK: §f".$atk."   §6DEF: §f".$def."\n§aHP >> ".$bar.' §f'.($per * 5)."%%\n\n";
		$player->sendTip($message);
		//if($player->isImmobile())
			//$event->setCancelled(true);
	}*/

	public function TickTask(){
		$players = $this->plugin->getServer()->getOnlinePlayers();
		foreach($players as $player){
		  $name = $player->getName();
		  if(!isset($this->data[$name])) continue;
		  $bar = $this->getHpBar($player);
		  $atk = $this->getAtk($player);
		  $def = $this->getDef($player);
		  $per = round($this->getHealthPer($player)*20);
		  $message = "§bDPS§f >> ".$this->dps[$name]."\n§cATK: §f".$atk."   §6DEF: §f".$def."\n§aHP >> ".$bar.' §f'.($per * 5)."%%\n\n";
		  $player->sendTip($message);
		  if($player->isSneaking())
		  	$this->addHp($player, 1);
		}
		$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'TickTask'], []), 5);
	}

	public function getHpBar($player){
		$name = $player->getName();
		$per = round($this->getHealthPer($player)*20);
		$bar = '§a';
		for($i = 0; $i < $per; $i++)
			$bar .= '|';
		$bar .= '§c';
		for($i = 0; $i < 20 - $per; $i++)
			$bar .= '|';
		return $bar;
	}
	
	public function ChangeTask($player){
		//$data = $this->Calc($player);
		$this->checkArmor($player);
		$player->sendMessage("§eINFO>>§f装備を更新しました。");
	}
	
	public function scoreUpdate($player){
		$name = $player->getName();
		$next = 0;
		for($i = 0; $i < $this->getLevel($player); $i++){
			$next += pow(1.1, $i);
		}
		SCM::updateLine($player, SCM::LINE_ATK,   '§catk  §f: '.$this->getAtk($player));
		SCM::updateLine($player, SCM::LINE_DEF,   '§ddef  §f: '.$this->getDef($player));
		SCM::updateLine($player, SCM::LINE_HP,    '§eMaxhp§f: '.$this->data[$name][self::NAME_MHP]);
	    SCM::updateLine($player, SCM::LINE_LEVEL, '§aLV   §f: '.$this->getLevel($player));
		SCM::updateLine($player, SCM::LINE_NEXT,  '§b次のレベルまで§f: '.floor((200 * $next - $this->getExp($player))).'exp');
		SCM::updateLine($player, SCM::LINE_EXP,   '§bexp  §f: '.$this->getExp($player));
		SCM::updateLine($player, SCM::LINE_MONEY, '§1Fl   §f: '.$this->plugin->playerData[$player->getName()]['money']);
	}

	public function Attack($player, $atk){
		$name = $player->getName();
		$def = $this->getDef($player);
		if($def >= 0){
		  $damage = (int)($atk * (160 / (160 + $def) ));
		}else{
		  $damage = $atk * ($def * -1);
		}
		$damage *= (1 - $this->getFinalDamageCutMultiple($player));
		$this->addHp($player, -1 * $damage);
		$player->sendPopup('§l§c'.$damage.'§d DAMAGE!');
		if($this->data[$name][self::NAME_HP] <= 0){
			$this->plugin->animation->CancelAnimation($player);
			$this->plugin->fieldmanager->toLastField($player);
			$this->data[$name][self::NAME_HP] = $this->data[$name][self::NAME_MHP];
		}
	}

	public function addStatus($player, $pos){
		$name = $player->getName();
		$this->defdata[$name][$pos]++;
		$this->setdefData($this->defdata[$name], $player);
		$hp = $this->getBody($player)/2 + $this->defdata[$name][self::NAME_MHP];
		$this->setMHP($player, $hp);
	}
	
	public function addExp($player, $exp, $log = true){
		$name = $player->getName();
		if($log) $player->sendMessage('§7'.$exp.'exp§fを手に入れた');
		$style = $this->getStyle($player);
		switch($style){
			case self::STYLE_SWORD:
				$exn = 'sexp';
				$lvn = 'slv';
			  break;
			case self::STYLE_PI:
				$exn = 'pexp';
				$lvn = 'plv';
			  break;
			case self::STYLE_BOW:
				$exn = 'bexp';
				$lvn = 'blv';
			  break;
			case self::STYLE_MAGIC:
				$exn = 'texp';
				$lvn = 'tlv';
			  break;
		}
		$this->plugin->playerData[$name][$exn] += $exp;
		$next = 0;
		for($i = 0; $i < $this->plugin->playerData[$name][$lvn]; $i++){
			$next += pow(1.1, $i);
		}
		if($this->plugin->playerData[$name][$exn] >= 200 * $next){
		  $this->plugin->playerData[$name][$lvn] ++;
		  if($this->plugin->playerData[$name][$lvn] % 10 === 0){
			switch($this->plugin->playerData[$name][$lvn]){
				case 10:
				  $id = 22;
				  break;
				case 20:
				  $id = 23;
				  break;
				case 30:
				  $id = 24;
				  break;
			}
			if(isset($id)){
			$item = ItemManager::getItem($id)->getItem();
			$player->getInventory()->addItem($item);
			$player->sendMessage($item->getName().'§fx'.$item->getCount().'§7を手に入れた');
			}
		  }
		  if($log) $player->sendMessage('§eINFO§f>>レベルが上がりました');
		  $this->plugin->playerData[$name]['point'] ++;
		  $this->plugin->playerData[$name]['atk'] ++;
		  $this->plugin->playerData[$name]['def'] ++;
		  $this->plugin->playerData[$name]['mhp'] += 5;
		  $this->setdefData($this->plugin->playerData[$name], $player);
		  $this->setMHP($player, $this->plugin->playerData[$name]['mhp']);
		}
		$this->scoreUpdate($player);
		SCM::updateLine($player, SCM::LINE_NEXT,  '§b次のレベルまで§f: '.floor((200 * $next - $this->getExp($player))).'exp');
		SCM::updateLine($player, SCM::LINE_LEVEL, '§aLV   §f: '.$this->getLevel($player));
		SCM::updateLine($player, SCM::LINE_EXP,   '§bexp  §f: '.$this->getExp($player));
		
	}
	
	public function addMoney($player, $money, $log = true){
        $name = $player->getName();
		$this->plugin->playerData[$name]['money'] += $money;
		if($log) $player->sendMessage($money."Flを手に入れた");
		SCM::updateLine($player, SCM::LINE_MONEY, '§1Fl§f: '.$this->plugin->playerData[$player->getName()]['money']);
	}

	public function addOfflineMoney($name, $money){
		$this->plugin->playerData[$name]['money'] += $money;
	}
	
	//return bool
	public function takeMoney($player, $money){
		$name = $player->getName();
		if($this->plugin->playerData[$name]['money'] < $money) return false;
		$this->plugin->playerData[$name]['money'] -= $money;
		SCM::updateLine($player, SCM::LINE_MONEY, '§1Fl§f: '.$this->plugin->playerData[$player->getName()]['money']);
		return true;
	}

	public function hasMoney($player, $money){
		$name = $player->getName();
		if($this->plugin->playerData[$name]['money'] < $money) return false;
		return true;
	}

	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if($event->getBlock()->getId() === 199){
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
				$event->cancel(true);
			}
		}
		if($event->getBlock()->getId() === 130){
			//$player->setCurrentWindow($player->getEnderInventory());
		}
		$inv = new PlayerInventory($player);
        //$player->addWindow($player->getInventory());
        if(!array_key_exists($name, $this->cooltime))
          $this->cooltime[$name] = 0;
		$time = microtime(true);
        $num = $time - $this->cooltime[$name];
        if($num < 0.25){
			return false;
        }
        $this->cooltime[$name] = $time;
		if($event->getBlock()->getId() === 120){
		  $player->sendMessage('§dYOUR STATUS§f>>');
		  $player->sendMessage('§aATK§f: '. $this->getAtk($player));
		  $player->sendMessage('§6Def§f: '. $this->getDef($player));
		  $player->sendMessage('§bSharp§f: '. $this->getSharp($player));
		  $player->sendMessage('§dCrit§f: '. $this->getCrit($player));
		  $player->sendMessage('§d会心倍率§f: '. $this->getCritPer($player));
		  $player->sendMessage('§e最終ダメージ倍率§f: '. $this->getFinalDamageMultiple($player));
		  $player->sendMessage('§e最終ダメージ軽減倍率§f: '. $this->getFinalDamageCutMultiple($player));
		}
		$item = $player->getInventory()->getItemInHand();
		$id = $item->getId();
		$tag = $item->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
		if(!isset($tag) or !$player->isSneaking()) return true;
		$pos = $tag->getTag(Armor::TAG_POS)->getValue();
		$item2 = $player->getArmorInventory()->getItem($pos);
		$player->getArmorInventory()->setItem($pos, $item);
		$player->getInventory()->setItemInHand($item2);
	}

	public function onUse(PlayerItemUseEvent $event){
		/*$player = $event->getPlayer();
		$item = $event->getItem();
		if(is_null($item->getNamedTag()->getTag(Armor::TAG_ARMOR))) return false;
		$event->cancel();
		if(!$player->isSneaking()){
		  $player->sendMessage('§aHINT§f>>アーマーを着るときは、スニークをしてください。');
		}else{
		  $tag = $item->getNamedTag()->getTag(Armor::TAG_ARMOR);
		  $pos = $tag->getTag(Armor::TAG_POS)->getValue();
		  $item2 = $player->getArmorInventory()->getItem($pos);
		  $player->getArmorInventory()->setItem($pos, $item);
		  $player->getInventory()->setItemInHand($item2);
		}*/
	}

	public function onDrop(PlayerDropItemEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if(isset($this->open[$name])) return false;
		$item = $player->getInventory()->getItemInHand();
		$tag = $item->getNamedTag()->getTag(Weapon::TAG_WEAPON);
		if(!isset($tag)) return false;
		$uuid1 = $tag->getTag(Weapon::TAG_UNIQUE_ID)->getValue();
		$item2 = $event->getItem();
		$tag2 = $item2->getNamedTag()->getTag(Weapon::TAG_WEAPON);
		if(!isset($tag2)) return false;
		$uuid2 = $tag2->getTag(Weapon::TAG_UNIQUE_ID)->getValue();
		if($uuid1 === $uuid2){
			$this->checkSubPrefix($player, $item, -1);
			$this->addWeaponAtk($player, $item, -1);
		}
	}

	public function onPick(EntityItemPickupEvent $event){
		$player = $event->getEntity();
		if(!($player instanceof Player)) return false;
		if(isset($this->open[$player->getName()])) return false;
		$inv = $player->getInventory();
		if($inv->firstEmpty() == $inv->getHeldItemIndex()){
			$this->checkSubPrefix($player, $event->getItem());
			$this->addWeaponAtk($player, $event->getItem());
		}
		
	}

	public function getStyle($player){
		$name = $player->getName();
		return $this->defdata[$name][self::NAME_STYLE];
	}

	public function changeStyle($player, $style){
		$name = $player->getName();
		$this->defdata[$name][self::NAME_STYLE] = $style;
		$this->setdefData($this->defdata[$name], $player);
		$this->scoreUpdate($player);
	}

	public function getLevel($player){
		$name = $player->getName();
		$style = $this->defdata[$name][self::NAME_STYLE];
		switch($style){
			case self::STYLE_SWORD:
				$lv = $this->plugin->playerData[$name]['slv'];
				break;
			case self::STYLE_PI:
				$lv = $this->plugin->playerData[$name]['plv'];
				break;
			case self::STYLE_BOW:
				$lv = $this->plugin->playerData[$name]['blv'];
				break;
			case self::STYLE_MAGIC:
				$lv = $this->plugin->playerData[$name]['tlv'];
				break;
		}
		return $lv;
	}

	public function addWeaponAtk($player, $item, $hugou = 1){
		$tag = $item->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
        if(isset($tag)){
		  $count = $tag->getTag(Weapon::TAG_REROLL_COUNT);
		  if(!isset($count)) return false;
		  $lv = $tag->getTag(Weapon::TAG_LEVEL)->getValue();
		  if($lv > 8) return false;
          $watk = $tag->getTag(Weapon::TAG_ATK)->getValue();
          $sharp = $tag->getTag(Weapon::TAG_SHARP)->getValue();
          $sharp += $this->plugin->playermanager->getSharp($player);
          $watk /= 1;
          $watk *= $sharp / 300;
		  $this->setAtk($player, $watk*$hugou);
		}
	}

	public function getPow($player){
		$name = $player->getName();
		return $this->defdata[$name][self::NAME_POW] + $this->data[$name][self::NAME_POW];
	}

	public function getAgi($player){
		$name = $player->getName();
		return $this->defdata[$name][self::NAME_AGI] + $this->data[$name][self::NAME_AGI];
	}

	public function addAgi($player, $amount, $time){
		$name = $player->getName();
		$this->data[$name][self::NAME_AGI] += $amount;
		$this->plugin->getScheduler()->scheduleDelayedTask(
			new Callback([$this, 'setAgi'], [$player, -$amount]), $time * 20);
		$this->sendJukePop($player, '§bすばやさ§f +'.$amount."\n".$time.'seconds!');
	}

	public function setAgi($player, $amount){
		$name = $player->getName();
        $this->data[$name][self::NAME_AGI] += $amount;
	}

	public function getHan($player){
		$name = $player->getName();
		return $this->defdata[$name][self::NAME_HAN] + $this->data[$name][self::NAME_HAN];
	}

	public function addHan($player, $amount, $time){
		$name = $player->getName();
		$this->data[$name][self::NAME_HAN] += $amount;
		$this->plugin->getScheduler()->scheduleDelayedTask(
			new Callback([$this, 'setHan'], [$player, -$amount]), $time * 20);
		$this->sendJukePop($player, '§7はんだんりょく§f +'.$amount."\n".$time.'seconds!');
	}

	public function setHan($player, $amount){
		$name = $player->getName();
        $this->data[$name][self::NAME_HAN] += $amount;
	}

	public function getBody($player){
		$name = $player->getName();
		return $this->defdata[$name][self::NAME_BODY] + $this->data[$name][self::NAME_BODY];
	}

	public function setBody($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_BODY] += $amount;
		$hp = $this->getBody($player)/2 + $this->defdata[$name][self::NAME_MHP];
		$this->setMHP($player, $hp);
	}

	public function getSyu($player){
		$name = $player->getName();
		return $this->defdata[$name][self::NAME_SYU] + $this->data[$name][self::NAME_SYU];
	}

	public function addSyu($player, $amount, $time){
		$name = $player->getName();
		$this->data[$name][self::NAME_SYU] += $amount;
		$this->plugin->getScheduler()->scheduleDelayedTask(
			new Callback([$this, 'setSyu'], [$player, -$amount]), $time * 20);
		$this->sendJukePop($player, '§0しゅうちゅうりょく§f +'.$amount."\n".$time.'seconds!');
	}

	public function setSyu($player, $amount){
		$name = $player->getName();
        $this->data[$name][self::NAME_SYU] += $amount;
	}

	

	public function getExp($player){
		$name = $player->getName();
		$style = $this->defdata[$name][self::NAME_STYLE];
		switch($style){
			case self::STYLE_SWORD:
				$exp = $this->plugin->playerData[$name]['sexp'];
				break;
			case self::STYLE_PI:
				$exp = $this->plugin->playerData[$name]['pexp'];
				break;
			case self::STYLE_BOW:
				$exp = $this->plugin->playerData[$name]['bexp'];
				break;
			case self::STYLE_MAGIC:
				$exp = $this->plugin->playerData[$name]['texp'];
				break;
		}
		return $exp;
	}

	public function getDef($player){
		$name = $player->getName();
		$def = $this->data[$name][self::NAME_DEF] + $this->defdata[$name][self::NAME_DEF];
		$def *= $this->data[$name][self::NAME_DEFMULTI];
		return round($def);
	}

	public function getAtk($player){
		$name = $player->getName();
		$atk = $this->data[$name][self::NAME_ATK] + $this->defdata[$name][self::NAME_ATK];
		$atk *= $this->data[$name][self::NAME_ATKMULTI];
		return round($atk);
	}

	public function getHp($player){
		$name = $player->getName();
		return $this->data[$name][self::NAME_HP];
	}

	public function getCritPer($player){
		$name = $player->getName();
		return $this->data[$name][self::NAME_CRIT_PER];
	}

	public function getAtkSpeed($player){
		$name = $player->getName();
		$as = $this->data[$name][self::NAME_ATKSPEEDMULTI];
		return $as;
	}

	public function setAtkSpeed($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_ATKSPEEDMULTI] += $amount;
	}

	public function setMHP($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_MHP] = $amount;
		SCM::updateLine($player, SCM::LINE_HP,    '§eMaxhp§f: '.$this->data[$name][self::NAME_MHP]);
	}


	public function addHP($player, $amount){
		$name = $player->getName();
		if($amount > 0)
		  $amount = SBM::get(Type::LIGHT)->onHeal($player, $amount);
		$this->data[$name][self::NAME_HP] += $amount;
		if($this->data[$name][self::NAME_HP] / $this->data[$name][self::NAME_MHP] > 1)
			$this->data[$name][self::NAME_HP] = $this->data[$name][self::NAME_MHP];
		if($this->data[$name][self::NAME_HP] < 0)
		    $this->data[$name][self::NAME_HP] = 0;
		$skills = SkillManager::onHp();
        foreach ($skills as $skill){
          $skill->onHp($player);
		}
		$this->setNameTag($player);
	}

	public function getHealthPer($player){
		$name = $player->getName();
		return $this->data[$name][self::NAME_HP] / $this->data[$name][self::NAME_MHP];
	}

	public function addAtk($player, $amount, $time){
		$name = $player->getName();
		$this->data[$name][self::NAME_ATK] += $amount;
		$this->plugin->getScheduler()->scheduleDelayedTask(
			new Callback([$this, 'setAtk'], [$player, -$amount]), $time * 20);
		$player->sendPopup('§dATK§f +'.$amount.' : '.$time.'seconds!');
		SCM::updateLine($player, SCM::LINE_ATK,   '§catk  §f: '.$this->getAtk($player));
		$this->setNameTag($player);
	}

    public function addDef($player, $amount, $time){
		$name = $player->getName();
		$this->data[$name][self::NAME_DEF] += $amount;
		$this->plugin->getScheduler()->scheduleDelayedTask(
			new Callback([$this, 'setDef'], [$player, -$amount]), $time * 20);
		$player->sendPopup('§6DEF§f +'.$amount.' : '.$time.'seconds!');
		$this->setNameTag($player);
		SCM::updateLine($player, SCM::LINE_DEF,   '§ddef  §f: '.$this->getDef($player));
	}
	public function setAtk($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_ATK] += $amount;
		$this->setNameTag($player);
		SCM::updateLine($player, SCM::LINE_ATK,   '§catk  §f: '.$this->getAtk($player));
	}

	public function setDef($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_DEF] += $amount;
		$this->setNameTag($player);
		SCM::updateLine($player, SCM::LINE_DEF,   '§ddef  §f: '.$this->getDef($player));
	}

	public function setCritPer($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_CRIT_PER] += $amount;
		$this->setNameTag($player);
	}

	public function setAtkMultiple($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_ATKMULTI] += $amount;
		$this->setNameTag($player);
	}

	public function setDefMultiple($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_DEFMULTI] += $amount;
		$this->setNameTag($player);
	}

	public function setFinalDamageMultiple($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_ATKFINAL] += $amount;
		$this->setNameTag($player);
	}

	public function getFinalDamageMultiple($player){
		$name = $player->getName();
		return $this->data[$name][self::NAME_ATKFINAL];
	}

	public function setFinalDamageCutMultiple($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_DEFFINAL] += $amount;
		$this->setNameTag($player);
	}

	public function getFinalDamageCutMultiple($player){
		$name = $player->getName();
		return $this->data[$name][self::NAME_DEFFINAL];
	}

	public function setTypeDamageMultiple($player, $type, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_TYPEMULTI][$type] += $amount;
		$this->setNameTag($player);
	}

	public function getTypeDamageMultiple($player, $type){
		$name = $player->getName();
		return $this->data[$name][self::NAME_TYPEMULTI][$type];
	}

	public function setTypeDamageCutMultiple($player, $type, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_TYPEDEFMULTI][$type] += $amount;
		$this->setNameTag($player);
	}

	public function getTypeDamageCutMultiple($player, $type){
		$name = $player->getName();
		return $this->data[$name][self::NAME_TYPEDEFMULTI][$type];
	}

	public function getAtkData($player){
		$name = $player->getName();
		return $this->data[$name][self::NAME_ATK];
	}

	public function getCrit($player){
		$name = $player->getName();
		return $this->data[$name][self::NAME_CRIT];
	}

	public function getSharp($player){
		$name = $player->getName();
		return $this->data[$name][self::NAME_SHARP];
	}

	public function setCrit($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_CRIT] += $amount;
	}

	public function addCrit($player, $amount, $time){
		$name = $player->getName();
		$this->data[$name][self::NAME_CRIT] += $amount;
		$this->plugin->getScheduler()->scheduleDelayedTask(
			new Callback([$this, 'setCrit'], [$player, -$amount]), $time * 20);
		$player->sendPopup('§eCrit§f +'.$amount.'％ : '.$time.'seconds!');
		$this->setNameTag($player);
	}

	public function setSharp($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_SHARP] += $amount;
	}

	public function onFirst($player){
	  $bairitu = [100, 100];
	  $item = WeaponManager::getWeapon()->getItem(6, $bairitu, 0);
	  $player->getInventory()->setItemInHand($item);
	  $player->getInventory()->addItem(ItemFactory::getInstance()->get(364, 0, 64));
	  $message = ['lobeliaへようこそ！です！', '私はLobelia-Battle-Assist-System、通称L-BASです！'
				, '案内役を務めるのでどうぞよろしくです！','とはいっても、実は起動されたばかりで何もわからないというか...',
			'とりあえず、Aconitumっていう人を探してください！その人がいろいろ教えてくれるはずです...多分。'];
	  //$this->plugin->lbas->sendMessages($player, $message);
	  $this->addTitle($player, 0);
	}

	public function addTitle($player, $id, $rankup = false){
		$name = $player->getName();
		if(!in_array($id, $this->plugin->playerData[$name]['title'])){
			$this->plugin->playerData[$name]['title'][] = $id;
			if($rankup) $this->addRank($player);
		    $this->plugin->getServer()->broadcastMessage('§l§bLobelia§f>>'.$name.'さんが['.$this->plugin->titleData[$id].'§f]を達成しました！');
		}
		
	}

	public function addRank($player){
		$name = $player->getName();
		$this->plugin->playerData[$name]['rank']++;
		$this->setName($player);
		$player->sendMessage('§eINFO>>§fランクが上昇しました！');
		$this->plugin->getServer()->broadcastMessage('§l§bLobelia§f>>'.$name.'さんが[§dRANK§f:'.$this->plugin->rankData[$this->plugin->playerData[$name]['rank']].']§fに昇格しました！');
	}

	public function addMine($player, $amount, $time){
		$name = $player->getName();
		$this->data[$name][self::NAME_MINE] += $amount;
		$this->plugin->getScheduler()->scheduleDelayedTask(
			new Callback([$this, 'setMine'], [$player, -$amount]), $time * 20);
		$player->sendPopup('§cMINE§f +'.$amount.' : '.$time.'seconds!');
		$this->setNameTag($player);
	}

	public function setMine($player, $amount){
		$name = $player->getName();
		$this->data[$name][self::NAME_MINE] += $amount;
	}

	public function getMine($player){
		$name = $player->getName();
		return $this->data[$name][self::NAME_MINE];
	}

	public function setImmobile($player, $time, $bool = true){
		$player->setImmobile($bool);
		if($bool)
		$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'setImmobile'], [$player, $time, false]), $time*20);
	}

	public function setName($player){
		$name = $player->getName();
		$player->setDisplayName('['.$this->plugin->rankData[$this->plugin->playerData[$name]['rank']].']'.$name);
		$tag = $player->getDisplayName();
		$player->setDisplayName('['.$this->plugin->playerData[$name]['guild'].'§f]'.$tag);
		$this->setNameTag($player);
	}

	public function setNameTag($player){
		$name = $player->getDisplayName();
		$hp = $this->getHpBar($player);
		$atk = $this->getAtk($player);
		$def = $this->getDef($player);
		$player->setNameTag($hp."\n".$name."\n§cATK: §f".floor($atk)."   §6DEF: §f".floor($def));
	}

	public function setDamageData($player, $damage){
		$name = $player->getName();
		$this->damagedata[$name] += $damage;
		//if($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return false;
		//$this->damagedata[$name] = $this->damagedata[$name] > $damage ? $this->damagedata[$name] : $damage;

	}

	public function setDpsData(){
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			$name = $player->getName();
			if(!isset($this->dps[$name])) continue;
			$this->dps[$name] = $this->damagedata[$name];
			$this->damagedata[$name] = 0;
		}
		$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'setDpsData'], []), 20);
	}

	public function damageRanking(){
		/*$top = [];
		foreach($this->damagedata as $name => $damage){
			for($i = 0; $i < 3; $i++){
				if(!isset($top[$i])){
					$top[$i] = [$name, $damage];
					break;
				}
				if($top[$i][1] < $damage){
					$top[$i+1] = $top[$i];
					$top[$i] = [$name, $damage];
					break;
				}
			}
			$this->damagedata[$name] = 0;
		}
		$this->plugin->getServer()->broadcastMessage(
			'§l§bLobelia§f>>1撃の最大ダメージランキング！'
		);
		for($i = 0; $i < 3; $i++){
			if(!isset($top[$i])) continue;
			$this->plugin->getServer()->broadcastMessage(
				'§l'.($i+1).'位§r: '.$top[$i][0].'さん §e[Damage]§f:'.$top[$i][1]
			);
			$player = $this->plugin->getServer()->getPlayersExact($top[$i][0]);
			if(isset($player)){
				$item = ItemManager::getItem(14)->getItem()->setCount(3-$i);
				$player->getInventory()->addItem($item);
				//$player->sendMessage($item->getName().'§fx'.$item->getCount().'§7を手に入れた');
				//$this->addMoney($player, 5000);
			}
		}
		$this->plugin->getServer()->broadcastMessage(
			'§l§bLobelia§f>>上位者には報酬が与えられます！ぜひ頑張ってくださいね！'
		);
		unset($top);
		$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'damageRanking'], []), 10*60*20);*/
	}

	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
		$message = $event->getMessage();
		if($message === '/kill'){
		  $event->cancel(true);
		  $this->plugin->lbas->sendMessages($player, ['自殺なんてダメですよ！']);
		}
        //$this->sendToDiscord("<".$player->getDisplayName()."> ".$message);
	}
	
	public function onEntityDamage(EntityDamageEvent $event){
		$event->cancel(true);
	}

	public function onReceive(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		if($packet instanceof InteractPacket){
			if($packet->action == 6){
				$player = $event->getOrigin()->getPlayer();
				$i = $player->getInventory()->getItemInHand();
				$index = $player->getInventory()->getHeldItemIndex();
				$this->checkSubPrefix($player, $i, -1);
				$this->addWeaponAtk($player, $i, -1);
				$this->open[$player->getName()] = true;
			}
		}elseif($packet instanceof ContainerClosePacket){
			$player = $event->getOrigin()->getPlayer();
			$name = $player->getName();
			if(isset($this->open[$player->getName()])){
				if(!$this->open[$player->getName()]) return false;
				//$player->getInventory()->setHeldItemIndex($this->open[$name]);
				$i = $player->getInventory()->getItemInHand();
				$this->checkSubPrefix($player, $i);
				$this->addWeaponAtk($player, $i);
				unset($this->open[$player->getName()]);
			}
		}
		if($packet instanceof ItemFrameDropItemPacket){
			$player = $event->getOrigin()->getPlayer();
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
				$event->cancel(true);
			}
		}
	}

	public function onQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$this->plugin->fieldmanager->onQuit($event);
		$this->removeAllArmor($player);
		if(DungeonManager::isDungeon($player)){
	      DungeonManager::getDungeonByPlayer($player)->Death($player);
		}
	}

	public function onHeld(PlayerItemHeldEvent $event){
		$player = $event->getPlayer();
		$item = $event->getItem();
		$i = $player->getInventory()->getItemInHand();
		if(!isset($this->data[$player->getName()])) return false;
		if(isset($this->open[$player->getName()])) return false;
		$this->checkSubPrefix($player, $i, -1);
		$this->addWeaponAtk($player, $i, -1);
		$this->checkSubPrefix($player, $item);
		$this->addWeaponAtk($player, $item);
	}

	public function onOpen(InventoryOpenEvent $event){
		$player = $event->getPlayer();
		$item = $player->getInventory()->getItemInHand();
		$this->checkSubPrefix($player, $item, -1);
		$this->addWeaponAtk($player, $item, -1);
		$this->open[$player->getName()] = false;
	}

	public function onClose(InventoryCloseEvent $event){
		$player = $event->getPlayer();
		$item = $player->getInventory()->getItemInHand();
		$this->checkSubPrefix($player, $item);
		$this->addWeaponAtk($player, $item);
		unset($this->open[$player->getName()]);
	}

	public function sendJukePop($player, $message){
		$pk = new TextPacket();
		$pk->type = 4;
		$pk->sourceName = 'aaa';
		$pk->message = $message;
		$pk->parameters = ['aaa'];
		$player->getNetworkSession()->sendDataPacket($pk);
	}


	public function checkSubPrefixArmor($player, $item, $hugou = 1){
		$tag = $item->getNamedTag()->getTag(Armor::TAG_ARMOR);
		//if(!isset($tag)) $tag = $item->getNamedTag()->getTag(Armor::TAG_ARMOR);
		if(isset($tag)){
			for($i = 0; $i < 6; $i++){
			  $st = $tag->getTag(Weapon::TAG_SUB[$i]);
			  if(!isset($st)) continue;
			  $id = $st->getValue();
			  if($id == -1) continue;
			  $per = $tag->getTag(Weapon::TAG_SUBPER[$i])->getValue() * $hugou;
			  $this->SubPrefixHanei($player, $id, $per);
			}
		}
	}

	public function checkSubPrefix($player, $item, $hugou = 1){
		$tag = $item->getNamedTag()->getTag(Weapon::TAG_WEAPON);
		//if(!isset($tag)) $tag = $item->getNamedTag()->getTag(Armor::TAG_ARMOR);
		if(isset($tag)){
			for($i = 0; $i < 6; $i++){
			  $st = $tag->getTag(Weapon::TAG_SUB[$i]);
			  if(!isset($st)) continue;
			  $id = $st->getValue();
			  if($id == -1) continue;
			  $per = $tag->getTag(Weapon::TAG_SUBPER[$i])->getValue() * $hugou;
			  $this->SubPrefixHanei($player, $id, $per);
			}
		}
	}

	public function checkSubPrefixAcc($player, $item, $hugou = 1){
		$tag = $item->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
		//if(!isset($tag)) $tag = $item->getNamedTag()->getTag(Armor::TAG_ARMOR);
		if(isset($tag)){
			for($i = 0; $i < 6; $i++){
			  $st = $tag->getTag(Weapon::TAG_SUB[$i]);
			  if(!isset($st)) continue;
			  $id = $st->getValue();
			  if($id == -1) continue;
			  $per = $tag->getTag(Weapon::TAG_SUBPER[$i])->getValue() * $hugou;
			  $this->SubPrefixHanei($player, $id, $per);
			}
		}
	}

	public function SubPrefixHanei($player, $id, $per){
		switch($id){
			case 0:
				$this->setCrit($player, $per);
				break;
			case 1:
				$this->setCritPer($player, $per/100);
				break;
			case 2:
				$this->setAtkMultiple($player, $per/100);
				break;
			case 3:
				$this->setDefMultiple($player, $per/100);
				break;
			case 4:
				$this->setFinalDamageMultiple($player, $per/100);
				break;
			case 5:
				$this->setFinalDamageCutMultiple($player, $per/100);
				break;
			case 6:
				$this->setAtkSpeed($player, $per/100);
				break;
			case 7:
			case 8:
			case 9:
			case 10:
			case 11:
			case 12:
			case 13:
				$this->setTypeDamageMultiple($player, $id - 7, $per/100);
				break;
			case 14:
			case 15:
			case 16:
			case 17:
			case 18:
			case 19:
			case 20:
				$this->setTypeDamageCutMultiple($player, $id - 14, $per/100);
				break;
		}
	}

	public function TPTask(){
		$players = $this->plugin->getServer()->getOnlinePlayers();
		foreach($players as $player){
			$this->healTP($player);
		}
		$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'TPTask'], []), 20);
	}

	public function healTP($player){
		$xp = $player->getXpManager()->getXpLevel();
		if($xp < 100){
		  $player->getXpManager()->setXpLevel($xp + 1);
		  $player->getXpManager()->setXpProgress(($xp + 1) / 100);
		}
	}

	public function setJob($player, $slot, $data){
		$this->plugin->playerdata[$name]['job'][$slot] = $data;
	}
}
			
		
	
	
 