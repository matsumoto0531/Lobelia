<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest8 extends Quest {
    const ID = 8;
    const NAME = '§a§l風の守護者';
    const DES = 'Dahliaを倒す';

    const COUNT = 1;
    const TARGET = 'Dahlia';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            if($this->data[$name] >= self::COUNT){
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>頑張りなよ！');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(3, $this->plugin->playerData[$name]['quest'])){
                    $this->start($player);
                    $player->sendMessage('§a'.$nn.'§f>>守護者への挑戦を認めます！');
                    $message = ['やっと守護者とかいうのに会えるんですね！', 'とっととはっ倒して次の町に行くですよ！', '目指せ、エピナント奥地、ですっ！'];
                    $this->plugin->lbas->sendMessages($player, $message);
                    $this->data[$name] = 0;
                }else{
                    $player->sendMessage('§a'.$nn.'§f>>町の人の依頼をこなしたら、また来なさい。');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>おめでとう！');
        $player->sendMessage('§e§lINFO§r§f>>magnoliaへ行けるようになりました！');
        $messages = ['次はmagnolia、ですか。', '一体どんな町なんでしょうか...'];
        $this->plugin->lbas->sendMessages($player, $messages);
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 10000);
        $this->plugin->playermanager->addExp($player, 10000);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>頑張りなよ！');
    }

    public function onKill($player, $n){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        if($n === self::TARGET){
          $this->data[$name]++;
          if($this->data[$name] >= self::COUNT){
              $player->sendMessage('§aQuest§f>>条件達成!依頼者に報告しよう!');
              $messages = ['やりましたです！', 'お茶菓子さいさい、でしたね！'];
              $this->plugin->lbas->sendMessages($player, $messages);
              return true;
          }
          
          $player->sendMessage('§aQuest§f>>'.$this->data[$name].'体目!');
        }
    }
}