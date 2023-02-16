<?php
namespace pve;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use Ramsey\Uuid\UUID;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties as EM;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;

use pve\WeaponManager;
use pve\ArmorManager;
use pve\item\ItemManager;
use pve\item\PveItem;
use pve\armor\Armor;
use pve\weapon\Weapon;
use pve\accessory\Accessory;
use pve\skill\Skill;
use pve\mobs\Mobs;
use pve\MobManager;
use pve\Recipe;
use pve\prefix\Prefix;
use pve\inventory\ForgeInventory;

class Forge implements Listener{
	
	const NAME = '鍛冶屋';
	const MAKING_FORM_ID = 209;
	const ARMOR_FORM_ID = 210;
	const WEAPON_FORM_ID = 211;
	const ORB_FORM_ID = 212;
	const SELECT_ORB_ID = 213;
  const SELECT_POS_ID = 214;
  const STR_ID = 215;
  const FUSION_ID = 216;
  const RECIPE_SWORD_ID = 217;
  const RECIPE_ARMOR_ID = 218;
  const RECIPE_ORB_ID = 219;
  const CHECK_SWORD_ID = 220;
  const CHECK_ARMOR_ID = 221;
  const CHECK_ORB_ID = 222;
  const REMOVE_ORB_ID = 223;
  const RECIPE_RARE_ID = 224;
  const RECIPE_TYPE_ID = 225;
  const CHECK_RECIPE_ID = 226;
  const STR_FORM_ID = 227;
  const REFORGE_ID = 228;
  const AUTO_REFORGE_ID = 229;
	const WEAPON = 'weapon';
	const ARMOR = 'armor';
	const RANK = ['§f粗雑な', '§a普通§fの', '§b優れた§f', '§e§l最上級§fの', '§l§e天§c下§d一§a品§fの', '§l§1唯§2一§3無§4二§fの'];
  const OVER = [204, 612, 1428, 3468, 4095];
  const MONEY = [110, 220, 330, 440, 550, 660, 770, 935, 1100, 1265, 1430, 1595];
  const RATE = [];
	
	public function __construct($plugin){
		$this->plugin = $plugin;
		$this->spawn();
		$this->select = [];
	}
	
	
    public function spawn(){
	    
	    $data = $this->plugin->forgeData;
      
      foreach($data as $field => $pos){
        $this->eid[$field] = Entity::nextRuntimeId();
        $this->uuid[$field] = UUID::fromString(md5(uniqid(mt_rand(), true)));
        $this->pk[$field] = $this->summon($pos, $field);
        $this->chest[$field] = new Vector3($pos['x'], $pos['y'] - 1, $pos['z']);
      }
        //$this->summon($pos["x"], $pos["y"], $pos["z"], self::NAME, $this->eid, $player);
    }
    
    public function summon($pos, $field){
	  $pk = new AddPlayerPacket();
	  $pk->uuid = $this->uuid[$field];
	  $pk->username = self::NAME;
	  $pk->actorRuntimeId = $this->eid[$field];
	  $pk->position = new Vector3($pos['x'], $pos['y'], $pos['z']);
	  $pk->motion = new Vector3(0, 0, 0);
	  $pk->yaw = $pos['yaw'];
	  $pk->pitch = 0;
	  $pk->item = ItemStackWrapper::legacy(ItemStack::null());
    $pk->gameMode = 0;
    $pk->syncedProperties = new PropertySyncData([1], [1.0]);
    $pk2 = UpdateAbilitiesPacket::create(0, 0, $this->eid[$field], []);
    $pk->abilitiesPacket = $pk2;
  
    /*$adp = new AdventureSettingsPacket();
    $adp->targetActorUniqueId = 0;
    $pk->adventureSettingsPacket = $adp;*/
    $meta = new EntityMetadataCollection();
    $meta->setByte(EM::ALWAYS_SHOW_NAMETAG, 1);
    $meta->setString(EM::NAMETAG, self::NAME);
    $meta->setLong(EM::LEAD_HOLDER_EID, -1);
    $meta->setFloat(EM::SCALE, 1);
	  $pk->metadata = $meta -> getAll();
	  /*[
		EM::DATA_FLAGS => 
			[
				EM::DATA_TYPE_LONG, 1 << EM::DATA_FLAG_ALWAYS_SHOW_NAMETAG ^ 1 << EM::DATA_FLAG_CAN_SHOW_NAMETAG
			],
		EM::DATA_NAMETAG => 
			[
				EM::DATA_TYPE_STRING, self::NAME
			],
		EM::DATA_LEAD_HOLDER_EID => 
			[
				EM::DATA_TYPE_LONG, -1
			],
		EM::DATA_SCALE => 
			[
				EM::DATA_TYPE_FLOAT,1
			]
	  ];*/
	 return $pk;
  }
  
  public function removeAll($field){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    if(!isset($this->eid[$field]))return false;
    foreach($players as $player){
      $this->remove($player, $field);
    }
  }

  public function remove($player, $field){
    $pk = new RemoveActorPacket();
    $pk->actorUniqueId = $this->eid[$field];
    $player->getNetworkSession()->sendDataPacket($pk);
  }
  
  
  public function sendPacket($player, $field){
    $player->getNetworkSession()->sendDataPacket($this->pk[$field]);
    $skin = $this->plugin->mob->Skin;
    if(!isset($skin[self::NAME])) return false;
    $pk2 = new PlayerSkinPacket();
    $pk2->uuid = $this->uuid[$field];
    $pk2->skin = SkinAdapterSingleton::get()->toSkinData($skin[self::NAME]);
    $player->getNetworkSession()->sendDataPacket($pk2);
  }
  
  /*public function onReceive(DataPacketReceiveEvent $event){
    $pk = $event->getPacket();
    $player = $event->getOrigin()->getPlayer();
    if(is_null($player)) return false;
    $field = $this->plugin->fieldmanager->getField($player);
    if($pk instanceof InventoryTransactionPacket && $pk->requestId === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
      $eid = $pk->trData->entityRuntimeId;
      if(!isset($this->eid[$field])) return false;
      if($eid === $this->eid[$field]){
       if($player->isSneaking()){
         $player->sendMessage('§bSorry!!>>スニークした状態では鍛冶屋に話しかけることは出来ません。');
         return false;
        }
	     $this->sendMakingForm($player);
      }    
    }
  }*/

  public function onReceive(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if(is_null($player)) return false;
        $field = $this->plugin->fieldmanager->getField($player);
        if($pk instanceof InventoryTransactionPacket){
          if($pk->trData instanceof UseItemOnEntityTransactionData){
            $eid = $pk->trData->getActorRuntimeId();
            if(!isset($this->eid[$field])) return false;
            if($eid === $this->eid[$field]){
              if($player->isSneaking()){
                $player->sendMessage('§bSorry!!>>スニークした状態では鍛冶屋に話しかけることは出来ません。');
                return false;
               }
              $this->sendMakingForm($player);
            }  
          }
        }
    }
  
    public function sendMakingForm($player){
      $buttons = [];
    $comments = [/*'剣作成', '防具作成', 'オーブ作成', */'オーブ取り外し', 'スキルを付与', '武具強化', 'オーブ合成', 'リフォージ', /*'オートリフォージ'*/];
      foreach($comments as $name){
        $buttons[] = ['text' => $name];
      }
      $data = [
			    'type' => 'form',
			    'title' => '§l鍛冶屋',
			    'content' => '何をしますか？（レシピから作ります)',
			    'buttons' => $buttons
	    ];
      $this->sendForm($player, $data, self::MAKING_FORM_ID);
    }
    
