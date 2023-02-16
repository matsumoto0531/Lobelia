<?php
namespace pve\armor;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\NBT;

use pve\SkillManager;
use pve\Type;
use pve\prefix\Prefix;

class Armor {
	const ID = 0;
	const ITEM_ID = 0;
	const ATK = 0;
	const DEF = 0;
	const TYPE = Type::NONE;
	const NAME = 'Error';
	const SKILL_ID = 0;
	const SKILL_LV = 0;
	
	
	const TAG_ARMOR = 'armor';
	const TAG_ARMOR_ID = "armor_id";
	const TAG_TYPE = "type";
	const TAG_PROPERTY = "property";
	const TAG_UNIQUE_ID = "unique_id";
	const TAG_LEVEL = "level";
	const TAG_ATK = 'atk';
	const TAG_DEF = 'def';
	const TAG_EXP = 'exp';
	const TAG_POS = 'pos';
	const TAG_SLOT = 'slot';
	const TAG_RARE = 'rare';
    const TAG_SKILLS = ['skill1', 'skill2', 'skill3'];
	const TAG_SKILL_LV = ['skilllv1', 'skillllv2', 'skilllv3'];
	const TAG_SKILL_ORB = ['sorb1', 'sorb2', 'sorb3'];
	const TAG_PREFIX = 'prefix';
	const TAG_REROLL_COUNT = 'pcount';
	const TAG_SUB = ['sub1', 'sub2', 'sub3', 'sub4', 'sub5', 'sub6'];
	const TAG_SUBPER = ['sp1', 'sp2', 'sp3', 'sp4', 'sp5', 'sp6'];
	const TAG_SUBSTATE = ['ss1', 'ss2', 'ss3', 'ss4', 'ss5', 'ss6'];
	//const TAG_STATUS = ['pow' => '§cちから§f', 'agi' => '§bすばやさ§f', 'han' => '§7はんだんりょく§f', 'body' => '§6からだ§f', 'syu' => '§0しゅうちゅうりょく§f', 'magic' => '§aまりょく§f'];
	const BUI = [0.9, 1.2, 1.1, 0.8];
	const NAMES = [
		'JP' => ['兜', '胸当て', '腰当て', '靴'],
		'EN' => ['ヘルメット', 'プレート', 'レギンス', 'ブーツ']
	];
	
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
	
