<?php
namespace pve;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\event\Listener;

use pve\MobManager;

class Setting implements Listener {
	
	const SELECT_FORM_ID = 901;
	const SPAWNSET_FORM_ID = 902;
	const FIELDSET_FORM_ID = 903;
	const ITEMSET_FORM_ID = 904;
	const WEAPON_SET_FORM_ID = 905;
	const ARMOR_SET_FORM_ID = 906;
	const PVE_ITEM_SET_FORM_ID = 907;
	
	public function __construct($plugin){
		$this->plugin = $plugin;
	}
	
  public function sendSettingForm($player){
      $buttons = [];
      $comments = ['spawn', 'field', 'item', 'weapon', 'armor'];
      foreach($comments as $name){
        $buttons[] = ['text' => $name];
      }
      $data = [
			    'type' => 'form',
			    'title' => '§lSetting',
			    'content' => '何を設定しますか？',
			    'buttons' => $buttons
	   ];
	   $this->sendForm($player, $data, self::SELECT_FORM_ID);
    }
    
    public function sendSpawnForm($player){
		$content = [];
		$content[] = ["type" => "input", "text" => "湧かせる場所"];
		$content[] = ["type" => "input", "text" => "mob名"];
		$content[] = ["type" => "input", "text" => "レベル"];
		$content[] = ["type" => "input", "text" => "数"];
		$content[] = ["type" => "input", "text" => "ボスかどうか(true or false)"];
		$data = [
					'type'=>'custom_form',
					'title'   => "§l湧き場所追加",
					'content' => $content
				];
	    $this->sendForm($player, $data, self::SPAWNSET_FORM_ID);
    }
    
    public function sendFieldForm($player){
		$content = [];
		$content[] = ["type" => "input", "text" => "フィールド名"];
		$content[] = ["type" => "input", "text" => "x"];
		$content[] = ["type" => "input", "text" => "y"];
		$content[] = ["type" => "input", "text" => "z"];
		$content[] = ["type" => "input", "text" => "isbattle"];
		$data = [
					'type'=>'custom_form',
					'title'   => "§lフィールド追加",
					'content' => $content
				];
	    $this->sendForm($player, $data, self::FIELDSET_FORM_ID);
    }
    
    public function sendItemForm($player){
		$content = [];
		$content[] = ["type" => "input", "text" => "id"];
		$content[] = ["type" => "input", "text" => "ItemId"];
		$content[] = ["type" => "input", "text" => "名前"];
		$content[] = ["type" => "input", "text" => "作れる武器id"];
		$content[] = ["type" => "input", "text" => "作れる防具id"];
		$content[] = ["type" => "input", "text" => "オーブのスキルid"];
		$content[] = ["type" => "input", "text" => "オーブのスキルレベル"];
		$data = [
					'type'=>'custom_form',
					'title'   => "§lアイテム編集",
					'content' => $content
				];
	    $this->sendForm($player, $data, self::ITEMSET_FORM_ID);
	}
	
	public function sendWeaponSettingForm($player){
		$content = [];
		$content[] = ["type" => "input", "text" => "id"];
		$content[] = ["type" => "input", "text" => "ItemId"];
		$content[] = ["type" => "input", "text" => "名前"];
		$content[] = ["type" => "input", "text" => "atk"];
		$content[] = ["type" => "input", "text" => "def"];
		$content[] = ["type" => "input", "text" => "sharp"];
		$content[] = ["type" => "input", "text" => "skillid"];
		$content[] = ["type" => "input", "text" => "type"];
		$data = [
					'type'=>'custom_form',
					'title'   => "§lアイテム編集",
					'content' => $content
				];
	    $this->sendForm($player, $data, self::WEAPON_SET_FORM_ID);
	}
	
	public function sendArmorSettingForm($player){
		$content = [];
		$content[] = ["type" => "input", "text" => "id"];
		$content[] = ["type" => "input", "text" => "ItemId"];
		$content[] = ["type" => "input", "text" => "名前"];
		$content[] = ["type" => "input", "text" => "def"];
		$content[] = ["type" => "input", "text" => "skilllv"];
		$content[] = ["type" => "input", "text" => "skillid"];
		$content[] = ["type" => "input", "text" => "type"];
		$data = [
					'type'=>'custom_form',
					'title'   => "§lアイテム編集",
					'content' => $content
				];
	    $this->sendForm($player, $data, self::ARMOR_SET_FORM_ID);
	}
	
	public function sendPveItemSettingForm($player){
		$content = [];
		$content[] = ["type" => "input", "text" => "id"];
		$content[] = ["type" => "input", "text" => "ItemId"];
		$content[] = ["type" => "input", "text" => "名前"];
		$data = [
					'type'=>'custom_form',
					'title'   => "§lアイテム編集",
					'content' => $content
				];
	    $this->sendForm($player, $data, self::PVE_ITEM_SET_FORM_ID);
    }
    
    public function sendForm($player, $data, $id){
      $pk = new ModalFormRequestPacket;
      $pk->formId = $id;
      $pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
      $player->getNetworkSession()->sendDataPacket($pk);
    }
      
    
    public function receiveSelectForm($data, $player){
		if(!isset($data)) return false;
		switch($data+902){
			case self::SPAWNSET_FORM_ID:
			  $this->sendSpawnForm($player);
			  break;
			case self::FIELDSET_FORM_ID:
			  $this->sendFieldForm($player);
			  break;
			case self::ITEMSET_FORM_ID:
			  $this->sendItemForm($player);
			  break;
			case self::WEAPON_SET_FORM_ID:
			  $this->sendWeaponSettingForm($player);
			  break;
			case self::ARMOR_SET_FORM_ID:
			  $this->sendArmorSettingForm($player);
			  break;
			case self::PVE_ITEM_SET_FORM_ID:
			  $this->sendPveItemSettingForm($player);
			  break;
	    }
	}
	
