<?php
namespace pve;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\event\Listener;

use pve\dungeon\DungeonManager;

class FormManager implements Listener{
	
	const FIELD_FORM_ID = 7647;
	const DUNGEON_FORM_ID = 7648;
	const SELECT_FORM_ID = 1;
	const SPAWNSET_FORM_ID = 2;
	const FIELDSET_FORM_ID = 3;
	const ITEMSET_FORM_ID = 4;

	const MONEY = [10, 20, 30, 40, 50, 60, 70, 85, 100, 115, 130, 145];

	
	public function __construct($plugin){
		$this->plugin = $plugin;
	}
	
	public function sendForm($player, $data, $id){
      $pk = new ModalFormRequestPacket;
      $pk->formId = $id;
      $pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
      $player->getNetworkSession()->sendDataPacket($pk);
    }
	
	public function sendFieldForm($player){
      $buttons = [];
      foreach($this->plugin->fieldData as $name => $data){
        $buttons[] = ['text' => $name];
      }
      $data = [
			    'type' => 'form',
			    'title' => '§lFieldManager',
			    'content' => '行く階層を選んでください',
			    'buttons' => $buttons
	   ];
      $this->sendForm($player, $data, self::FIELD_FORM_ID);
	}
	
	public function sendDungeonForm($player, $id){
		$buttons = [];
		$buttons[] = ['text' => 'yes'];
		$this->chose[$player->getName()] = $id;
		$dungeon = DungeonManager::getDungeon($id);
		$money = self::MONEY[$dungeon->danger-1];
		$message = $dungeon->name.': §c危険度§f'.$dungeon->danger."\n消費金額: {$money}Fl\nこのダンジョンに挑戦しますか？";
		$data = [
				  'type' => 'form',
				  'title' => '§lDungeon',
				  'content' => $message,
				  'buttons' => $buttons
		 ];
		$this->sendForm($player, $data, self::DUNGEON_FORM_ID);
	  }
    
    public function receiveFieldForm($player, $data){
	    if(!isset($data)){
	       $this->plugin->fieldmanager->setNow($player);
	       return false;
	    }
	    $fielddata = $this->plugin->fieldData;
	    $field = current(array_slice($fielddata, $data, 1, true));
	    $name = array_keys($this->plugin->fieldData, $field)[0];
	    $this->plugin->fieldmanager->changeField($player, $this->plugin->fieldmanager->data[$player->getName()], $name);
	}

	public function receiveDungonForm($player, $data){
		if(!isset($data)){
			return false;
		}
		
		$dungeon = DungeonManager::getDungeon($this->chose[$player->getName()]);
		$money = self::MONEY[$dungeon->danger-1];
		if(!$this->plugin->playermanager->hasMoney($player, $money)){
		  $player->sendMessage('§eINFO§f>>お金が足りません！');
		  return false;
		}
		$this->plugin->playermanager->takeMoney($player, $money);
		if(!$this->plugin->party->isLeader($player)){
            $dungeon->onStart([$player]);
            return true;
        }
        if($dungeon->isDungeon($player)) return false;
        $players = $this->plugin->party->getPlayers($player);
		$dungeon->onStart($players);
		return true;
	}
	
	public function onPacketReceive(DataPacketReceiveEvent $event){
	  $pk = $event->getPacket();
	  $player = $event->getOrigin()->getPlayer();
	  if($pk instanceof ModalFormResponsePacket){
	      $data = json_decode($pk->formData, true);
	      switch($pk->formId){
			  case self::FIELD_FORM_ID;
			    $this->receiveFieldForm($player, $data);
				break;
			  case self::DUNGEON_FORM_ID;
			    $this->receiveDungonForm($player, $data);
			    break;
		  }
	    }
	}
	
	
}
