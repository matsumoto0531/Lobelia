<?php
namespace pve;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\event\Listener;

use pve\PlayerManager;

class StyleForm implements Listener{
	
	const STYLE_FORM_ID = 7649;
  const STATUS_FORM_ID = 7650;
  const MENU_FORM_ID = 7651;
  const SKILL_FORM_ID = 7652;
  const SKILL_CHECK_ID = 7653;
  const CHOSE_FORM_ID = 7654;
    const MONEY = [1000, 2000, 3000, 4000, 5000, 6000, 7000, 8500, 10000, 11500, 13000, 14500];
    const STYLES = ['sword', 'pi', 'bow', 'stick'];
    //const STYLE_NAMES = ['剣士', '採掘師', '弓人間', '魔道士'];
    const STYLE_NAMES = ['剣士'];
    const STATUS_NAMES = ['ちから', 'すばやさ', 'はんだんりょく', 'からだ', 'しゅうちゅうりょく', 'まりょく'];
    const STATUS = ['pow', 'agi', 'han', 'body', 'syu', 'magic'];

	
	public function __construct($plugin){
		$this->plugin = $plugin;
	}
	
	public function sendForm($player, $data, $id){
      $pk = new ModalFormRequestPacket;
      $pk->formId = $id;
      $pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
      $player->dataPacket($pk);
  }

  public function sendMenuForm($player){
    $buttons = [];
    $a = ['§lスタイル変更', '§lスキル変更'];
    foreach($a as $name){
      $buttons[] = ['text' => $name];
    }
    $data = [
        'type' => 'form',
        'title' => '§lMenu',
        'content' => '何をしますか？',
        'buttons' => $buttons
    ];
    $this->sendForm($player, $data, self::MENU_FORM_ID);
  }

  public function sendChoseForm($player){
    $buttons = [];
    $pos = ['skill1', 'skill2', 'skill3'];
    $i = 0;
    foreach($pos as $p){
      $skill = $this->plugin->playerData[$player->getName()]['skills'][$i];
      if($skill === -1) $name = '無し';
      else $name = WeaponSkillManager::getSkill($skill)->getName();
      $buttons[] = ['text' => $p.': '.$name];
      $i++;
    }
    $data = [
        'type' => 'form',
        'title' => '§lSkill選択',
        'content' => "付け替える場所を選んでください。\n§7Skill1: 右クリックで発動\n§7Skill2: スニークしながら右クリックで発動\nSkill3: ダッシュしながら右クリックで発動",
        'buttons' => $buttons
    ];
    $this->sendForm($player, $data, self::CHOSE_FORM_ID);
  }

  public function sendSkillForm($player){
    $buttons = [];
    /*$skills = WeaponSkillManager::getAll();
    $this->ids = [];*/
    $ids = $this->plugin->playerData[$player->getName()]['shoji'];
    foreach($ids as $id){
      $class = WeaponSkillManager::getSkill($id);
      $buttons[] = ['text' => $class->getName()];
      //$this->ids[] = $class->getId();
    }
    $data = [
        'type' => 'form',
        'title' => '§lSkill選択',
        'content' => 'どれをつけますか？',
        'buttons' => $buttons
    ];
    $this->sendForm($player, $data, self::SKILL_FORM_ID);
  }

  public function sendCheckSkillForm($player){
    $buttons = [];
    $ids = $this->plugin->playerData[$player->getName()]['shoji'];
    $skill = WeaponSkillManager::getAll()[$ids[$this->skill[$player->getName()]]];
    $name = $skill->getName();
    $des = $skill->getDes();
    $rank = $skill->getRank();
    $level = $skill->getLevel();
    $rank = $this->plugin->rankData[$rank];
    $time = $skill->getCt();
    $tp = $skill->getTp();
    $content = "{$name}\n§f{$des}\nクールタイム: {$time}秒\n消費TP: {$tp}\n§7必要ランク: {$rank}\n§7必要レベル: {$level}\nこれにしますか？";
    $buttons[] = ['text' => 'Yes'];
    $data = [
        'type' => 'form',
        'title' => '§lSkill選択',
        'content' => $content,
        'buttons' => $buttons
    ];
    $this->sendForm($player, $data, self::SKILL_CHECK_ID);
  }
	
	public function sendStyleForm($player){
      $buttons = [];
      foreach(self::STYLE_NAMES as $name){
        $buttons[] = ['text' => $name];
      }
      $data = [
			    'type' => 'form',
			    'title' => '§lStyle',
			    'content' => 'スタイルを選択してください。',
			    'buttons' => $buttons
	  ];
      $this->sendForm($player, $data, self::STYLE_FORM_ID);
  }
    
