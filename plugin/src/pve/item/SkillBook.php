<?php
namespace pve\item;

class SkillBook extends PveItem {
    
    const SKILL_ID = 0;
    const NAME = '§d§l秘伝書 §f§r:';
    const ITEM_ID = 340;
    const DESCRIPTION = 'の技術が書き込まれた秘伝書。';

    

    public function Interact($event){
        $player = $event->getPlayer();
        $n = $player->getName();
        $item = $player->getInventory()->getItemInHand();
        if(in_array(static::SKILL_ID, $this->plugin->playerData[$n]['shoji'])){
            $player->sendMessage('§eそのスキルは習得済みです！');
            return false;
        }
        $this->plugin->playerData[$n]['shoji'][] = static::SKILL_ID;
        $player->sendMessage('§eINFO§f>>'.static::SKILL_NAME.'§f§rを習得しました!');
        $count = $item->getCount();
        $player->getInventory()->setItemInHand($item->setCount($count-1));
    }
}