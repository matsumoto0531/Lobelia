<?php
namespace pve;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class Party implements Listener{

    const MENU = 500;
    const CHOOSE = 501;
    const KYOKA = 502;
    const KICK = 503;
    const LEAVE = 504;

    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->data = [];
        $this->req = [];
        $this->join = [];
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $this->join[$player->getName()] = false;
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $this->leaveParty($player);
        foreach($this->req as $name => $pn){
            if(in_array($player->getName(), $this->req[$name]));
            $this->req[$name] = array_diff($this->req[$name], [$player->getName()]);
            $this->req[$name] = array_values($this->req[$name]);
        }
    }

    public function getPlayers($player){
        $players = [];
        foreach($this->data[$player->getName()] as $name){
            $p = $this->plugin->getServer()->getPlayerExact($name);
            if(isset($p)) $players[] = $p;
        }
        return $players;
    }

    public function makeParty($player){
        if($this->join[$player->getName()]){
            $player->sendMessage('§eINFO§f>>すでにパーティーに参加しています！');
            return false;
        }
        $this->data[$player->getName()][] = $player->getName();
        $this->join[$player->getName()] = $player->getName();
        $this->req[$player->getName()] = [];
        $player->sendMessage('§eINFO§f>>パーティーを作成しました！');
    }

    public function leaveParty($player){
        $name = $player->getName();
        if(!$this->join[$name]){
            $player->sendMessage('§eINFO§f>>あなたはパーティーに参加していません。');
            return false;
        }
        if($this->isLeader($player)){
            foreach($this->data[$name] as $n){
                $p = $this->plugin->getServer()->getPlayerExact($n);
                if(isset($p)) $p->sendMessage('§eINFO§f>>パーティーが解散されました!');
                $this->join[$n] = false;
            }
            unset($this->data[$name]);
        }else{
            $this->data[$this->join[$name]] = array_diff($this->data[$this->join[$name]], [$name]);
            $this->data[$this->join[$name]] = array_values($this->data[$this->join[$name]]);
            $player->sendMessage('§eINFO§f>>'.$this->join[$name].'さんのパーティーから退出しました！');
            foreach($this->data[$this->join[$name]] as $n){
                $p = $this->plugin->getServer()->getPlayerExact($n);
                if(isset($p)) $p->sendMessage('§eINFO§f>>'.$name.'さんがパーティーから退出しました!');
            }
            $this->join[$name] = false;
        }
    }

    public function isLeader($player){
        if(isset($this->data[$player->getName()])) return true;
        return false;
    }

    public function sendMenuForm($player){
        $buttons = [];
        $comments = ['パーティーを作成', 'パーティーに参加申請', '申請を許可', 'メンバーをキック', 'パーティーから退出'];
        foreach($comments as $name){
          $buttons[] = ['text' => $name];
        }
        $data = [
                  'type' => 'form',
                  'title' => '§lパーティー',
                  'content' => '何をしますか？',
                  'buttons' => $buttons
        ];
        $this->sendForm($player, $data, self::MENU);
    }

    public function sendChooseForm($player){
        $buttons = [];
        $comments = [];
        foreach($this->data as $name => $players){
            $comments[] = $name."\n人数: ".count($players)."/ 4";
        }
        foreach($comments as $name){
          $buttons[] = ['text' => $name];
        }
        $data = [
                  'type' => 'form',
                  'title' => '§lパーティー',
                  'content' => '誰のパーティーに申請しますか？',
                  'buttons' => $buttons
        ];
        $this->sendForm($player, $data, self::CHOOSE);
    }

    public function sendKyokaForm($player){
        $buttons = [];
        $comments = [];
        if(!$this->isLeader($player)){
          $player->sendMessage('§eINFO§f>>この項目はリーダーしか操作できません。');
          return false;
        }
        foreach($this->req[$player->getName()] as $name){
            $comments[] = $name;
        }
        foreach($comments as $name){
          $buttons[] = ['text' => $name];
        }
        $data = [
                  'type' => 'form',
                  'title' => '§lパーティー',
                  'content' => '誰の申請を許可しますか？',
                  'buttons' => $buttons
        ];
        $this->sendForm($player, $data, self::KYOKA);
    }

    public function sendKickForm($player){
        $buttons = [];
        $comments = [];
        if(!$this->isLeader($player)){
            $player->sendMessage('§eINFO§f>>この項目はリーダーしか操作できません。');
            return false;
        }
        $name = $player->getName();
        foreach($this->data[$name] as $players){
            $comments[] = $players;
        }
        foreach($comments as $name){
          $buttons[] = ['text' => $name];
        }
        $data = [
                  'type' => 'form',
                  'title' => '§lパーティー',
                  'content' => '誰をキックしますか？',
                  'buttons' => $buttons
        ];
        $this->sendForm($player, $data, self::KICK);
    }

    public function receiveMenuForm($data, $player){
        if(!isset($data)) return false;
        switch($data){
          case 0:
            $this->makeParty($player);
          break;
          case 1:
            $this->sendChooseForm($player);
          break;
          case 2:
            $this->sendKyokaForm($player);
          break;
          case 3:
            $this->sendKickForm($player);
          break;
          case 4:
            $this->leaveParty($player);
          break;
        }
    }

    public function receiveChooseForm($data, $player){
        if(!isset($data)) return false;
        if($this->join[$player->getName()]){
            $player->sendMessage('§eINFO§f>>すでにパーティーに参加しています。');
            return false;
        }
        $key = key(array_slice($this->data, $data, 1, true));
        if(count($this->data[$key]) > 3){
            $player->sendMessage('§eINFO§f>>パーティーが満員です！');
            return false;
        }
        $this->req[$key][] = $player->getName();
        $player->sendMessage('§eINFO§f>>'.$key.'さんのパーティーに申請しました！');
        $p = $this->plugin->getServer()->getPlayerExact($key);
        if(isset($p)){
            $p->sendMessage('§eINFO§f>>'.$player->getName().'さんからパーティーに申請がきました！');
        }
    }

    public function receiveKyokaForm($data, $player){
        if(!isset($data)) return false;
        $pn = $player->getName();
        $name = $this->req[$pn][$data];
        $this->req[$pn] = array_diff($this->req[$pn], [$name]);
        $this->req[$pn] = array_values($this->req[$pn]);
        if(count($this->data[$pn]) > 3){
            $player->sendMessage('§eINFO§f>>パーティーが満員です！');
            return false;
        }
        foreach($this->data[$pn] as $n){
            $p = $this->plugin->getServer()->getPlayerExact($n);
            if(isset($p)){
                $p->sendMessage('§eINFO§f>>'.$name.'さんがパーティーに参加しました！');
            }
        }
        $pl = $this->plugin->getServer()->getPlayerExact($name);
        if(isset($pl)){
            $pl->sendMessage('§eINFO§f>>'.$pn.'さんのパーティーに参加しました！');
            /*foreach($this->req as $nm => $d){
                if(in_array($name, $this->req[$nm]));
                $this->req[$nm] = array_diff($this->req[$nm], [$name]);
                $this->req[$nm] = array_values($this->req[$nm]);
            }*/
        }
        $this->data[$pn][] = $name;
        $this->join[$name] = $pn;
    }

    public function receiveKickForm($data, $player){
        if(!isset($data)) return false;
        $pn = $player->getName();
        $name = $this->data[$pn][$data];
        $p = $this->plugin->getServer()->getPlayerExact($name);
        if(isset($p)) $this->leaveParty($p);
        $player->sendMessage('§eINFO§f>>'.$name.'さんを退出させました。');
    }

    public function sendForm($player, $data, $id){
        $pk = new ModalFormRequestPacket;
        $pk->formId = $id;
        $pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function onPacketReceive(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if(is_null($player)) return false;
        if($pk instanceof ModalFormResponsePacket){
            $data = json_decode($pk->formData, true);
            if(!isset($data)) return false;
            switch($pk->formId){
                case self::MENU;
                  $this->receiveMenuForm($data, $player);
                  break;
                case self::CHOOSE;
                  $this->receiveChooseForm($data, $player);
                  break;
                case self::KYOKA;
                  $this->receiveKyokaForm($data, $player);
                  break;
                case self::KICK;
                  $this->receiveKickForm($data, $player);
                  break;
            }
         }
    }
}