	public function getItem($id, $bairitu, $pos, $slot, $skill){
		$data = $this->plugin->armorData[$id];
		$def = round($data['def']);
		$type = $data['type'];
		
		$item = ItemFactory::getInstance()->get($data['itemid'], 0, 1);
		$name = $this->plugin->armorData[$id]['name'];
		//$n = self::NAMES[$this->plugin->armorData[$id]['bui']][$pos];
        //$name = str_replace("%1", $n, $name);
		$item->setCustomName($name);
		
		$nbt = new CompoundTag();
		$nbt->setString(self::TAG_ARMOR_ID, $id);
		$nbt->setString(self::TAG_LEVEL, 0);
		$nbt->setString(self::TAG_EXP, 0);
		$nbt->setString(self::TAG_TYPE, 'armor');
		$nbt->setString(self::TAG_PROPERTY, $type);
		$nbt->setString(self::TAG_DEF, $def);
		$nbt->setString(self::TAG_POS, $data['pos']);
		$nbt->setString(self::TAG_SLOT, $slot);
		$nbt->setString(self::TAG_RARE, $data['rare']);
		//$nbt->setString(self::TAG_PREFIX, 0);
		$nbt->setString(self::TAG_REROLL_COUNT, 0);

		$lore = ['§dDEF: '.$def, '§b属性: §f'.Type::getName($type)];
		$lore[] = '§d強化: §f0Lv';
		for($i = 0; $i < $slot; $i++){
			$lore[] = '§aSKILL'.($i+1).'§f: --';
		}
		$lore[] = '§7EXP§f: 0/'.(100*$data['rare']);
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
		$lore[] = '§cRARE§f: '.$message;
		/*foreach($data['status'] as $name => $amount){
			$amount *= mt_rand(0.8, 1.2);
			$nbt->setString($name, round($amount));
			$lore[] = self::TAG_STATUS[$name].': +'.$amount;
		}*/
		$item->setLore($lore);

		$item->getNamedTag()->setTag(self::TAG_ARMOR, $nbt);
		$item->getNamedTag()->setTag("Unbreakable", new ByteTag(1));
		if($skill) $item = $this->setOnlySkill($item, $id);
		//$prefix = Prefix::make();
		//$nbt->setString(self::TAG_PREFIX, $prefix);
		$item->getNamedTag()->setTag(self::TAG_ARMOR, $nbt);
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

	public function setSubFix($armor){
		$tag = $armor->getNamedTag()->getTag(self::TAG_ARMOR);
		$id = $tag->getTag(self::TAG_ARMOR_ID)->getValue();
		$subs = $this->plugin->armorData[$id]['prefix'];
		$count = 0;
		foreach($subs as $sub){
			$id = $sub[0]; $bairitu = $sub[1];
			$per = mt_rand(1, 100);
			$tag->setString(self::TAG_SUB[$count], $id);
			$tag->setString(self::TAG_SUBPER[$count], floor($bairitu * $per / 100));
			$tag->setString(self::TAG_SUBSTATE[$count], $per);
			$count ++;
		}
		$armor->getNamedTag()->setTag(self::TAG_ARMOR, $tag);
		return $armor;
	}

	public function removeAllSubFix($armor){
		$tag = $armor->getNamedTag()->getTag(self::TAG_ARMOR);
		$count = 0;
		for($i = 0; $i < 6; $i++){
			if(is_null($tag->getTag(self::TAG_SUB[$i]))) continue;
			$tag->setString(self::TAG_SUB[$i], -1);
			$tag->setString(self::TAG_SUBPER[$count], 0);
			$tag->setString(self::TAG_SUBSTATE[$count], 0);
		}
		$armor->getNamedTag()->setTag(self::TAG_ARMOR, $tag);
		return $armor;
	}

	public function setLore($armor){
		$tag = $armor->getNamedTag()->getTag(self::TAG_ARMOR);
		$def = $tag->getTag(self::TAG_DEF)->getValue();
		$type = $tag->getTag(self::TAG_PROPERTY)->getValue();
		$slot = $tag->getTag(self::TAG_SLOT)->getValue();
		$rare = $tag->getTag(self::TAG_RARE)->getValue();
		$count = $tag->getTag(self::TAG_REROLL_COUNT)->getValue();
		$exp = $tag->getTag(self::TAG_EXP)->getValue();
		$lore = ['§dDEF: '.floor($def), '§b属性: §f'.Type::getName($type)];
		$lore[] = '§d強化: §f0Lv';
		for($i = 0; $i < $slot; $i++){
			$skill = $tag->getTag(self::TAG_SKILLS[$i]);
			if(!isset($skill)){
				$lore[] = '§aSKILL'.($i+1).'§f: --';
				continue;
			}
			$id = $skill->getValue();
			$lv = $tag->getTag(self::TAG_SKILL_LV[$i])->getValue();
			$lore[] = '§aSKILL'.($i+1).'§f: '.SkillManager::getSkill($id)->getName().'[lv: '.$lv.']';
		}
		$lore[] = '§7EXP§f: '.$exp.'/'.(100*$rare);
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
		$lore[] = '§cRARE§f: '.$message;
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
		$armor->setLore($lore);
		return $armor;
	}

	public function setName($armor){
		$tag = $armor->getNamedTag()->getTag(self::TAG_ARMOR);
		$id = $tag->getTag(self::TAG_ARMOR_ID)->getValue();
		$pos = $tag->getTag(self::TAG_POS)->getValue();
		$data = $this->plugin->armorData[$id];
		$lv = $tag->getTag(self::TAG_LEVEL)->getValue();
		//$prefix = $tag->getTag(self::TAG_PREFIX)->getValue();
		//$n = self::NAMES[$this->plugin->armorData[$id]['bui']][$pos];
		$name = $this->plugin->armorData[$id]['name'];
        //$name = str_replace("%1", $n, $name);
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
			$armor->setCustomName($name.' §e+'.$lv.'§f');
		else
			$armor->setCustomName(Prefix::getName(floor($sum/ $count)).'§r '.$name.' §e+'.$lv.'§f');
		return $armor;
	}
    
    public function setOnlySkill($item, $id){
		$data = $this->plugin->armorData[$id];
		$tag = $item->getNamedTag(self::TAG_ARMOR)->getTag(self::TAG_ARMOR);
		if(!isset($tag)){
			return false;
		}
		$tag->setString(self::TAG_SKILLS[0], $data['skillid']);
		$tag->setString(self::TAG_SKILL_LV[0], $data['skilllv']);
		$tag->setString(self::TAG_SKILL_ORB[0], 0);
		//$lore = $item->getLore();
		//$lore[3] = '§aSKILL1: '.SkillManager::getSkill($data['skillid'])->getName().'[lv: '.$data['skilllv'].']';
		//$item->setLore($lore);
		$item->getNamedTag()->setTag(self::TAG_ARMOR, $tag);
		/*$ench = new ListTag(Item::TAG_ENCH, [], NBT::TAG_Compound);
        $ench->push(new CompoundTag("", [
          new ShortTag("id", -1),
          new ShortTag("lvl", 0)
		]));*/
		//$item->getNamedTag()->setTag($ench);
		$item = $this->setLore($item);
		return $item;
	}
	
	public function setSkill($item, $id, $lv, $pos){
		$tag = $item->getNamedTag(self::TAG_ARMOR)->getTag(self::TAG_ARMOR);
		if(!isset($tag)){
			echo('このアイテムにスキルは付きません');
			return false;
		}
		$slot = $tag->getTag(self::TAG_SLOT)->getValue();
		if($slot < $pos) return false;
		$tag->setString(self::TAG_SKILLS[$pos], $id);
		$tag->setString(self::TAG_SKILL_LV[$pos], $lv);
		$tag->setString(self::TAG_SKILL_ORB[$pos], 1);
		//$lore = $item->getLore();
		//$lore[2+$pos] = '§aSKILL'.($pos+1).'§f: '.SkillManager::getSkill($id)->getName().'[lv: '.$lv.']';
		$item = $this->setLore($item);

		$item->getNamedTag()->setTag(self::TAG_ARMOR, $tag);
		return $item;
	}
	
	public function onSet($data){
		return $data;
	}

	public function getCustomItem($data, $id, $rank){
		$def = round($data['def'] * $rank);
		$type = $data['type'];
		
		$item = Item::get($data['itemid'], 0, 1);
		$item->setLore(['§dDEF: '.$def, '§b属性: §f'.Type::getName($type), '§aSKILL1§f: --', '§aSKILL2§f: --', '§aSKILL3§f: --','§d強化: §f0Lv']);
		$item->setCustomName($data['name']);
		
		$nbt = new CompoundTag();
		$nbt->setString(self::TAG_ARMOR_ID, $id);
		$nbt->setString(self::TAG_LEVEL, 0);
		$nbt->setString(self::TAG_TYPE, 'armor');
		$nbt->setString(self::TAG_PROPERTY, $type);
		$nbt->setString(self::TAG_DEF, $def);
		//$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromRandom()->toString());
		$item->getNamedTag()->setTag(self::TAG_ARMOR, $nbt);
		$item->getNamedTag()->setTag("Unbreakable", new ByteTag(1));
		return $item;
	}

	public function addEnch($item){
		$ench = new ListTag(Item::TAG_ENCH, [], NBT::TAG_Compound);
        $ench->push(new CompoundTag("", [
          new ShortTag("id", -1),
          new ShortTag("lvl", 0)
		]));
		$item->getNamedTag()->setTag($ench);
		return $item;
	}

}
