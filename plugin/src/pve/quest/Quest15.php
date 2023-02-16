<?php
namespace pve\quest;

use pve\mobs\Mobs;

use pve\WeaponManager;

class Quest15 extends Quest {
    const ID = 15;
    const NAME = 'ドキドキ§e光の星§f!?';
    const DES = '光の星を15個集めよう。';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            $item = $this->getItem(38);
            $item->setCount(15);
            if($player->getInventory()->contains($item)){
                $player->getInventory()->removeItem($item);
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>胸の高鳴りがとまらない！早く光の星15個を持ってきてくれ！');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(14, $this->plugin->playerData[$name]['quest'])){
                  $this->start($player);
                  $this->data[$name] = 0;
                }else{
                  $player->sendMessage('§a'.$nn.'§f>>ウオオオ(ノ・ω・)ノオオオォォォ-オオ');
                }
            }
        }
    }

    public function clear($player, $nn){
        $item = WeaponManager::getWeapon()->getItem(1, 38);
        $itemm = WeaponManager::getWeapon()->setOnlySkill($item, 38);
        $player->getInventory()->addItem($itemm);
        $player->sendMessage('§l§dGIFT§f>>'.$itemm->getName().'を手に入れた！');
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>ウッヒョォァ～');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 30000);
        $this->plugin->playermanager->addExp($player, 30000);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>ウオオオ(ノ・ω・)ノオオオォォォ-オオ');
    }
}