	public function receiveFieldForm($data, $player){
		if(!isset($data)) return false;
		if(!isset($data[0])){
			$player->sendMessage('§eINFO>>§fフィールド名が入力されていません');
			return false;
		}
		if(!isset($data[1]) or !isset($data[2]) or !isset($data[3])){
			$player->sendMessage('§eINFO>>§f座標が設定されていません');
			return false;
		}
		$this->plugin->fieldData[$data[0]] = [ 'x' => $data[1]*1, 'y' => $data[2]*1, 'z' => $data[3]*1, 'isbattle' => $data[4] ] ;
		$player->sendMessage('§eINFO>>§f設定が完了しました');
	}
	
	public function receiveSpawnForm($data, $player){
		if(!isset($data)) return false;
		if(!isset($this->plugin->fieldData[$data[0]])){
			$player->sendMessage('§eINFO>>§fフィールドがありません');
			return false;
		}
		$mob = MobManager::getMob($data[1]); 
		if(!isset($mob)){
			$player->sendMessage('§eINFO>>§fMobデータがありません');
			return false;
		}
		if(!isset($data[2]) or !isset($data[3])){
			$player->sendMessage('§eINFO>>§fレベルか湧き数が設定されていません');
			return false;
		}
		if(!isset($data[4])){
			$player->sendMessage('§eINFO>>§fボスかどうか');
			return false;
		}
		$pos = $player->getPosition();
		$this->plugin->spawnData[$data[0]][$data[1]] = [ 'level' => $data[2], 'amount' => $data[3], 'boss' => $data[4], 'pos' => ['x' =>  $pos->x, 'y' => $pos->y, 'z' => $pos->z]];
		for($i = 0; $i < $data[3]; $i++){
		  $this->plugin->mob->spawn($data[0], $data[1], $data[2], $data[4]);
		}
		$player->sendMessage('§eINFO>>§f設定が完了しました');
	}
	
	//[0 => ["itemid" => 0, "name" => 'error', "weapon" => 0, "armor" => 0]]]
	public function receiveItemForm($data, $player){
		if(!isset($data)) return false;
		foreach($data as $d){
		  if(!isset($d)){
			$player->sendMessage('§eINFO>>§fなにかが設定されていません');
			return false;
		  }
		}
		$this->plugin->itemData[$data[0]]= [ 'itemid' => $data[1], "name" => $data[2], "weapon" => $data[3], "armor" => $data[4], "skillid" => $data[5], "skilllv" => $data[6]];
		$player->sendMessage('§eINFO>>§fItem設定が完了しました');
	}

	public function receiveWeaponForm($data, $player){
		if(!isset($data)) return false;
		foreach($data as $d){
		  if(!isset($d)){
			$player->sendMessage('§eINFO>>§fなにかが設定されていません');
			return false;
		  }
		}
		$this->plugin->weaponData[$data[0]]= [ 'itemid' => $data[1], "name" => $data[2], "atk" => $data[3], "def" => $data[4], "sharp" => $data[5], "skillid" => $data[6], "type" => $data[7]];
		$player->sendMessage('§eINFO>>§fItem設定が完了しました');
	}

	public function receiveArmorForm($data, $player){
		if(!isset($data)) return false;
		foreach($data as $d){
		  if(!isset($d)){
			$player->sendMessage('§eINFO>>§なにかが設定されていません');
			return false;
		  }
		}
		$this->plugin->ArmorData[$data[0]]= [ 'itemid' => $data[1], "name" => $data[2], "def" => $data[3], "skilllv" => $data[5], "skillid" => $data[6], "type" => $data[7]];
		$player->sendMessage('§eINFO>>§fItem設定が完了しました');
	}

	public function receivePveItemForm($data, $player){
		if(!isset($data)) return false;
		foreach($data as $d){
		  if(!isset($d)){
			$player->sendMessage('§eINFO>>§fなにかが設定されていません');
			return false;
		  }
		}
		$this->plugin->PveItemData[$data[0]]= [ 'itemid' => $data[1], "name" => $data[2]];
		$player->sendMessage('§eINFO>>§fItem設定が完了しました');
	}
    
  public function onPacketReceive(DataPacketReceiveEvent $event)
  {
	$pk = $event->getPacket();
	$player = $event->getOrigin()->getPlayer();
	if($pk instanceof ModalFormResponsePacket)
	{
	  $data = json_decode($pk->formData, true);
	  switch($pk->formId){
		  case self::SELECT_FORM_ID:
		    $this->receiveSelectForm($data, $player);
		    break;
		  case self::SPAWNSET_FORM_ID:
		    $this->receiveSpawnForm($data, $player);
		    break;
		  case self::FIELDSET_FORM_ID:
		    $this->receiveFieldForm($data, $player);
		    break;
		  case self::ITEMSET_FORM_ID:
		    $this->receiveItemForm($data, $player);
			break;
		  case self::WEAPON_SET_FORM_ID:
			$this->receiveWeaponForm($data, $player);
			break;
		  case self::PVE_ITEM_SET_FORM_ID:
			$this->receivePveItemForm($data, $player);
			break;
	   }    
      }
  }
}
?>
