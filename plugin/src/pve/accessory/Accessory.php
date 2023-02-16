<?php
namespace pve\accessory;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use Ramsey\Uuid\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\NBT;

use pve\SkillManager;
use pve\Type;
use pve\prefix\Prefix;

class Accessory {
	const ID = 0;
	const ITEM_ID = 0;
	const ATK = 0;
	const DEF = 0;
	const TYPE = Type::NONE;
	const NAME = 'Error';
	const SKILL_ID = 0;
	const SKILL_LV = 0;
	
	
	const TAG_ACCESSORY = 'accessory';
	const TAG_ACCESSORY_ID = "accessory_id";
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
	
	public function getItem($id, $bairitu, $slot, $skill){
		$data = $this->plugin->accessoryData[$id];
		$def = round($data['def'] * $bairitu);
		$atk = round($data['atk'] * $bairitu);
		
		$item = ItemFactory::getInstance()->get($data['itemid'], 0, 1);
		$name = $this->plugin->accessoryData[$id]['name'];
		$item->setCustomName($name);
		
		$nbt = new CompoundTag();
		$nbt->setString(self::TAG_ACCESSORY_ID, $id);
		$nbt->setString(self::TAG_TYPE, 'accessory');
		$nbt->setString(self::TAG_SLOT, $slot);
		$nbt->setString(self::TAG_RARE, $data['rare']);
		//$nbt->setString(self::TAG_PREFIX, 0);
		$nbt->setString(self::TAG_DEF, $def);
		$nbt->setString(self::TAG_ATK, $atk);
		$nbt->setString(self::TAG_REROLL_COUNT, 0);
		$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromString(md5(uniqid(mt_rand(), true))));

		//$lore[] = '§7EXP§f: 0/'.(100*$data['rare']);
		/*foreach($data['status'] as $name => $amount){
			$amount *= mt_rand(0.8, 1.2);
			$nbt->setString($name, round($amount));
			$lore[] = self::TAG_STATUS[$name].': +'.$amount;
		}*/

		$item->getNamedTag()->setTag(self::TAG_ACCESSORY, $nbt);
		$item->getNamedTag()->setTag("Unbreakable", new ByteTag(1));
		if($skill) $item = $this->setOnlySkill($item, $id);
		//$prefix = Prefix::make();
		//$nbt->setString(self::TAG_PREFIX, $prefix);
		$item->getNamedTag()->setTag(self::TAG_ACCESSORY, $nbt);
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
		$tag = $armor->getNamedTag()->getTag(self::TAG_ACCESSORY);
		$id = $tag->getTag(self::TAG_ACCESSORY_ID)->getValue();
		$subs = $this->plugin->accessoryData[$id]['prefix'];
		$count = 0;
		foreach($subs as $sub){
			$id = $sub[0]; $bairitu = $sub[1];
			$per = mt_rand(1, 100);
			$tag->setString(self::TAG_SUB[$count], $id);
			$tag->setString(self::TAG_SUBPER[$count], floor($bairitu * $per / 100));
			$tag->setString(self::TAG_SUBSTATE[$count], $per);
			$count ++;
		}
		$armor->getNamedTag()->setTag(self::TAG_ACCESSORY, $tag);
		return $armor;
	}

	public function removeAllSubFix($armor){
		$tag = $armor->getNamedTag()->getTag(self::TAG_ACCESSORY);
		$count = 0;
		for($i = 0; $i < 6; $i++){
			if(is_null($tag->getTag(self::TAG_SUB[$i]))) continue;
			$tag->setString(self::TAG_SUB[$i], -1);
			$tag->setString(self::TAG_SUBPER[$count], 0);
			$tag->setString(self::TAG_SUBSTATE[$count], 0);
		}
		$armor->getNamedTag()->setTag(self::TAG_ACCESSORY, $tag);
		return $armor;
	}

	public function setLore($armor){
		$tag = $armor->getNamedTag()->getTag(self::TAG_ACCESSORY);
		$slot = $tag->getTag(self::TAG_SLOT)->getValue();
		$rare = $tag->getTag(self::TAG_RARE)->getValue();
		$atk = $tag->getTag(self::TAG_ATK)->getValue();
		$def = $tag->getTag(self::TAG_DEF)->getValue();
		$count = $tag->getTag(self::TAG_REROLL_COUNT)->getValue();
        $lore = ['§cATK: '.floor($atk), '§dDEF: '.floor($def)];
		for($i = 0; $i < $slot; $i++){
			$skill = $tag->getTag(self::TAG_SKILLS[$i]);
			if(!isset($skill)){
				$lore[] = '§aSKILL'.($i+1).'§f: --';
				continue;
			}
			$id = $skill->getValue();
			$lv = $tag->getTag(self::TAG_SKILL_LV[$i])->getValue();
			$lore[] = '§aSKILL'.($i+1).'§f: '.SkillManager::getSkill($id)->getName().'§r[§clv§r: '.$lv.']';
		}
		//$lore[] = '§7EXP§f: 0/'.(100*$data['rare']);
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
		$tag = $armor->getNamedTag()->getTag(self::TAG_ACCESSORY);
		$id = $tag->getTag(self::TAG_ACCESSORY_ID)->getValue();
		$data = $this->plugin->armorData[$id];
		//$prefix = $tag->getTag(self::TAG_PREFIX)->getValue();
		$name = $this->plugin->accessoryData[$id]['name'];
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
			$armor->setCustomName($name);
		else
			$armor->setCustomName(Prefix::getName(floor($sum / $count)).'§r '.$name);
		return $armor;
	}
    
    public function setOnlySkill($item, $id){
		$data = $this->plugin->accessoryData[$id];
		$tag = $item->getNamedTag(self::TAG_ACCESSORY)->getTag(self::TAG_ACCESSORY);
		if(!isset($tag)){
			return false;
		}
		$tag->setString(self::TAG_SKILLS[0], $data['skillid']);
		$tag->setString(self::TAG_SKILL_LV[0], $data['skilllv']);
		$tag->setString(self::TAG_SKILL_ORB[0], 0);
		$lore = $item->getLore();
		$lore[2] = '§aSKILL1: '.SkillManager::getSkill($data['skillid'])->getName().'[lv: '.$data['skilllv'].']';
		$item->setLore($lore);
		$item->getNamedTag()->setTag(self::TAG_ACCESSORY, $tag);
		/*$ench = new ListTag(Item::TAG_ENCH, [], NBT::TAG_Compound);
        $ench->push(new CompoundTag("", [
          new ShortTag("id", -1),
          new ShortTag("lvl", 0)
		]));*/
		//$item->getNamedTag()->setTag($ench);
		return $item;
	}
	
	public function setSkill($item, $id, $lv, $pos){
		$tag = $item->getNamedTag(self::TAG_ACCESSORY)->getTag(self::TAG_ACCESSORY);
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

		$item->getNamedTag()->setTag(self::TAG_ACCESSORY, $tag);
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
		$nbt->setString(self::TAG_ACCESSORY_ID, $id);
		$nbt->setString(self::TAG_LEVEL, 0);
		$nbt->setString(self::TAG_TYPE, 'armor');
		$nbt->setString(self::TAG_PROPERTY, $type);
		$nbt->setString(self::TAG_DEF, $def);
		//$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromRandom()->toString());
		$item->getNamedTag()->setTag(self::TAG_ACCESSORY, $nbt);
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