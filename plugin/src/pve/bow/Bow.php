<?php
namespace pve\bow;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\NBT;

use pve\Type;

class Bow {
	const ID = 0;
	const ITEM_ID = 0;
	const ATK = 0;
	const DEF = 0;
	const NAME = 'Error';
	const KANTSUU = 0;
	const SKILL_ID = 0;
	const SKILL_NAME = '';
	
	const TAG_bow = 'bow';
	const TAG_bow_ID = "bow_id";
	const TAG_TYPE = "type";
	const TAG_PROPERTY = "property";
	const TAG_UNIQUE_ID = "unique_id";
	const TAG_LEVEL = "level";
	const TAG_EXP = "exp";
	const TAG_ATK = 'atk';
	const TAG_DEF = 'def';
	const TAG_KANTSUU = 'kantsuu';
	const TAG_SKILL = 'skill';
	const TAG_RARE = 'rare';
	
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
	
	public function getKantsuu(){
		return static::KANTSUU;
	}
	
	public function getItemId(){
		return static::ITEM_ID;
	}
	
	public function getName(){
		return static::NAME;
	}
	
	public function getItem($id, $bairitu, $bairitu2){
		$data = $this->plugin->bowData[$id];
		$atk = round($data['atk'] * $bairitu);
		$kantsuu = round($data['kantsuu'] * $bairitu2);
		$rare = $data['rare'];
		$type = $data['type'];
		$next = 100 * $rare;
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
		$item->setLore(['§cATK: '.$atk, '§e貫通力: '.$kantsuu, '§b属性: §f'.Type::getName($type),'§d強化: §f0Lv', 
		  '§7EXP§f: 0/'.$next, '§cRARE§f: '.$message]);
		$item->setCustomName($data['name']);
		
		$nbt = new CompoundTag(self::TAG_bow);
		$nbt->setString(self::TAG_bow_ID, $id);
		$nbt->setString(self::TAG_TYPE, 'bow');
		$nbt->setString(self::TAG_PROPERTY, $type);
		$nbt->setString(self::TAG_LEVEL, 0);
		$nbt->setString(self::TAG_EXP, 0);
		$nbt->setString(self::TAG_ATK, $atk);
		$nbt->setString(self::TAG_KANTSUU, $kantsuu);
		$nbt->setString(self::TAG_RARE, $rare);
		//$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromRandom()->toString());
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
		$tag = $item->getNamedTagEntry(self::TAG_bow);
		if(!isset($tag)){
			echo('このアイテムにスキルは付きません');
			return false;
		}
		
		$data = $this->plugin->swordData[$id];
		if($data['skillid'] == 0) return $item;
		$tag->setString(self::TAG_SKILL, $data['skillid']);
		$lore = $item->getLore();
		$lore[] = '§aSKILL: '.bowSkillManager::getSkill($data['skillid'])->getName();
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

	public static function isbow($item){
		$result = false;
		$tag = $item->getNamedTagEntry(self::TAG_bow);
		if(isset($tag)) $result = true;
		return $result;
	}

	public static function isSetSkill($item){
	  $result = null;
	  $tag = $item->getNamedTagEntry(self::TAG_bow);
	  if(!isset($tag)) return $result;
	  $skill = $tag->getTag(bow::TAG_SKILL);
	  if(!isset($skill)) return $result;
	  $result = $skill->getValue();
	  return $result;
	}

	public function getCustomItem($data, $id, $rank){
		$atk = round($data['atk'] * $rank);
		$def = round($data['def'] * $rank);
		$kantsuu = round($data['kantsuu'] * $rank);
		$type = $data['type'];
		
		$item = Item::get($data['itemid'], 0, 1);
		$item->setLore(['§cATK: '.$atk, '§dDEF: '.$def, '§e切れ味: '.$kantsuu, '§b属性: §f'.Type::getName($type),'§d強化: §f0Lv']);
		$item->setCustomName($data['name']);
		
		$nbt = new CompoundTag(self::TAG_bow);
		$nbt->setString(self::TAG_bow_ID, $id);
		$nbt->setString(self::TAG_TYPE, 'bow');
		$nbt->setString(self::TAG_PROPERTY, $type);
		$nbt->setString(self::TAG_LEVEL, 0);
		$nbt->setString(self::TAG_ATK, $atk);
		$nbt->setString(self::TAG_DEF, $def);
		$nbt->setString(self::TAG_KANTSUU, $kantsuu);
		//$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromRandom()->toString());
		$item->setNamedTagEntry($nbt);
		$item->setNamedTagEntry(new ByteTag("Unbreakable", 1));
		return $item;
	}

	public function setSkill($item, $skillid){
		$tag = $item->getNamedTagEntry(self::TAG_bow);
		if(!isset($tag)){
			echo('このアイテムにスキルは付きません');
			return false;
		}
		
		$data = ['skillid' => $skillid];
		if($data['skillid'] == 0) return $item;
		$tag->setString(self::TAG_SKILL, $data['skillid']);
		$lore = $item->getLore();
		$lore[] = '§aSKILL: '.bowSkillManager::getSkill($data['skillid'])->getName();
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