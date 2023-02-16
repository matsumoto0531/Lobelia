<?php
namespace pve\weapon;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\NBT;

use pve\SpecialSkillManager;
use pve\WeaponSkillManager;
use pve\Type;

class Weapon {
	const ID = 0;
	const ITEM_ID = 0;
	const ATK = 0;
	const DEF = 0;
	const NAME = 'Error';
	const SHARP = 0;
	const SKILL_ID = 0;
	const SKILL_NAME = '';
	
	const TAG_PICKAXE = 'pickaxe';
	const TAG_PICKAXE_ID = "pickaxe_id";
	const TAG_TYPE = "type";
	const TAG_PROPERTY = "property";
	const TAG_UNIQUE_ID = "unique_id";
	const TAG_LEVEL = "level";
	const TAG_EXP = "exp";
	const TAG_ATK = 'atk';
	const TAG_DEF = 'def';
	const TAG_SHARP = 'sharp';
	const TAG_SKILL = 'skill';
	const TAG_RARE = 'rare';
	const TAG_BAIRITU = 'bairitu';
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
		$atk = round($data['atk'] * $bairitus[0]/100);
		$sharp = round($data['sharp'] * $bairitus[1]/100);
		$rare = $data['rare'];
		$type = $data['type'];
		$next = 100 * $rare;
		$lv = 0;
		while($exp >= $next){
			$lv++;
			if($lv >= 16){
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
		
		$item = Item::get($data['itemid'], 0, 1);
		$lore = ['§cATK: '.$atk, '§e切れ味: '.$sharp, '§b属性: §f'.Type::getName($type),'§d強化: §f'.$lv.'Lv', 
		'§7EXP§f:'.$exp.'/'.$next, '§cRARE§f: '.$message];
		$item->setCustomName($data['name']);
		$exp += (100 * $rare * (1+$lv) * $lv) / 2;
		$nbt = new CompoundTag(self::TAG_PICKAXE);
		$nbt->setString(self::TAG_PICKAXE_ID, $id);
		$nbt->setString(self::TAG_TYPE, 'pickaxe');
		$nbt->setString(self::TAG_PROPERTY, $type);
		$nbt->setString(self::TAG_LEVEL, $lv);
		$nbt->setString(self::TAG_EXP, $exp);
		$nbt->setString(self::TAG_ATK, $atk);
		$nbt->setString(self::TAG_SHARP, $sharp);
		$nbt->setString(self::TAG_RARE, $rare);
		$nbt->setIntArray(self::TAG_BAIRITU, $bairitus);
		$nbt->setString(self::TAG_SKILL, $data['skill']);
		$i = 2;
		foreach($data['status'] as $name => $amount){
			$amount *= round($bairitus[$i] / 100);
			$nbt->setString($name, (int)$amount);
			$lore[] = self::TAG_STATUS[$name].': +'.$amount;
			$i++;
		}
		if($data['skill'] !== 0){
			$name = SpecialSkillManager::getSkill($data['skill'])->getName();
			$description = SpecialSkillManager::getSkill($data['skill'])->getDes();
			$lore[] = "{$name}: {$description}";
		}
		$item->setLore($lore);
		$item->setNamedTagEntry($nbt);
		$item->setNamedTagEntry(new ByteTag("Unbreakable", 1));
		return $item;
	}
	
	public function onInteract(PlayerInteractEvent $event){
		$this->Interact($event);
	}
	
	public function Interact($event){
    }
    
    public function setOnlySkill($item, $id){
		$tag = $item->getNamedTagEntry(self::TAG_WEAPON);
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
		$item->setNamedTagEntry($tag);
		$ench = new ListTag(Item::TAG_ENCH, [], NBT::TAG_Compound);
        $ench->push(new CompoundTag("", [
          new ShortTag("id", -1),
          new ShortTag("lvl", 0)
		]));
		$item->setNamedTagEntry($ench);
		return $item;
	}

	public static function isWeapon($item){
		$result = false;
		$tag = $item->getNamedTagEntry(self::TAG_WEAPON);
		if(isset($tag)) $result = true;
		return $result;
	}

	public static function isSetSkill($item){
	  $result = null;
	  $tag = $item->getNamedTagEntry(self::TAG_WEAPON);
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
		
		$item = Item::get($data['itemid'], 0, 1);
		$item->setLore(['§cATK: '.$atk, '§dDEF: '.$def, '§e切れ味: '.$sharp, '§b属性: §f'.Type::getName($type),'§d強化: §f0Lv']);
		$item->setCustomName($data['name']);
		
		$nbt = new CompoundTag(self::TAG_WEAPON);
		$nbt->setString(self::TAG_WEAPON_ID, $id);
		$nbt->setString(self::TAG_TYPE, 'weapon');
		$nbt->setString(self::TAG_PROPERTY, $type);
		$nbt->setString(self::TAG_LEVEL, 0);
		$nbt->setString(self::TAG_ATK, $atk);
		$nbt->setString(self::TAG_DEF, $def);
		$nbt->setString(self::TAG_SHARP, $sharp);
		//$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromRandom()->toString());
		$item->setNamedTagEntry($nbt);
		$item->setNamedTagEntry(new ByteTag("Unbreakable", 1));
		return $item;
	}

	public function setSkill($item, $skillid){
		$tag = $item->getNamedTagEntry(self::TAG_WEAPON);
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
		$item->setNamedTagEntry($tag);
		$ench = new ListTag(Item::TAG_ENCH, [], NBT::TAG_Compound);
        $ench->push(new CompoundTag("", [
          new ShortTag("id", -1),
          new ShortTag("lvl", 0)
		]));
		$item->setNamedTagEntry($ench);
		return $item;
	}

}