    public function sendSelectForm($player, $type){
      $content = [];
      $amount = $player->getInventory()->getItemInHand()->getCount();
      $content[] = ["type" => "slider", "text" => "何個使いますか?(多いと質が良くなる)", "min" => 1, "max" => $amount];
      $data = [
		    'type'=>'custom_form',
		    'title'   => "§l武具、オーブ作成",
		    'content' => $content
	    ];
      $this->sendForm($player, $data, $type);
    }
    
    public function sendOrbSelectForm($player){
      $inv = $player->getInventory();
      $buttons = [];
      foreach ($inv->getContents() as $item){
	$tag = $item->getNamedTag(Skill::TAG_SKILL)->getTag(Skill::TAG_SKILL);
        if(isset($tag)){
	  $buttons[] = ['text' => $item->getName()];
	}
      }
      if(!isset($buttons[0])){
	$player->sendMessage('§eINFO>>§f>>オーブがありません。');
	return false;
      }
      $data = [
			    'type' => 'form',
			    'title' => '§lスキル付与',
			    'content' => 'どれをつけますか？',
			    'buttons' => $buttons
	      ];
      $this->sendForm($player, $data, self::SELECT_ORB_ID);
    }
    
    public function sendSelectPosForm($player){
      $buttons = [];
      $comments = ['Skill 1', 'SKill 2', 'Skill 3'];
      $this->slots[$player->getName()] = [];
      $item = $player->getInventory()->getItemInHand();
      $tag = $item->getNamedTag()->getTag(Armor::TAG_ARMOR);
      if(!isset($tag)){
        $tag = $item->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
        if(!isset($tag)){
          $player->sendMessage("§cINFO§f>>スキルがつけられるのは防具またはアクセサリのみです");
          return false;
        }
      }
      $slot = $tag->getTag(Armor::TAG_SLOT)->getValue();
      for($i = 0; $i < $slot; $i++){
        $skill = $tag->getTag(Armor::TAG_SKILLS[$i]);
        if(!isset($skill)){
          $this->slots[$player->getName()][] = $i;
          $buttons[] = ['text' => $comments[$i]];
        }
      }
      $data = [
			    'type' => 'form',
			    'title' => '§lスキル付与',
			    'content' => 'どこにつけますか？',
			    'buttons' => $buttons
      ];
      $this->sendForm($player, $data, self::SELECT_POS_ID);
    }

    public function removeOrbForm($player){
      $buttons = [];
      $this->slots[$player->getName()] = [];
      $item = $player->getInventory()->getItemInHand();
      $tag = $item->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
      if(!isset($tag)){
        $tag = $item->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
        if(!isset($tag)){
          $player->sendMessage("§eSYSTEM>>§f防具またはアクセサリを手にもって行ってください。");
          return false;
        }
      }
      $slot = $tag->getTag(Armor::TAG_SLOT)->getValue();
      for($i = 0; $i < $slot; $i++){
        $skill = $tag->getTag(Armor::TAG_SKILLS[$i]);
        if(isset($skill)){
          if($tag->getTag(Armor::TAG_SKILL_ORB[$i])->getValue()){
            $this->slots[$player->getName()][] = $i;
            $name = SkillManager::getSkill($skill->getValue())->getName();
            $lv = $tag->getTag(Armor::TAG_SKILL_LV[$i])->getValue();
            $buttons[] = ['text' => $name.':'.$lv."lv\n§7消費フロル§f: 1000Fl"];
          }
        }
      }
      $data = [
			    'type' => 'form',
			    'title' => '§lオーブ取り外し',
			    'content' => 'どれをとりますか？',
			    'buttons' => $buttons
      ];
      $this->sendForm($player, $data, self::REMOVE_ORB_ID);
    }

    public function sendStrSelectForm($player){
      $inv = $player->getInventory();
      $item = $inv->getItemInHand();
      $tag = $item->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
      if(!isset($tag)){
        $tag = $item->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
        if(is_null($tag)){
          $player->sendMessage('§eINFO§f>>強化可能なのは武器か防具のみです。(手に強化したい武器か防具を持って行ってください。)');
          return false;
        }
      }
      $buttons = [];
	    $buttons[] = ['text' => 'yes'];
      $data = [
			    'type' => 'form',
			    'title' => '§l武具強化',
			    'content' => "強化するなら、yesを押してください。(鉱石を使います。)\n強化には、獲得経験値の10倍の費用が掛かります。\n",
			    'buttons' => $buttons
      ];
      $data['content'] .= "{$item->getName()}\n";
      foreach($item->getLore() as $lore){
        $data['content'] .= $lore."\n";
      }
      $lv = $tag->getTag(Weapon::TAG_LEVEL)->getValue();
      $rare = $tag->getTag(Weapon::TAG_RARE)->getValue();
      /*$money = self::MONEY[$rare] * (1 + ($lv+1)/4);
      $data['content'] .= "§l消費fl: {$money}fl\n";*/
      //$data['content'] .= "§l§c成功確率: §f".(100-5*$lv)."%\n";
      /*$data['content'] .= "§l§d※強化に失敗すると武具は§c破壊§fされます！\n";*/
      $this->sendForm($player, $data, self::STR_ID);
    }

    public function sendOrbFusionForm($player){
      $buttons = [];
	    $buttons[] = ['text' => 'YES'];
      $data = [
			    'type' => 'form',
			    'title' => '§lオーブ強化',
			    'content' => "本当に強化しますか？(手に持っているオーブを強化します。)\n消費フロル: 1000fl",
			    'buttons' => $buttons
	   ];
      $this->sendForm($player, $data, self::FUSION_ID);
      //$player->sendMessage('§eINFO§f>>現在使用できません');
    }

    public function sendRecipeForm($player, $id){
      $buttons = [];
      $buttons = [['text' => '§e☆'], ['text' => '§e☆☆'], ['text' => '§e☆☆☆'], ['text' => '§e☆☆☆☆'], ['text' => '§c☆']];
      $name = $player->getName();
      switch($id){
        case self::RECIPE_SWORD_ID:
          $this->choose[$name] = 'sword';
          break;
        case self::RECIPE_ARMOR_ID:
          $this->choose[$name] = 'armor';
          break;
        case self::RECIPE_ORB_ID:
          $this->choose[$name] = 'orb';
          break;
      }
      $data = [
			    'type' => 'form',
			    'title' => '§l武具、オーブ作成',
			    'content' => 'レア度を選択してください。',
			    'buttons' => $buttons
	   ];
      $this->sendForm($player, $data, self::RECIPE_RARE_ID);
    }

    public function chooseRecipeTypeForm($d, $player, $id){
      $buttons = [];
      $name = $player->getName();
      $this->chRare[$name] = $d;
      for($i = 0; $i < 7; $i++){
        $buttons[] = ["text" => Type::NAME[$i]];
      }
      $data = [
			    'type' => 'form',
			    'title' => '§l武具、オーブ作成',
			    'content' => 'タイプを選択してください。',
			    'buttons' => $buttons
	   ];
      $this->sendForm($player, $data, self::RECIPE_TYPE_ID);
    }

    

