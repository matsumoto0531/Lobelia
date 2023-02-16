<?php
namespace pve;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\event\Listener;

use pve\PlayerManager;

class StyleForm implements Listener{
	
    const JOBSLOT_FORM_ID = 7648;
    const JOB_FORM_ID = 7649;
    const STYLE_NAMES = ['剣士'];

    const LIMIT = [0, 50, 100];

	
	public function __construct($plugin){
		$this->plugin = $plugin;
	}
	
	public function sendForm($player, $data, $id){
      $pk = new ModalFormRequestPacket;
      $pk->formId = $id;
      $pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
      $player->dataPacket($pk);
  }

  public function sendJobSlotForm($player){
    $buttons = [];
    $a = $this->plugin->playermanager->getJobs($player);
    $i = 1;
    foreach($a as $name){
      $buttons[] = ['text' => 'SLOT'.$i.''.$name];
      $i++;
    }
    $data = [
        'type' => 'form',
        'title' => '§lMenu',
        'content' => 'SLOTを選んでください。',
        'buttons' => $buttons
    ];
    $this->sendForm($player, $data, self::JOBSLOT_FORM_ID);
  }

  public function sendJobForm($player){
    $buttons = [];
    $a = $this->plugin->job->getList();
    foreach($a as $name){
      $buttons[] = ['text' => $name];
    }
    $data = [
        'type' => 'form',
        'title' => '§lMenu',
        'content' => 'JOBを選択してください。',
        'buttons' => $buttons
    ];
    $this->sendForm($player, $data, self:JOB_FORM_ID);
  }

    public function receiveJobSlot($player, $data){
        $this->choose[$player->getName()] = $data;
        $lv = $this->plugin->playermanager->getLevel($player);
        if($lv < self::LIMIT[$data])
		  $this->sendJobForm($player, $data);
        else{
          $player->sendMessage('§cERROR§r>>Lvが足りません!');
          $player->sendMessage('§cERROR§r>>'.$data+1.'つ目のJOBを設定するには、');
          $player->sendMessage('§cERROR§r>>'.self::LIMIT[$data].'Lvが必要です');
        }
    }

	public function onPacketReceive(DataPacketReceiveEvent $event){
	  $pk = $event->getPacket();
	  $player = $event->getOrigin()->getPlayer();
    if(is_null($player)) return false;
	  if($pk instanceof ModalFormResponsePacket){
	      $data = json_decode($pk->formData, true);
	      switch($pk->formId){
            case self::JOBSLOT_FORM_ID;
                $this->receiveJobSlot($player, $data);
          break;
            case self::JOB_FORM_ID;
			    $this->plugin->playermanager->setJob($player, $this->choose[$player->getName()], $data);
            break;
	      }
	    }
    }
	
	
}