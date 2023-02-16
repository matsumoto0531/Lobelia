<?php
namespace pve\weapon;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use Ramsey\Uuid\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\NBT;

use pve\SpecialSkillManager;
use pve\WeaponSkillManager;
use pve\Type;
use pve\prefix\Prefix;

class Weapon {
	const ID = 0;
	const ITEM_ID = 0;
	const ATK = 0;
	const DEF = 0;
	const NAME = 'Error';
	const SHARP = 0;
	const SKILL_ID = 0;
	const SKILL_NAME = '';
	
	const TAG_WEAPON = 'weapon';
	const TAG_WEAPON_ID = "weapon_id";
	const TAG_TYPE = "type";
	const TAG_PROPERTY = "property";
	const TAG_UNIQUE_ID = "unique_id";
	const TAG_LEVEL = "level";
	const TAG_EXP = "exp";
	const TAG_ATK = 'atk';
	const TAG_HOSEIATK = 'hatk';
	const TAG_DEF = 'def';
	const TAG_SHARP = 'sharp';
	const TAG_SKILL = 'skill';
	const TAG_RARE = 'rare';
	const TAG_BAIRITU = 'bairitu';
	const TAG_PREFIX = 'prefix';
	const TAG_REROLL_COUNT = 'pcount';
	const TAG_SUB = ['sub1', 'sub2', 'sub3', 'sub4', 'sub5', 'sub6'];
	const TAG_SUBPER = ['sp1', 'sp2', 'sp3', 'sp4', 'sp5', 'sp6'];
	const TAG_SUBSTATE = ['ss1', 'ss2', 'ss3', 'ss4', 'ss5', 'ss6'];
	const TAG_STATUS = ['pow' => '§cちから§f', 'agi' => '§bすばやさ§f', 'han' => '§7はんだんりょく§f', 'body' => '§6からだ§f', 'syu' => '§0しゅうちゅうりょく§f', 'magic' => '§aまりょく§f'];
	
	public function __construct($plugin){
		$this->plugin = $plugin;
	}
	
	public function getId(){
		return static::ID;
	}
	
	public function getAtk(){
		return static::ATK;
	}
	
	public function getDef(){
		return static::DEF;
	}
	
	public function getSharp(){
		return static::SHARP;
	}
	
	public function getItemId(){
		return static::ITEM_ID;
	}
	
	public function getName(){
		return static::NAME;
	}
	
