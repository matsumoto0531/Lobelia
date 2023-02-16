<?php
namespace pve;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\NBT;

use Ramsey\Uuid\Uuid;

use pve\item\ItemManager;

class Recipe { 

    const TAG_RECIPE = 'recipe';
    const TAG_TYPE = 'type';
    const TAG_ID = 'id';
    const TAG_UNIQUE_ID = 'uuid';
    const TAG_KAISUU = 'kaisuu';
    const TAG_BAIRITU = 'bairitu';
    const TAG_BAIRITU2 = 'bairitu2';
    const TAG_BAIRITU3 = 'bairitu3';
    const BAIRITUS = 'bairitus';
    const TAG_SLOT = 'slot';
    const TAG_SKILL = 'skill';
    const TAG_POS = 'pos';
    const TAG_LV = 'lv';

    const TAG_STATUS = ['pow', 'agi', 'han', 'body', 'syu', 'magic'];

    const NAMES = [
      'JP' => ['兜', '胸当て', '腰当て', '靴'],
      'EN' => ['ヘルメット', 'プレート', 'レギンス', 'ブーツ']
    ];

    const ITEM_ARMOR_IDS = [1101, 1100, 1102, 1099, 1103, 1098];
    const ITEM_WEAPON_IDS = [1107, 1106, 1108, 1105, 1109, 1104];
    const ITEM_ORB_IDS = [1110];

    public function __construct($plugin){
        $this->plugin = $plugin;
    }

    public function getArmorRecipe($ida, $count){
      $data = $this->plugin->recipeArmorData[$ida];
      $type = $this->plugin->armorData[$data['make']]['type'];
      $item = ItemFactory::getInstance()->get(self::ITEM_ARMOR_IDS[$type]);
      $name = $this->plugin->armorData[$data['make']]['name'];
      $pos = mt_rand(0, 3);
      $n = self::NAMES[$this->plugin->armorData[$data['make']]['bui']][$pos];
      $name = str_replace("%1", $n, $name);
      $bairitu = mt_rand(9, 11);
      $slot = mt_rand(0, 3);
      $skill = 0;
      if($slot > 1){
        if(mt_rand(0, 10) > 7) $skill = 1;
      }
      $dat['type'] = '防具';
      $dat['rare'] = $this->plugin->armorData[$data['make']]['rare'];
      $lore = [
        '§7詳細§f ',
        '§6DEF§f: '.round($this->plugin->armorData[$data['make']]['def'] * $bairitu / 10),
        '§bSLOT§f: '.$slot.'個',
        '§cSKILL§f: '.($skill ? 'あり' : 'なし')
      ];
      foreach($data['items'] as $id => $amount){
        $dat['sozai'][$this->plugin->itemData[$id]['name']] = $amount;
      }
      foreach($data['armors'] as $id => $amount){
        $n = $this->plugin->armorData[$id]['name'];
        $bui = self::NAMES[$this->plugin->armorData[$id]['bui']][$pos];
        $n = str_replace("%1", $bui, $n);
        $dat['sozai'][$n] = $amount;
      }
      foreach($data['ores'] as $id => $amount){
        $dat['sozai'][ItemManager::getItem($id)->getName()] = $amount;
      }
      $item = $this->makeRecipe($name, $count, $dat, $item);
      $item->setLore(array_merge($item->getLore(), ['  '], $lore));
      $nbt = new CompoundTag();
      $nbt->setString(self::TAG_TYPE, 'armor');
		  $nbt->setString(self::TAG_ID, $ida);
      $nbt->setString(self::TAG_KAISUU, $count);
      $nbt->setString(self::TAG_BAIRITU, $bairitu);
      $nbt->setString(self::TAG_SLOT, $slot);
      $nbt->setString(self::TAG_SKILL, $skill);
      $nbt->setString(self::TAG_POS, $pos);
		  $item->getNamedTag()->setTag(self::TAG_RECIPE, $nbt);
      $item->getNamedTag()->setTag("Unbreakable", new ByteTag(1));
      if($skill){
        $ench = new ListTag([], NBT::TAG_Compound);
        $entag = new CompoundTag();
        $entag->setShort("id", -1);
        $entag->setShort("lvl", 0);
        $ench->push($entag);
        $item->getNamedTag()->setTag(Item::TAG_ENCH, $ench);
      }
      return $item;
    }