    public function checkRecipeForm($data, $player, $id){
      $buttons = [];
      switch($id){
        case self::CHECK_SWORD_ID:
          $d = 'sword';
          $bai = 8;
          break;
        case self::CHECK_ARMOR_ID:
          $d = 'armor';
          $bai = 6;
          break;
        case self::CHECK_ORB_ID:
          $d = 'orb';
          $bai = 2;
          break;
      }
      $items = [];
      foreach ($player->getInventory()->getContents() as $item){
        $tag = $item->getNamedTag(Recipe::TAG_RECIPE)->getTag(Recipe::TAG_RECIPE);
        if(isset($tag)){
          if($tag->getTag(Recipe::TAG_TYPE)->getValue() === $d)
            $items[] = $item;
        }
      }
      $item = $items[$data];
      $this->item[$player->getName()] = $item;
      $idd = $item->getNamedTag(Recipe::TAG_RECIPE)->getTag(Recipe::TAG_RECIPE)->getTag(Recipe::TAG_ID)->getValue();
      $buttons = [['text' => 'yes']];
      $data = [
			    'type' => 'form',
			    'title' => '§l武具、オーブ作成',
			    'buttons' => $buttons
      ];
      $data['content'] = "{$item->getName()}\n";
      foreach($item->getLore() as $lore){
        $data['content'] .= $lore."\n";
      }
      $money = self::MONEY[$this->plugin->recipeSwordData[$idd]['rare']-1] * $bai;
      $data['content'] .= "消費フロル: {$money}FL\n";
      $data['content'] .= 'これを使用しますか？';
      $this->sendForm($player, $data, $id);
    }

    public function receiveStr($player){
      $item = $player->getInventory()->getItemInHand();
      $tag = $item->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
      if(!isset($tag)){
        $tag = $item->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
        if(is_null($tag)){
          $player->sendMessage('§eINFO§f>>強化可能なのは武器か防具のみです。(手に強化したい武器か防具を持って行ってください。');
          return false;
        }

        foreach($this->plugin->recipeArmorData as $r){
          if($r['make'] == $tag->getTag(Armor::TAG_ARMOR_ID)->getValue()){
            $recipe = $r;
          }
        }

        $content = "消費するアイテム\n";
        foreach($recipe['items'] as $id => $amount){
          $content .= Mobmanager::getMob('shadow')->getItem($id)->getName();
          $content .= "x {$amount}\n";
        }
        foreach($recipe['armor'] as $id => $amount){
          $content .= Armor::getItem($id, 0, 0, 0, 0)->getName();
          $content .= "x {$amount}\n";
        }

        $data = ["type" => 'form',
                 "tytle" => "武具強化",
                 "content" => $content,
                 "buttons" => $buttons
        ];
        $buttons = ["はい"];
        $id = self::STR_FORM_ID;
        $this->sendForm($player, $data, $id);
        return true;
      }
      foreach($this->plugin->recipeSwordData as $r){
        if($r['make'] == $tag->getTag(Weapon::TAG_WEAPON_ID)->getValue()){
          $recipe = $r;
        }
      }

      $content = "消費するアイテム\n";
      foreach($recipe['items'] as $id => $amount){
        $content .= MobManager::getMob('shadow')->getItem($id)->getName();
        $content .= "x {$amount}\n";
      }
      foreach($recipe['swords'] as $id => $amount){
        $content .= WeaponManager::getWeapon()->getItem($id, [1, 1], 0)->getName();
        $content .= "x {$amount}\n";
      }

      $data = ["type" => 'form',
               "tytle" => "武具強化",
               "content" => $content,
               "buttons" => $buttons
      ];
      $buttons = ["はい"];
      $id = self::STR_FORM_ID;
      $this->sendForm($player, $data, $id);
      return true;
    }

    public function checkAutoReforgeForm($player, $id){
      $inv = $player->getInventory();
      $item = $inv->getItemInHand();
      $tag = $item->getNamedTag()->getTag(Weapon::TAG_WEAPON);
      if(!isset($tag)){
        $tag = $item->getNamedTag()->getTag(Armor::TAG_ARMOR);
        if(is_null($tag)){
          $tag = $item->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
          if(is_null($tag)){
            $player->sendMessage('§eINFO§f>>リフォージ可能なのは武器か防具のみです。(手に強化したい武器か防具を持って行ってください。)');
            return false;
          }
        }
      }
      $buttons = [];
	    $buttons[] = ['text' => 'yes'];
      $data = [
			    'type' => 'form',
			    'title' => '§l武具強化',
			    'content' => "リフォージするなら、yesを押してください。(唯一無二が出るまで繰り返します。)\n",
			    'buttons' => $buttons
      ];
      $data['content'] .= "{$item->getName()}\n";
      foreach($item->getLore() as $lore){
        $data['content'] .= $lore."\n";
      }
      $rare = $tag->getTag(Weapon::TAG_RARE)->getValue();
      $money = self::MONEY[$rare] * (1/2);
      $data['content'] .= "§l消費fl: {$money}fl/回\n";
      $this->sendForm($player, $data, $id);
    }

    public function checkReforgeForm($player, $id){
      $inv = $player->getInventory();
      $item = $inv->getItemInHand();
      $tag = $item->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
      if(!isset($tag)){
        $tag = $item->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
        if(is_null($tag)){
          $tag = $item->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
          if(is_null($tag)){
            $player->sendMessage('§eINFO§f>>リフォージ可能なのは武器か防具のみです。(手に強化したい武器か防具を持って行ってください。)');
            return false;
          }
        }
      }
      $buttons = [];
	    $buttons[] = ['text' => 'yes'];
      $data = [
			    'type' => 'form',
			    'title' => '§l武具強化',
			    'content' => "リフォージするなら、yesを押してください。(現在ついている接頭辞は削除されます。)\n",
			    'buttons' => $buttons
      ];
      $data['content'] .= "{$item->getName()}\n";
      foreach($item->getLore() as $lore){
        $data['content'] .= $lore."\n";
      }
      $rare = $tag->getTag(Weapon::TAG_RARE)->getValue();
      $count = $tag->getTag(Weapon::TAG_REROLL_COUNT)->getValue();
      $money = (int)(self::MONEY[$rare] * (1/2)) ** ($count / 4 + 1);
      $data['content'] .= "§l消費fl: {$money}fl\n";
      $this->sendForm($player, $data, $id);
    }
    
    public function sendForm($player, $data, $id){
      $pk = new ModalFormRequestPacket;
      $pk->formId = $id;
      $pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
      $player->getNetworkSession()->sendDataPacket($pk);
    }
    
    public function receiveMakingForm($data, $player){
	if(!isset($data)) return false;
	switch($data){
	  /*case 0:
	    $this->sendRecipeForm($player, self::RECIPE_SWORD_ID);
			break;
	  case 1:
	    $this->sendRecipeForm($player, self::RECIPE_ARMOR_ID);
	    break;
	  case 2:
	    $this->sendRecipeForm($player, self::RECIPE_ORB_ID);
	    break;*/
	  case 1:
	    $this->sendOrbSelectForm($player, self::SELECT_ORB_ID);
      break;
    case 0:
      $this->removeOrbForm($player, self::REMOVE_ORB_ID);
      break;
    case 2:
      $this->sendStrSelectForm($player, self::STR_ID);
      break;
    case 3:
      $this->sendOrbFusionForm($player, self::FUSION_ID);
      break;
    case 4:
      $this->checkReforgeForm($player, self::REFORGE_ID);
      break;
    case 5:
      $this->checkAutoReforgeForm($player, self::AUTO_REFORGE_ID);
      break;
	}
    }
    