	public function getItem($id, $bairitus, $exp = 0){
		$data = $this->plugin->swordData[$id];
		$atk = round($data['atk']); //* $bairitus[0]/100);
		$sharp = round($data['sharp']); //* $bairitus[1]/100);
		$rare = $data['rare'];
		$type = $data['type'];
		$lv = 0;
		$next = ($lv+1) * 100 * $rare;
        while($exp >= $next){
          $lv++;
          $atk *= 1.1;
          if($lv >= 8){
            $exp = 0;
            $next = 0;
            break;
          }else{
            $exp -= $next;
            $next = ($lv+1) * 100 * $rare;
          }
        }
		$message = '';
		if($data['rare'] > 10){
            $message .= '§e';
        }elseif($data['rare'] > 5){
            $message .= '§a';
        }else{
            $message .= '§e';
        }
        for($i = 0; $i < $data['rare']; $i ++){
            $message .= '☆';
        }
		$atk = floor($atk);
		$item = ItemFactory::getInstance()->get($data['itemid'], 0, 1);
		$lore = ['§cATK: '.$atk, '§e切れ味: '.$sharp, '§a火力指数: §f'.floor(floor($atk) * ($sharp/300)), '§b属性: §f'.Type::getName($type),'§d強化: §f'.$lv.'Lv', 
		'§7EXP§f: 0/'.(100*$rare*($lv+1)), '§cRARE§f: '.$message];
		$item->setCustomName($data['name']);
		$prefix = Prefix::get(0);
		//$exp += (100 * $rare * (1+$lv) * $lv) / 2;
		$nbt = new CompoundTag();
		$nbt->setString(self::TAG_WEAPON_ID, $id);
		$nbt->setString(self::TAG_TYPE, 'weapon');
		$nbt->setString(self::TAG_PROPERTY, $type);
		$nbt->setString(self::TAG_LEVEL, $lv);
		$nbt->setString(self::TAG_EXP, $exp);
		$nbt->setString(self::TAG_ATK, $atk);
		$nbt->setString(self::TAG_SHARP, $sharp);
		$nbt->setString(self::TAG_RARE, $rare);
		//$nbt->setString(self::TAG_PREFIX, 0);
		$nbt->setString(self::TAG_REROLL_COUNT, 0);
		$nbt->setIntArray(self::TAG_BAIRITU, $bairitus);
		$nbt->setString(self::TAG_SKILL, $data['skill']);
		$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromString(md5(uniqid(mt_rand(), true))));
		$i = 2;
		/*foreach($data['status'] as $name => $amount){
			$amount *= round($bairitus[$i] / 100);
			$nbt->setString($name, (int)$amount);
			$lore[] = self::TAG_STATUS[$name].': +'.$amount;
			$i++;
		}*/
		if($data['skill'] !== 0){
			$name = SpecialSkillManager::getSkill($data['skill'])->getName();
			$description = SpecialSkillManager::getSkill($data['skill'])->getDes();
			$lore[] = "{$name}: {$description}";
		}
		//$item->setLore($lore);
		$item->getNamedTag()->setTag(self::TAG_WEAPON ,$nbt);
		$item->getNamedTag()->setTag("Unbreakable", new ByteTag(1));
		//$prefix = Prefix::make();
		//$nbt->setString(self::TAG_PREFIX, $prefix);
		$item->getNamedTag()->setTag(self::TAG_WEAPON, $nbt);
		//$subs = Prefix::getSub($prefix);
		$item = $this->setSubFix($item);
		$item = $this->setLore($item);
		$item = $this->setName($item);
		return $item;
	}
	
	public function onInteract(PlayerInteractEvent $event){
		$this->Interact($event);
	}
	
	public function Interact($event){
    }

	public function setSubFix($weapon){
		$tag = $weapon->getNamedTag()->getTag(self::TAG_WEAPON);
		$id = $tag->getTag(self::TAG_WEAPON_ID)->getValue();
		$subs = $this->plugin->swordData[$id]['prefix'];
		$count = 0;
		foreach($subs as $sub){
			$id = $sub[0]; $bairitu = $sub[1];
			$per = mt_rand(1, 100);
			$tag->setString(self::TAG_SUB[$count], $id);
			$tag->setString(self::TAG_SUBPER[$count], floor($bairitu * $per / 100));
			$tag->setString(self::TAG_SUBSTATE[$count], $per);
			$count ++;
		}
		$weapon->getNamedTag()->setTag(self::TAG_WEAPON, $tag);
		return $weapon;
	}


	public function removeAllSubFix($weapon){
		$tag = $weapon->getNamedTag()->getTag(self::TAG_WEAPON);
		$count = 0;
		for($i = 0; $i < 6; $i++){
			if(is_null($tag->getTag(self::TAG_SUB[$i]))) continue;
			$tag->setString(self::TAG_SUB[$i], -1);
			$tag->setString(self::TAG_SUBPER[$count], 0);
			$tag->setString(self::TAG_SUBSTATE[$count], 0);
		}
		$weapon->getNamedTag()->setTag(self::TAG_WEAPON, $tag);
		return $weapon;
	}

	public function setLore($weapon){
		$tag = $weapon->getNamedTag()->getTag(self::TAG_WEAPON);
		$atk = $tag->getTag(self::TAG_ATK)->getValue();
		$sharp = $tag->getTag(self::TAG_SHARP)->getValue();
		$type = $tag->getTag(self::TAG_PROPERTY)->getValue();
		$lv = $tag->getTag(self::TAG_LEVEL)->getValue();
		$rare = $tag->getTag(self::TAG_RARE)->getValue();
		$count = $tag->getTag(self::TAG_REROLL_COUNT)->getValue();
		$exp = $tag->getTag(self::TAG_EXP)->getValue();
		$message = '';
		if($rare > 10){
            $message .= '§e';
        }elseif($rare > 5){
            $message .= '§a';
        }else{
            $message .= '§e';
        }
        for($i = 0; $i < $rare; $i ++){
            $message .= '☆';
        }
		$next = (100*$rare*($lv+1));
		if($lv >= 8) $next = 0;
		$lore = ['§cATK: '.floor($atk), '§e切れ味: '.$sharp, '§a火力指数: §f'.floor(floor($atk) * ($sharp/300)),'§b属性: §f'.Type::getName($type),'§d強化: §f'.$lv.'Lv', 
		'§7EXP§f: '.$exp.'/'.$next, '§cRARE§f: '.$message];

		$skill = $this->isSetSkill($weapon);
		if($skill != 0){
			$name = SpecialSkillManager::getSkill($skill)->getName();
			$description = SpecialSkillManager::getSkill($skill)->getDes();
			$lore[] = "{$name}: {$description}";
		}
		$lore[] = '§8リロール回数§f: '.$count.'回';
		for($i = 0; $i < 6; $i++){
			if(is_null($tag->getTag(self::TAG_SUB[$i]))) continue;
			$sub = $tag->getTag(self::TAG_SUB[$i])->getValue();
			if($sub == -1) continue;
			$name = Prefix::SUB_PREFIX_NAME[$sub];
			$per = $tag->getTag(self::TAG_SUBPER[$i])->getValue();
			$pper = $tag->getTag(self::TAG_SUBSTATE[$i])->getValue();
			if($pper > 90) $pper = "§b".$pper;
			else if($pper > 50) $pper = "§a".$pper; 
			else if($pper > 20) $pper = "§e".$pper; 
			else $pper = "§c".$pper;
			$lore[] = "{$name}: §f+{$per}% [{$pper}§f%]";
		}
		$weapon->setLore($lore);
		return $weapon;
	}

	public function setName($weapon){
		$tag = $weapon->getNamedTag()->getTag(self::TAG_WEAPON);
		$id = $tag->getTag(self::TAG_WEAPON_ID)->getValue();
		$data = $this->plugin->swordData[$id];
		$lv = $tag->getTag(self::TAG_LEVEL)->getValue();
		//$prefix = $tag->getTag(self::TAG_PREFIX)->getValue();
		$sum = 0; $count = 0;
		for($i = 0; $i < 6; $i++){
			if(is_null($tag->getTag(self::TAG_SUB[$i]))) continue;
			$sub = $tag->getTag(self::TAG_SUB[$i])->getValue();
			if($sub == -1) continue;
			$pper = $tag->getTag(self::TAG_SUBSTATE[$i])->getValue();
			$sum += $pper;
			$count++;
		}
		if($count == 0)
			$weapon->setCustomName($data['name'].' §e+'.$lv.'§f');
		else
			$weapon->setCustomName(Prefix::getName(floor($sum/ $count)).'§r '.$data['name'].' §e+'.$lv.'§f');
		return $weapon;
	}
    
    public function setOnlySkill($item, $id){
		$tag = $item->getNamedTag(self::TAG_WEAPON)->getTag(self::TAG_WEAPON);
		if(!isset($tag)){
			echo('このアイテムにスキルは付きません');
			return false;
		}
		
		$data = $this->plugin->swordData[$id];
		if($data['skillid'] == 0) return $item;
		$tag->setString(self::TAG_SKILL, $data['skillid']);
		$lore = $item->getLore();
		$lore[] = '§aSKILL: '.WeaponSkillManager::getSkill($data['skillid'])->getName();
		$item->setLore($lore);
		$item->getNamedTag()->setTag(self::TAG_WEAPON, $tag);
		$ench = new ListTag(Item::TAG_ENCH, [], NBT::TAG_Compound);
        $ench->push(new CompoundTag("", [
          new ShortTag("id", -1),
          new ShortTag("lvl", 0)
		]));
		$item->getNamedTag()->setTag($ench);
		return $item;
	}

	public static function isWeapon($item){
		$result = false;
		$tag = $item->getNamedTag(self::TAG_WEAPON)->getTag(self::TAG_WEAPON);
		if(isset($tag)) $result = true;
		return $result;
	}

	public static function isSetSkill($item){
	  $result = null;
	  $tag = $item->getNamedTag()->getTag(self::TAG_WEAPON);
	  if(!isset($tag)) return $result;
	  $skill = $tag->getTag(Weapon::TAG_SKILL);
	  if(!isset($skill)) return $result;
	  $result = $skill->getValue();
	  return $result;
	}

	public function getCustomItem($data, $id, $rank){
		$atk = round($data['atk'] * $rank);
		$def = round($data['def'] * $rank);
		$sharp = round($data['sharp'] * $rank);
		$type = $data['type'];
		
		$item = ItemFactory::getInstance()->get($data['itemid'], 0, 1);
		$item->setLore(['§cATK: '.$atk, '§dDEF: '.$def, '§e切れ味: '.$sharp, '§a火力指数: §f'.floor($atk) * ($sharp/300), '§b属性: §f'.Type::getName($type),'§d強化: §f0Lv']);
		$item->setCustomName($data['name']);
		
		$nbt = new CompoundTag();
		$nbt->setString(self::TAG_WEAPON_ID, $id);
		$nbt->setString(self::TAG_TYPE, 'weapon');
		$nbt->setString(self::TAG_PROPERTY, $type);
		$nbt->setString(self::TAG_LEVEL, 0);
		$nbt->setString(self::TAG_ATK, $atk);
		$nbt->setString(self::TAG_DEF, $def);
		$nbt->setString(self::TAG_SHARP, $sharp);
		//$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromRandom()->toString());
		$item->getNamedTag()->setTag(self::TAG_WEAPON ,$nbt);
		$item->getNamedTag()->setTag("Unbreakable", new ByteTag(1));
		return $item;
	}

	public function setSkill($item, $skillid){
		$tag = $item->getNamedTag(self::TAG_WEAPON)->getTag(self::TAG_WEAPON);
		if(!isset($tag)){
			echo('このアイテムにスキルは付きません');
			return false;
		}
		
		$data = ['skillid' => $skillid];
		if($data['skillid'] == 0) return $item;
		$tag->setString(self::TAG_SKILL, $data['skillid']);
		$lore = $item->getLore();
		$lore[] = '§aSKILL: '.WeaponSkillManager::getSkill($data['skillid'])->getName();
		$item->setLore($lore);
		$item->getNamedTag()->setTag(self::TAG_WEAPON, $tag);
		$ench = new ListTag(Item::TAG_ENCH, [], NBT::TAG_Compound);
        $ench->push(new CompoundTag("", [
          new ShortTag("id", -1),
          new ShortTag("lvl", 0)
		]));
		$item->getNamedTag()->setTag($ench);
		return $item;
	}

}
