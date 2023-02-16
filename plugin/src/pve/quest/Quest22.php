<?php
namespace pve\quest;

use pve\mobs\Mobs;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pve\WeaponManager;

class Quest22 extends Quest {
    const ID = 22;

    public function onTouch($player, $nn){
      $this->sendChooseForm($player);
    }

    public function sendChooseForm($player){
        $buttons = [['text' => 'アクセサリの付け替え'], ['text' => '倉庫を開く']];
        $data = [
                  'type' => 'form',
                  'title' => '§lBOX',
                  'content' => '何をしますか？',
                  'buttons' => $buttons
         ];
        $this->sendForm($player, $data, 777);
    }

    public function sendForm($player, $data, $id){
        $pk = new ModalFormRequestPacket;
        $pk->formId = $id;
        $pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
        $player->getNetworkSession()->sendDataPacket($pk);
    }

}