    public function sendStatusForm($player){
        $buttons = [];
        $n = $player->getName();
        foreach(self::STATUS_NAMES as $name){
          $buttons[] = ['text' => $name];
        }
        $data = [
                  'type' => 'form',
                  'title' => '§lStatus',
                  'content' => 'どこに振りますか？',
                  'buttons' => $buttons
        ];
        $data['content'] .= "\n現在のステータス";
        $data['content'] .= "\n§cちから§f: ".$this->plugin->playerData[$n]['pow'];
        $data['content'] .= "\n§bすばやさ§f: ".$this->plugin->playerData[$n]['agi'];
        $data['content'] .= "\n§7はんだんりょく§f: ".$this->plugin->playerData[$n]['han'];
        $data['content'] .= "\n§6からだ§f: ".$this->plugin->playerData[$n]['body'];
        $data['content'] .= "\n§0しゅうちゅうりょく§f: ".$this->plugin->playerData[$n]['syu'];
        $data['content'] .= "\n§aまりょく§f: ".$this->plugin->playerData[$n]['magic'];
        $data['content'] .= "\n\n所持ポイント: ".$this->plugin->playerData[$n]['point'];
        $this->sendForm($player, $data, self::STATUS_FORM_ID);
      }
    

    public function receiveMenuForm($player, $data){
        if(!isset($data)){
           return false;
        }
        if($data === 0) $this->sendStyleForm($player);
        if($data === 1) $this->sendChoseForm($player);
    }

    public function receiveChoseForm($player, $data){
      if(!isset($data)){
         return false;
      }
      $this->chose[$player->getName()] = $data;
      $this->sendSkillForm($player);
    }

    public function receiveSkillForm($player, $data){
      if(!isset($data)){
         return false;
      }
      $this->skill[$player->getName()] = $data;
      $this->sendCheckSkillForm($player);
    }

    public function receiveSkillCheckForm($player, $data){
      if(!isset($data)){
         return false;
      }
      $n = $player->getName();
      $d = $this->plugin->playerData[$n];
      $ids = $d['shoji'];
      $class = WeaponSkillManager::getSkill($ids[$this->skill[$n]]);
      if($d['rank'] < $class->getRank()){
        $player->sendMessage('§eINFO§f>>ランクが足りません!');
        return false;
      }
      if($this->plugin->playermanager->getLevel($player) < $class->getLevel()){
        $player->sendMessage('§eINFO§f>>レベルが足りません!');
        return false;
      }
      $player->sendMessage('§eINFO§f>>セットしました！');
      $this->plugin->playerData[$n]['skills'][$this->chose[$n]] = $ids[$this->skill[$n]];
    }

    public function receiveStyleForm($player, $data){
	    if(!isset($data)){
	       return false;
        }
        $this->plugin->playermanager->changeStyle($player, self::STYLES[$data]);
        $player->sendMessage('§eINFO§f>>'.self::STYLE_NAMES[$data].'に生まれ変わりました！');
    }
    
    public function receiveStatusForm($player, $data){
        if(!isset($data)) return false;
        if($this->plugin->playerData[$player->getName()]['point'] < 1){
            $player->sendMessage('§eINFO§f>>ポイントが足りません！');
            return false;
        }
        $this->plugin->playermanager->addStatus($player, self::STATUS[$data]);
        $this->plugin->playerData[$player->getName()]['point']--;
        $player->sendMessage('§eINFO§f>>'.self::STATUS_NAMES[$data].'が１上昇しました！');
    }
	
	public function onPacketReceive(DataPacketReceiveEvent $event){
	  $pk = $event->getPacket();
	  $player = $event->getOrigin()->getPlayer();
    if(is_null($player)) return false;
	  if($pk instanceof ModalFormResponsePacket){
	      $data = json_decode($pk->formData, true);
	      switch($pk->formId){
        case self::MENU_FORM_ID;
			    $this->receiveMenuForm($player, $data);
          break;
        case self::CHOSE_FORM_ID;
			    $this->receiveChoseForm($player, $data);
          break;
        case self::SKILL_FORM_ID;
			    $this->receiveSkillForm($player, $data);
          break;
        case self::SKILL_CHECK_ID;
			    $this->receiveSkillCheckForm($player, $data);
          break;
			  case self::STYLE_FORM_ID;
			    $this->receiveStyleForm($player, $data);
          break;
        case self::STATUS_FORM_ID;
			    $this->receiveStatusForm($player, $data);
				break;
		  }
	    }
	}
	
	
}