<?php
namespace pve\item;

class Ticket extends PveItem {
    
    const LEVEL = 0;
    const NAME = 'ダンジョン§c挑戦権 §f: §dLv: §f';
    const ITEM_ID = 339;
    const DESCRIPTION = 'ダンジョンへ挑戦できる。';

    

    public function Interact($event){
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        if(!$this->plugin->party->isLeader($player)){
            $player->sendMessage('§eINFO§f>>パーティーリーダのみチケットを使用できます。');
            $player->sendMessage('§eINFO§f>>パーティーを作成するか、リーダーにチケットを渡してください。');
            $player->sendMessage('§eINFO§f>>/party');
            return false;
        }
        if($this->plugin->dungeon->isDungeon($player)) return false;
        $players = $this->plugin->party->getPlayers($player);
        $this->plugin->dungeon->onStart($players, static::LEVEL);
        $count = $item->getCount();
        $player->getInventory()->setItemInHand($item->setCount($count-1));
    }
}