    public function onPacketReceive(DataPacketReceiveEvent $event){
	  $pk = $event->getPacket();
	  $player = $event->getOrigin()->getPlayer();
    if(is_null($player)) return false;
	  if($pk instanceof ModalFormResponsePacket){
	      $data = json_decode($pk->formData, true);
	      if(!isset($data)) return false;
	      switch($pk->formId){
			  case self::MAKING_FORM_ID;
			    $this->receiveMakingForm($data, $player);
			    break;
			  case self::RECIPE_RARE_ID;
          $this->chooseRecipeTypeForm($data, $player, self::RECIPE_TYPE_ID);
			    break;
			  case self::RECIPE_TYPE_ID:
			    $this->choiceRecipeForm($data, $player, self::CHECK_RECIPE_ID);
			    break;
			  case self::RECIPE_ORB_ID:
			    $this->checkRecipeForm($data, $player, self::CHECK_ORB_ID);
          break;
        case self::REMOVE_ORB_ID:
          $this->removeOrb($this->slots[$player->getName()][$data], $player);
          break;
			  case self::SELECT_ORB_ID:
			    $this->sendSelectPosForm($player);
			    $this->select[$player->getName()] = $data;
			    break;
			  case self::SELECT_POS_ID:
			    $this->setSkill($this->slots[$player->getName()][$data], $player);
          break;
        case self::STR_ID:
          //$this->receiveStr($player);
          //$this->Str($player);
          $inv = new ForgeInventory();
          $player->setCurrentWindow($inv);
          break;
        case self::STR_FORM_ID:
          $this->Str($player);
          break;
        case self::FUSION_ID:
          $this->OrbFusion($data, $player);
          break;
        case self::CHECK_SWORD_ID;
          $this->Making($data, $player, 'sword');
          break;
        case self::CHECK_ARMOR_ID;
          $this->Making($data, $player, 'armor');
          break;
        case self::CHECK_ORB_ID;
          $this->Making($data, $player, 'orb');
			    break;
        case self::REFORGE_ID;
          $this->Reforge($data, $player);
			    break;
        case self::AUTO_REFORGE_ID;
          $this->AutoReforge($data, $player);
			    break;
		  }
	   }
    }
    
