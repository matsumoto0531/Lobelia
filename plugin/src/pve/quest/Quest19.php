<?php
namespace pve\quest;

use pve\mobs\Mobs;

use pve\WeaponManager;

class Quest19 extends Quest {
    const ID = 19;
    const NAME = '§6雷の§f星集め！';
    const DES = '雷の星を15個集めよう。';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            $item = $this->getItem(42);
            $item->setCount(15);
            if($player->getInventory()->contains($item)){
                $player->getInventory()->removeItem($item);
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>雷の星15こ、頼んだぜ！');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(18, $this->plugin->playerData[$name]['quest'])){
                  $this->start($player);
                  $this->data[$name] = 0;
                }else{
                  $player->sendMessage('§a'.$nn.'§f>>よいしょ！');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>サンキュー！');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 30000);
        $this->plugin->playermanager->addExp($player, 30000);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>助かったぜ！');
    }
}