    public function getSwordRecipe($ida, $count){
      $data = $this->plugin->recipeSwordData[$ida];
      $type = $this->plugin->swordData[$data['make']]['type'];
      $item = ItemFactory::getInstance()->get(self::ITEM_WEAPON_IDS[$type]);
      $name = $this->plugin->swordData[$data['make']]['name'];
      $bairitus = [];
      $bairitus[] = mt_rand(90, 110);
      $bairitus[] = mt_rand(90, 110);
      $status = $this->plugin->swordData[$data['make']]['status'];
      foreach($status as $amount){
        $bairitus[] = mt_rand(90, 110);
      }
      $c = 0;
      $sum = 0;
      foreach($bairitus as $a){
        $sum += $a;
        $c ++;
      }
      $ave = $sum / $c;
      if($ave < 95)
        $strong = '§bⅠ';
      elseif($ave < 100)
        $strong = '§aⅡ';
      elseif($ave < 105)
        $strong = '§eⅢ';
      elseif($ave < 110)
        $strong = '§6Ⅳ';
      else
       $strong = '§cⅤ';
      $dat['type'] = '剣';
      $dat['rare'] = $this->plugin->swordData[$data['make']]['rare'];
      $lore = [
        '§6つよさ§f: '.$strong.'§f'
      ];
      foreach($data['items'] as $id => $amount){
        $dat['sozai'][$this->plugin->itemData[$id]['name']] = $amount;
      }
      foreach($data['swords'] as $id => $amount){
        $dat['sozai'][$this->plugin->swordData[$id]['name']] = $amount;
      }
      foreach($data['ores'] as $id => $amount){
        $dat['sozai'][ItemManager::getItem($id)->getName()] = $amount;
      }
      $item = $this->makeRecipe($name, $count, $dat, $item);
      $item->setLore(array_merge($item->getLore(), ['  '], $lore));
      $nbt = new CompoundTag();
      $nbt->setString(self::TAG_TYPE, 'sword');
		  $nbt->setString(self::TAG_ID, $ida);
      $nbt->setString(self::TAG_KAISUU, $count);
      $nbt->setIntArray(self::BAIRITUS, $bairitus);
		  $item->getNamedTag()->setTag(self::TAG_RECIPE, $nbt);
      if($ave > 104){
        $ench = new ListTag([], NBT::TAG_Compound);
        $entag = new CompoundTag();
        $entag->setShort("id", -1);
        $entag->setShort("lvl", 0);
        $ench->push($entag);
        $item->getNamedTag()->setTag(Item::TAG_ENCH, $ench);
      }
      return $item;
    }

    public function getOrbRecipe($ida, $count){
      $data = $this->plugin->recipeOrbData[$ida];
      $item = ItemFactory::getInstance()->get(self::ITEM_ORB_IDS[0]);
      $class = SkillManager::getSkill($data['make']);
      $name = $class->getName();
      $dat['type'] = 'オーブ';
      $dat['rare'] = $data['rare'];
      $lore = [
        '§7詳細§f ',
        '§b効果§f: '.$class->getDes()
      ];
      foreach($data['items'] as $id => $amount){
        $dat['sozai'][$this->plugin->itemData[$id]['name']] = $amount;
      }
      foreach($data['ores'] as $id => $amount){
        $dat['sozai'][ItemManager::getItem($id)->getName()] = $amount;
      }
      $item = $this->makeRecipe($name, $count, $dat, $item);
      $item->setCustomName($item->getName().' §f lv: '.$data['lv']);
      $item->setLore(array_merge($item->getLore(), ['  '], $lore));
      $nbt = new CompoundTag();
      $nbt->setString(self::TAG_TYPE, 'orb');
      $nbt->setString(self::TAG_ID, $ida);
      $nbt->setString(self::TAG_LV, $data['lv']);
      $nbt->setString(self::TAG_KAISUU, $count);
		  $item->getNamedTag()->setTag(self::TAG_RECIPE ,$nbt);
      return $item;
    }

    public function makeRecipe($name, $count, $data, $item){
        $item->setCustomName(
            '§l§dレシピ§f§r: '.$name
        );
        $lore = $item->getLore(); 
        $lore = ['§a種類§f: '.$data['type']];
        $message = '§bRare§f: ';
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
        $lore[] = $message;
        $lore[] = '§c残り使用可能回数§f: '.$count.'回';
        $count = 1;
        foreach($data['sozai'] as $name => $amount){
          $lore[] = '§d素材§f'.$count.' :'.$name.'x'.$amount;
          $count++;
        }
        $item->setLore($lore);
        return $item;
    }

}