    public function Making($data, $player, $type){
      $item = $this->item[$player->getName()];
      $item->setCount(1);
      $items = [];
      $tagu = $item->getNamedTag(Recipe::TAG_RECIPE)->getTag(Recipe::TAG_RECIPE);
      $recipeid = $tagu->getTag(Recipe::TAG_ID)->getValue();
      if($type === 'sword'){
        $data = $this->plugin->recipeSwordData[$recipeid];
        $money = self::MONEY[$data['rare']-1] * 8;
        if(!$this->plugin->playermanager->hasMoney($player, $money)){
          $player->sendMessage('§eINFO§f>>お金が足りません！');
          return false;
        }
        foreach($data['items'] as $id => $amount){
          $i = MobManager::getMob('shadow')->getItem($id)->setCount($amount);
          $items[] = $i;
          if(!$player->getInventory()->contains($i)){
            $player->sendMessage('§eINFO§f>>素材が足りません！');
            return false;
          }
        }
        $swords = [];
        $ores = [];
        foreach($player->getInventory()->getContents() as $i){
          $tag = $i->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
          if(isset($tag)){
            $swords[] = $i;
          }
          $tag = $i->getNamedTag(PveItem::TAG_ITEM)->getTag(PveItem::TAG_ITEM);
          if(isset($tag)){
            if(ItemManager::getItem($tag->getTag(PveItem::TAG_ID)->getValue())->isOre())
              $ores[] = $i;
          }
        }
        foreach($data['swords'] as $id => $amount){
          $flag = false;
          foreach($swords as $i){
            if($id == $i->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON_ID)->getValue()){
              if($i->getCount() >= $amount){
                $flag = true;
                $items[] = $i->setCount($amount);
                $weapon = $i;
                break;
              }
            }
          }
          if(!$flag){
            $player->sendMessage('§eINFO>>素が足りません！');
            return false;
          }
        }
        foreach($data['ores'] as $id => $amount){
          $flag = false;
          foreach($ores as $i){
            if($id === $i->getNamedTag(PveItem::TAG_ITEM)->getTag(PveItem::TAG_ITEM)->getTag(PveItem::TAG_ID)->getValue()){
              if($i->getCount() >= $amount){
                $flag = true;
                $items[] = $i->setCount($amount);
                break;
              }
            }
          }
          if(!$flag){
            $player->sendMessage('§eINFO§b>>素材が足りません！');
            return false;
          }
        }
        foreach($items as $i){
          $player->getInventory()->removeItem($i);
        }
        $bairitus = $tagu->getTag(Recipe::BAIRITUS)->getValue();
        $exp = 0;
        if(isset($weapon)){
          $bai = $weapon->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_BAIRITU)->getValue();
          $exp = $weapon->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_EXP)->getValue();
          $sum = 0; $c = 0;
          foreach($bai as $b){
            $sum += $b;
            $c++;
          }
          $hosei = $sum / $c / 100;
          $c = 0;
          foreach($bairitus as $b){
            $bairitus[$c] = (int)round($b * $hosei);
            $c++;
          }
        }
        $this->plugin->playermanager->takeMoney($player, $money);
        $make = WeaponManager::getWeapon()->getItem($data['make'], $bairitus, $exp);
      }elseif($type === 'armor'){
        $data = $this->plugin->recipeArmorData[$recipeid];
        $money = self::MONEY[$data['rare']-1] * 6;
        if(!$this->plugin->playermanager->hasMoney($player, $money)){
          $player->sendMessage('§eINFO§f>>お金が足りません！');
          return false;
        }
        $pos = $tagu->getTag(Recipe::TAG_POS)->getValue();
        foreach($data['items'] as $id => $amount){
          $i = MobManager::getMob('shadow')->getItem($id)->setCount($amount);
          $items[] = $i;
          if(!$player->getInventory()->contains($i)){
            $player->sendMessage('§eINFO§f>>素材が足りません！');
            return false;
          }
        }
        $armors = [];
        $ores = [];
        foreach($player->getInventory()->getContents() as $i){
          $tag = $i->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
          if(isset($tag)){
            $armors[] = $i;
          }
          $tag = $i->getNamedTag(PveItem::TAG_ITEM)->getTag(PveItem::TAG_ITEM);
          if(isset($tag)){
            if(ItemManager::getItem($tag->getTag(PveItem::TAG_ID)->getValue())->isOre())
              $ores[] = $i;
          }
        }
        foreach($data['armors'] as $id => $amount){
          $flag = false;
          foreach($armors as $i){
            $tag = $i->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
            if($id == $tag->getTag(ARMOR::TAG_ARMOR_ID)->getValue()){
              if($tag->getTag(Armor::TAG_POS)->getValue() == $pos){
                if($i->getCount() >= $amount){
                  $flag = true;
                  $items[] = $i->setCount($amount);
                  break;
                }
              }
            }
          }
          if(!$flag){
            $player->sendMessage('§eINFO§c>>素材が足りません！');
            return false;
          }
        }
        foreach($data['ores'] as $id => $amount){
          $flag = false;
          foreach($ores as $i){
            if($id === $i->getTag(PveItem::TAG_ID)->getValue()){
              if($i->getCount() >= $amount){
                $flag = true;
                $items[] = $i->setCount($amount);
                break;
              }
            }
          }
          if(!$flag){
            $player->sendMessage('§eINFO§d>>素材が足りません！');
            return false;
          }
        }
        foreach($items as $i){
          $player->getInventory()->removeItem($i);
        }

        $bairitu = $tagu->getTag(Recipe::TAG_BAIRITU)->getValue();
        $slot = $tagu->getTag(Recipe::TAG_SLOT)->getValue();
        $skill = $tagu->getTag(Recipe::TAG_SKILL)->getValue();
        $this->plugin->playermanager->takeMoney($player, $money);
        $make = ArmorManager::getArmor()->getItem($data['make'], $bairitu/10, $pos, $slot, $skill);
      }elseif($type === 'orb'){
        $data = $this->plugin->recipeOrbData[$recipeid];
        $money = self::MONEY[$data['rare']-1] * 2;
        if(!$this->plugin->playermanager->hasMoney($player, $money)){
          $player->sendMessage('§eINFO§f>>お金が足りません！');
          return false;
        }
        foreach($data['items'] as $id => $amount){
          $i = MobManager::getMob('shadow')->getItem($id)->setCount($amount);
          $items[] = $i;
          if(!$player->getInventory()->contains($i)){
            $player->sendMessage('§eINFO§f>>素材が足りません！');
            return false;
          }
        }
        foreach($data['ores'] as $id => $amount){
          $i = ItemManager::getItem($id)->getItem()->setCount($amount);
          $items[] = $i;
          if(!$player->getInventory()->contains($i)){
            $player->sendMessage('§eINFO§b>>素材が足りません！');
            return false;
          }
        }
        foreach($items as $i){
          $player->getInventory()->removeItem($i);
        }

        $id = $tagu->getTag(Recipe::TAG_ID)->getValue();
        $lv = $tagu->getTag(Recipe::TAG_LV)->getValue();
        $this->plugin->playermanager->takeMoney($player, $money);
        $make = SkillManager::getSkill($data['make'])->getOrb($lv);
      } 
      if(!isset($make)) return false;

      
      $player->getInventory()->removeItem($item);
      $kaisuu = $tagu->getTag(Recipe::TAG_KAISUU)->getValue();
      if($kaisuu > 1){
        $tagu->setString(Recipe::TAG_KAISUU, $kaisuu-1);
        $item->getNamedTag()->setTag(Recipe::TAG_RECIPE, $tagu);
        $lore = $item->getLore();
        $lore[2] = '§c残り使用可能回数§f: '.($kaisuu-1).'回';
        $item->setLore($lore);
        $player->getInventory()->addItem($item);
      }
      
      $player->getInventory()->addItem($make);
      $player->sendMessage('§eINFO>>§f作成しました!');
    }
    
    public function setRank($amount, $player, $class, $id){
      $rank = $this->getRank($amount);
      if($rank === 5)
	$this->plugin->getServer()->broadcastMessage(
	  '§l§aEXCELLENT!>>§f'.$player->getName().'さんが§b唯一無二§fの作成に成功しました!');
      $maked = $class->getItem(($rank+1) / 3, $id);
      $name = $maked->getName();
      $maked->setCustomName($name.' '.self::RANK[$rank]);
      
      if($rank >= 3){
	$maked = $class->setOnlySkill($maked, $id);
      }
      return $maked;
    }
    
    public function getRank($amount){
      $rand = mt_rand(0, $amount*$amount);
      for($i = 0; $i < 5; $i++){
	      if($rand < self::OVER[$i]) break;
      }
      
      return $i;
    }
    
    public function isOriginalItem($item, $player){
      $tag = $item->getNamedTag(Mobs::TAG_ITEM)->getTag(Mobs::TAG_ITEM);
      if(!isset($tag)){
	$player->sendMessage('§eINFO>>§fそのアイテムでは作成できません');
	return false;
      }
      return true;
    }
    
    public function setSkill($pos, $player){
      $inv = $player->getInventory();
      $armor = $inv->getItemInHand();
      $armtag = $armor->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
      $flag = true;
      if(!isset($armtag)){
        $armtag = $armor->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
        $flag = false;
        if(!isset($armtag)){
	        $player->sendMessage('§eINFO>>§f防具またはアクセサリにのみ付与できます(手に持って行ってください)');
	        return false;
        }
      }
      $orbs = [];
      foreach($inv->getContents() as $item){
        $tag = $item->getNamedTag(Skill::TAG_SKILL)->getTag(Skill::TAG_SKILL);
        if(isset($tag)){
	        $item->setCount(1);
	        $orbs[] = $item;
	      }
      }
      $orb = $orbs[$this->select[$player->getName()]];
      $tags = $orb->getNamedTag(Skill::TAG_SKILL)->getTag(Skill::TAG_SKILL);
      $id = $tags->getTag(Skill::TAG_SKILL_ID)->getValue();
      $lv = $tags->getTag(Skill::TAG_SKILL_LV)->getValue();
      if($flag) $armorr = ArmorManager::getArmor($armtag->getTag(Armor::TAG_ARMOR_ID)->getValue())->setSkill($armor, $id, $lv, $pos);
      else $armorr = $this->plugin->acc->setSkill($armor, $id, $lv, $pos);
      $inv->removeItem($orb);
      $inv->setItemInHand(ItemFactory::getInstance()->get(0, 0, 0));
      $inv->addItem($armorr);
      $player->sendMessage('§eINFO>>§f付与に成功しました!');
    }

    public function openWindow($player){
      $field = $this->plugin->fieldmanager->getField($player);
      $chest = $this->plugin->fieldmanager->getLevel()->getTile($this->chest[$field]);
      if(!isset($chest)) $player->sendMessage('UUNKOO');
      $inv = new ChestInventory($chest);
      $player->addWindow($inv);
    }

    /*public function onClose(InventoryCloseEvent $event){
      $player = $event->getPlayer();
      $item = $player->getInventory()->getItemInHand();
      $inv = $event->getInventory();
      $name = $inv->getName();
      if($name === 'Chest'){
        $chest1 = $inv->getHolder();
        $field = $this->plugin->fieldmanager->getField($player);
        if(!isset($this->chest[$field])) return false;
        $chest2 = $this->plugin->fieldmanager->getLevel()->getTile($this->chest[$field]);
        if($chest1 === $chest2){
          $tag = $item->getNamedTag(Armor::TAG_ARMOR);
          $flag = 'armor';
          if(!isset($tag)){
            $tag = $item->getNamedTag(Weapon::TAG_WEAPON);
            $flag = 'sword';
            if(!isset($tag)){
              $player->sendMessage('§eINFO>>§f武器か防具のみ強化できます(手に持って行ってください)');
              $items = $inv->getContents();
              foreach($items as $item){
                $player->getInventory()->addItem($item);
              }
              return false;
            }
          }
          $rare = $tag->getTag(Weapon::TAG_RARE)->getValue();
          $lv = $tag->getTag(Weapon::TAG_LEVEL)->getValue();
          if($lv > 16){
            $player->sendMessage('§eINFO§f>>それ以上強化できません');
            $items = $inv->getContents();
            foreach($items as $item){
                $player->getInventory()->addItem($item);
            }
            return false;
          }
          $items = $inv->getContents();
          $amount = 0;
          foreach($items as $item){
            $tag = $item->getNamedTag(PveItem::TAG_ITEM);
            if(isset($tag)){
              $id = $tag->getTag(PveItem::TAG_ID)->getValue();  
              if(ItemManager::getItem($id)->isOre()){
                $inv->removeItem($item);
                $amount += (ItemManager::getItem($id)->getPurity()) * ($item->getCount());
                continue;
              }
            }
            $inv->removeItem($item);
            $player->getInventory()->addItem($item);
          }
          if($amount == 0) r8turn false;
          $money = (3 / (550 * $rare)) * self::MONEY[$rare-1] * $amount;
          $player->sendMessage('§7§l消費フロル§f§r: '.$money.'Fl');
          $player->sendMessage('§e§l獲得経験値§f§r: '.$amount.'exp');
          if(!$this->plugin->playermanager->hasMoney($player, $money)){
            $player->sendMessage('§eINFO§f>>お金が足りません。');
            foreach($inv->getContents() as $item){
              $player->getInventory()->addItem($item);
            }
            return false;
          }
          $this->plugin->playermanager->takeMoney($player, $money);
          //$this->Str($player, $amount);
        }
      }
    }*/

    public function onClose(InventoryCloseEvent $event){
      $player = $event->getPlayer();
      $inv = $event->getInventory();
      $item = $player->getInventory()->getItemInHand();
      if($inv instanceof ForgeInventory){
          $player->setImmobile(false);
          $items = $inv->getContents();
          $tag = $item->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
          $flag = 'armor';
          if(!isset($tag)){
              $tag = $item->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
              $flag = 'sword';
          }
          if(!isset($tag)){
            $player->sendMessage('§eINFO§f>>強化したい武器または防具を手にもって行ってください。');
            $items = $inv->getContents();
            foreach($items as $item){
                $player->getInventory()->addItem($item);
            }
            return false;
          }
          $rare = $tag->getTag(Weapon::TAG_RARE)->getValue();
          $lv = $tag->getTag(Weapon::TAG_LEVEL)->getValue();
          if($lv > 16){
              $player->sendMessage('§eINFO§f>>それ以上強化できません');
              $items = $inv->getContents();
              foreach($items as $item){
                  $player->getInventory()->addItem($item);
              }
              return false;
          }
          $amount = 0;
          $ai = [];
          foreach($items as $item){
              $tag = $item->getNamedTag(PveItem::TAG_ITEM)->getTag(PveItem::TAG_ITEM);
              if(isset($tag)){
                  $id = $tag->getTag(PveItem::TAG_ID)->getValue();  
                  if(ItemManager::getItem($id)->isOre()){
                      $inv->removeItem($item);
                      $ai[] = $item;
                      $amount += (ItemManager::getItem($id)->getPurity()) * ($item->getCount());
                      continue;
                  }
              }
              //$inv->removeItem($item);
              $player->getInventory()->addItem($item);
          }
          //$money = (3 / (550 * $rare)) * self::MONEY[$rare-1] * $amount;
          $money = $amount * 10;
          $player->sendMessage('§7§l消費フロル§f§r: '.$money.'Fl');
          $player->sendMessage('§e§l獲得経験値§f§r: '.$amount.'exp');
          if(!$this->plugin->playermanager->hasMoney($player, $money)){
              $player->sendMessage('§eINFO§f>>お金が足りません。');
              foreach($ai as $item){
                $player->getInventory()->addItem($item);
              }
              return false;
          }
          $this->plugin->playermanager->takeMoney($player, $money);
          $this->Str($player, $amount);
      }  
    }


    public function Str($player, $amount){
      $inv = $player->getInventory();
      $armor = $inv->getItemInHand();
      $tag = $armor->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
      $flag = false;
      if(!isset($tag)){
        $tag = $armor->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
        $flag = true;
        if(!isset($tag)){
	        $player->sendMessage('§eINFO>>§f武器か防具のみ強化できます(手に持って行ってください)');
          return false;
        }
        $id = $tag->getTag(Weapon::TAG_WEAPON_ID)->getValue();
        $lv = $tag->getTag(Weapon::TAG_LEVEL)->getValue();
        if($lv >= 8){
          $player->sendMessage('§cERROR§f>>強化できるのは8LVまでです');
          return false;
        }
        $rare = $tag->getTag(Weapon::TAG_RARE)->getValue();
        /*$flg = false;
        $this->plugin->playermanager->addWeaponAtk($player, $armor, -1);
        $inv->setItemInHand(ItemFactory::getInstance()->get(0,0,0));
        foreach($player->getInventory()->getContents() as $item){
          $tag2 = $item->getNamedTag()->getTag(Weapon::TAG_WEAPON);
          if(!isset($tag2)) continue;
          if($tag2->getTag(Weapon::TAG_WEAPON_ID)->getValue() == $id){
            $flg = true;
            $ri = $item;
          }
        }
        if(!$flg){
          $player->sendMessage('§eINFO§f>>アイテムが足りません。');
          $player->sendMessage('§eINFO§f>>強化には、同種かつ同レベルの武具が必要です。');
          $inv->setItemInHand($armor);
          $this->plugin->playermanager->addWeaponAtk($player, $armor);
          return false;
        }
        if(mt_rand(0, 100) < 5*$lv){
          $player->sendMessage("§eINFO§f>>§c強化に失敗しました！");
          $inv->setItemInHand($armor);
          $inv->removeItem($ri);
          $this->plugin->playermanager->addWeaponAtk($player, $armor);
          return false;
        }*/
  
        /*foreach($this->plugin->recipeSwordData as $r){
          if($r['make'] == $id){
            $recipe = $r;
          }
        }*/
        //$items = $this->checkPlayerHasRecipeItem($player, $recipe);
        /*if(!$items){
          $player->sendMessage('§eINFO§f>>アイテムが足りません。');
          return false;
        }
        //$items2 = $this->checkPlayerHasRecipeWeapon($player, $recipe);
        if(!$items2){
          $player->sendMessage('§eINFO§f>>アイテムが足りません。');
          return false;
        }
        $items = array_merge($items, $items2);*/
        /*$money = self::MONEY[$rare] * (1 + ($lv+1)/4);
        if(!$this->plugin->playermanager->hasMoney($player, $money)){
          $player->sendMessage('§eINFO§f>>お金が足りません。');
          $player->sendMessage('§eINFO§f>>強化には'.$money.'fl必要です。');
          $inv->setItemInHand($armor);
          $this->plugin->playermanager->addWeaponAtk($player, $armor);
          return false;
        }
        $this->plugin->playermanager->takeMoney($player, $money);*/
        //$inv->removeItem($ri);
        $exp = $tag->getTag(Weapon::TAG_EXP)->getValue();
        $exp += $amount;
        $next = ($lv+1) * 100 * $rare;
        while($exp >= $next){
          $lv++;
          $tag->setString(Weapon::TAG_LEVEL, $lv);
          $atk = $tag->getTag(Weapon::TAG_ATK)->getValue();
          $tag->setString(Weapon::TAG_ATK, floor($atk * 1.1));
          if($lv >= 8){
            $exp = 0;
            $next = 0;
            break;
          }else{
            $exp -= $next;
            $next = ($lv+1) * 100 * $rare;
          }
        }
        $tag->setString(Weapon::TAG_EXP, $exp);
        $armor->getNamedTag()->setTag(Weapon::TAG_WEAPON, $tag);
        $class = WeaponManager::getWeapon();
        $armor = $class->setLore($armor);
        $armor = $class->setName($armor);
      }else{
        $id = $tag->getTag(Armor::TAG_ARMOR_ID)->getValue();
        $lv = $tag->getTag(Armor::TAG_LEVEL)->getValue();
        if($lv >= 8){
          $player->sendMessage('§cERROR§f>>強化できるのは8LVまでです');
          return false;
        }
        $rare = $tag->getTag(Armor::TAG_RARE)->getValue();
        /*$flg = false;
        $inv->setItemInHand(ItemFactory::getInstance()->get(0,0,0));
        foreach($player->getInventory()->getContents() as $item){
          $tag2 = $item->getNamedTag()->getTag(Armor::TAG_ARMOR);
          if(!isset($tag2)) continue;
          if($tag2->getTag(Armor::TAG_ARMOR_ID)->getValue() == $id){
            $flg = true;
            $ri = $item;
          }
        }
        if(!$flg){
          $player->sendMessage('§eINFO§f>>アイテムが足りません。');
          $player->sendMessage('§eINFO§f>>強化には、同種かつ同レベルの武具が必要です。');
          $inv->setItemInHand($armor);
          return false;
        }
        if(mt_rand(0, 100) < 5*$lv){
          $player->sendMessage("§eINFO§f>>§c強化に失敗しました！");
          $inv->setItemInHand($armor);
          $inv->removeItem($ri);
          return false;
        }
        /*foreach($this->plugin->recipeArmorData as $r){
          if($r['make'] == $id){
            $recipe = $r;
          }
        }
        $items = $this->checkPlayerHasRecipeItem($player, $recipe);
        if(!$items){
          $player->sendMessage('§eINFO§f>>アイテムが足りません。');
          return false;
        }
        $items2 = $this->checkPlayerHasRecipeArmor($player, $recipe);
        if(!$items2){
          $player->sendMessage('§eINFO§f>>アイテムが足りません。');
          return false;
        }
        $items = array_merge($items, $items2);*/
        /*$money = self::MONEY[$rare] * (1 + ($lv+1)/4);
        if(!$this->plugin->playermanager->hasMoney($player, $money)){
          $player->sendMessage('§eINFO§f>>お金が足りません。');
          $inv->setItemInHand($armor);
          $this->plugin->playermanager->addWeaponAtk($player, $armor);
          return false;
        }
        $this->plugin->playermanager->takeMoney($player, $money);*/
        //$inv->removeItem($ri);
        /*foreach($items as $item){
          $player->getInventory()->removeItem($item);
        }*/
        $exp = $tag->getTag(Weapon::TAG_EXP)->getValue();
        $exp += $amount;
        $next = ($lv+1) * 100 * $rare;
        while($exp >= $next){
          $lv++;
          $tag->setString(Armor::TAG_LEVEL, $lv);
          $def = $tag->getTag(Armor::TAG_DEF)->getValue();
          $tag->setString(Armor::TAG_DEF, $def * 1.1);
          if($lv >= 16){
            $exp = 0;
            $next = 0;
            break;
          }else{
            $exp -= $next;
            $next = ($lv+1) * 100 * $rare;
          }
        }
        $tag->setString(Armor::TAG_EXP, $exp);
        $armor->getNamedTag()->setTag(Armor::TAG_ARMOR, $tag);
        $class = ArmorManager::getArmor();
        $armor = $class->setLore($armor);
        $armor = $class->setName($armor);
      }

      $inv->setItemInHand($armor);
      $player->sendMessage('§eINFO§f>>強化に成功しました！');
      //$this->plugin->playermanager->addWeaponAtk($player, $armor);
    }

    public function accReforge($tag, $player){
      $item = $player->getInventory()->getItemInHand();
      $id = $tag->getTag(Accessory::TAG_ACCESSORY_ID)->getValue();
      $rare = $tag->getTag(Armor::TAG_RARE)->getValue();
      $count = $tag->getTag(Accessory::TAG_REROLL_COUNT)->getValue();
      $money = (int)(self::MONEY[$rare] * (1/2)) ** ($count / 4 + 1);
      if(!$this->plugin->playermanager->hasMoney($player, $money)){
        $player->sendMessage('§eINFO§f>>お金が足りません。');
        return false;
      }
      $this->plugin->playermanager->takeMoney($player, $money);
      //$now = $tag->getTag(Weapon::TAG_PREFIX)->getValue();
      /*$prefix = PREFIX::get($now);
      if($prefix == 5){
        $this->plugin->getServer()->broadcastMessage('§a§lcongratulations!!§r>>'.$player->getName().'さんが天下一品を手に入れました！');
      }elseif($prefix == 6){
        $this->plugin->getServer()->broadcastMessage('§c§l§ofantastic!!§r>>'.$player->getName().'さんが唯一無二を手に入れました！');
      }*/
      //$tag->setString(Weapon::TAG_PREFIX, $prefix);
      $tag->setString(Accessory::TAG_REROLL_COUNT, $count+1);
      $item->getNamedTag()->setTag(Accessory::TAG_ACCESSORY, $tag);
      //$subs = Prefix::getSub($prefix);
      $class = $this->plugin->acc;
      $item = $class->removeAllSubFix($item);
      $item = $class->setSubFix($item);
      $item = $class->setLore($item);
      $item = $class->setName($item);
      $player->getInventory()->setItemInHand($item);
      $player->sendMessage('§eINFO§f>>強化に成功しました！');
      return true;
    }

    public function Reforge($data, $player){
      $inv = $player->getInventory();
      $armor = $inv->getItemInHand();
      $tag = $armor->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
      if(isset($tag)){
        return $this->AccReforge($tag, $player);
      }
      $tag = $armor->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
      if(!isset($tag)){
        $tag = $armor->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
        if(!isset($tag)){
	        $player->sendMessage('§eINFO>>§f武器か防具のみリフォージできます(手に持って行ってください)');
          return false;
        }
        $id = $tag->getTag(Weapon::TAG_WEAPON_ID)->getValue();
        $lv = $tag->getTag(Weapon::TAG_LEVEL)->getValue();
        $rare = $tag->getTag(Weapon::TAG_RARE)->getValue(); 
        $count = $tag->getTag(Weapon::TAG_REROLL_COUNT)->getValue();
        $money = (int)(self::MONEY[$rare] * (1/2)) ** ($count / 4 + 1);
        if(!$this->plugin->playermanager->hasMoney($player, $money)){
          $player->sendMessage('§eINFO§f>>お金が足りません。');
          $player->sendMessage('§eINFO§f>>リフォージには'.$money.'fl必要です。');
          return false;
        }
        $this->plugin->playermanager->takeMoney($player, $money);
        $this->plugin->playermanager->checkSubPrefix($player, $armor, -1);
        //$now = $tag->getTag(Weapon::TAG_PREFIX)->getValue();
        //$prefix = PREFIX::get($now);
        //$tag->setString(Weapon::TAG_PREFIX, $prefix);
        $tag->setString(Weapon::TAG_REROLL_COUNT, $count+1);
        $armor->getNamedTag()->setTag(Weapon::TAG_WEAPON, $tag);
        //$subs = Prefix::getSub($prefix);
        $class = WeaponManager::getWeapon();
        $armor = $class->removeAllSubFix($armor);
        $armor = $class->setSubFix($armor);
        $armor = $class->setLore($armor);
        $armor = $class->setName($armor);
        $this->plugin->playermanager->checkSubPrefix($player, $armor);
      }else{
        $id = $tag->getTag(Armor::TAG_ARMOR_ID)->getValue();
        $lv = $tag->getTag(Armor::TAG_LEVEL)->getValue();
        $rare = $tag->getTag(Armor::TAG_RARE)->getValue();
        $count = $tag->getTag(Weapon::TAG_REROLL_COUNT)->getValue();
        /*foreach($this->plugin->recipeArmorData as $r){
          if($r['make'] == $id){
            $recipe = $r;
          }
        }
        $items = $this->checkPlayerHasRecipeItem($player, $recipe);
        if(!$items){
          $player->sendMessage('§eINFO§f>>アイテムが足りません。');
          return false;
        }
        $items2 = $this->checkPlayerHasRecipeArmor($player, $recipe);
        if(!$items2){
          $player->sendMessage('§eINFO§f>>アイテムが足りません。');
          return false;
        }
        $items = array_merge($items, $items2);*/
        $money = (int)(self::MONEY[$rare] * (1/2)) ** ($count / 4 + 1);
        if(!$this->plugin->playermanager->hasMoney($player, $money)){
          $player->sendMessage('§eINFO§f>>お金が足りません。');
          return false;
        }
        $this->plugin->playermanager->takeMoney($player, $money);
        //$now = $tag->getTag(Weapon::TAG_PREFIX)->getValue();
        //$prefix = PREFIX::get($now);
        //$tag->setString(Weapon::TAG_PREFIX, $prefix);
        $tag->setString(Armor::TAG_REROLL_COUNT, $count+1);
        $armor->getNamedTag()->setTag(Armor::TAG_ARMOR, $tag);
        //$subs = Prefix::getSub($prefix);
        $class = ArmorManager::getArmor();
        $armor = $class->removeAllSubFix($armor);
        $armor = $class->setSubFix($armor);
        $armor = $class->setLore($armor);
        $armor = $class->setName($armor);
      }
      $inv->setItemInHand($armor);
      $player->sendMessage('§eINFO§f>>強化に成功しました！');
      return true;
    }

    public function AutoReforge($data, $player){
      $inv = $player->getInventory();
      $armor = $inv->getItemInHand();
      $tag = $armor->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
      if(!isset($tag)){
        $tag = $armor->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
        if(!isset($tag)){
	        $tag = $armor->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
          if(is_null($tag)){
            $player->sendMessage('§eINFO§f>>リフォージ可能なのは武器か防具のみです。(手に強化したい武器か防具を持って行ってください。)');
            return false;
          }
        }
      }
      if(!$this->Reforge($data, $player)) return false;
      $prefix = $tag->getTag(Weapon::TAG_PREFIX)->getValue();
      if($prefix != 6)
        $this->AutoReforge($data, $player);
    }

    public function checkPlayerHasRecipeItem($player, $recipe){
      $items = [];
      foreach($recipe['items'] as $id => $amount){
        $item = MobManager::getMob('shadow')->getItem($id)->setCount($amount);
        $items[] = $item;
        if(!$player->getInventory()->contains($item)){
          return false;
        }
      }
      return $items;
    }

    public function checkPlayerHasRecipeArmor($player, $recipe){
      $items = [];
      foreach($recipe['armor'] as $id => $amount){
        $item = Armor::getItem($id, 0, 0, 0, 0, 0)->setCount($amount);
        $items[] = $item;
        if(!$player->getInventory()->contains($item)){
          return false;
        }
      }
      return $items;
    }

    public function checkPlayerHasRecipeWeapon($player, $recipe){
      $items = [];
      foreach($recipe['weapon'] as $id => $amount){
        $item = Weapon::getItem($id, 0, 0, 0, 0)->setCount($amount);
        $items[] = $item;
        if(!$player->getInventory()->contains($item)){
          return false;
        }
      }
      return $items;
    }

    public function OrbFusion($data, $player){
      $item = $player->getInventory()->getItemInHand();
      $item->setCount(1);
      $tag = $item->getNamedTag(Skill::TAG_SKILL)->getTag(Skill::TAG_SKILL);
      if(!isset($tag)){
        $player->sendMessage('§eINFO§f>>強化したいオーブを手にもって行ってください！');
        return false;
      }
      $player->getInventory()->removeItem($item);
      if(!$player->getInventory()->contains($item)){
        $player->sendMessage('§eINFO§f>>強化に使うオーブがありません！');
        $player->sendMessage('§eINFO§f>>オーブの強化には同じレベル、スキルの物が2つ必要です！');
        $player->getInventory()->addItem($item);
        return false;
      }
      $player->getInventory()->removeItem($item);
      $lv = $tag->getTag(Skill::TAG_SKILL_LV)->getValue();
      if($lv >= 3){
        $player->sendMessage('§cERROR§f>>強化できるのは3LVまでです。');
        $player->getInventory()->addItem($item);
        $player->getInventory()->addItem($item);
        return false;
      }
      $money = 500;
      $player->sendMessage('§eINFO>>§f消費金額: '.$money.'fl');
      if(!$this->plugin->playermanager->hasMoney($player, $money)){
        $player->sendMessage('§eINFO>>§fお金が足りません!');
        $player->getInventory()->addItem($item);
        $player->getInventory()->addItem($item);
        return false;
      }
      $this->plugin->playermanager->takeMoney($player, $money);
      $id = $tag->getTag(Skill::TAG_SKILL_ID)->getValue();
      $tag->setString(Skill::TAG_SKILL_LV, $lv+1);
      $item->getNamedTag()->setTag(Skill::TAG_SKILL, $tag);
      $name = SkillManager::getSkill($id)->getName();
      $item->setCustomName($name.'§f[§dLV: §f'.($lv+1).']');
      $player->getInventory()->addItem($item);
      $player->sendMessage('§eINFO§f>>強化に成功しました!');
    }

    public function removeOrb($pos, $player){
      $money = 500;
      if(!$this->plugin->playermanager->hasMoney($player, $money)){
        $player->sendMessage('§eINFO§f>>お金が足りません！');
        return false;
      }
      $this->plugin->playermanager->takeMoney($player, $money);
      $inv = $player->getInventory();
      $armor = $inv->getItemInHand();
      $inv->removeItem($armor);
      $armtag = $armor->getNamedTag(Armor::TAG_ARMOR)->getTag(Armor::TAG_ARMOR);
      $flag = true;
      if(!isset($armtag)){ 
        $armtag = $armor->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
        $flag = false;
      }
      $skill = $armtag->getTag(Armor::TAG_SKILLS[$pos]);
      if(!isset($skill)) return false;
      $id = $skill->getValue();
      $lv = $armtag->getTag(Armor::TAG_SKILL_LV[$pos])->getValue();
      $item = SkillManager::getSkill($id)->getOrb($lv);
      $armtag->removeTag(Armor::TAG_SKILLS[$pos]);
      $armtag->removeTag(Armor::TAG_SKILL_LV[$pos]);
      $armtag->removeTag(Armor::TAG_SKILL_ORB[$pos]);
      if($flag){ 
        $armor->getNamedTag()->setTag(Armor::TAG_ARMOR, $armtag);
        $armor = ArmorManager::getArmor()->setLore($armor);
      }else{
        $armor->getNamedTag()->setTag(Accessory::TAG_ACCESSORY, $armtag);
        $armor = $this->plugin->acc->setLore($armor);
      }
      //$lore = $armor->getLore();
		  //$lore[2+$pos] = '§aSKILL'.($pos+1).'§f: --';
      $inv->addItem($item);
      $inv->addItem($armor);
      $player->sendMessage('§eINFO§f>>取り外しました！');
    }


}




?>
    
    
    
    
	
	
