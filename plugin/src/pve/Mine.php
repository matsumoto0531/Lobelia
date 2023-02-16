<?php
namespace pve;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\block\BlockFactory;
use pocketmine\block\Block;

use pve\item\ItemManager;
use pve\Callback;

class Mine implements Listener{

    public $plugin;

    public function __construct($plugin){
        $this->plugin = $plugin;
    }


    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if(!$this->plugin->getServer()->isOp($player->getName()) or $player->getGamemode()->getEnglishName() != "Creative") $event->cancel();
        if($player->getGamemode() !== 1){
            if($this->isOre($block)){
                $id = $block->getId();
                $item = $this->getOre($id, $player);
                if($item->getCount() !== 0){
                  $player->getInventory()->addItem($item);
                  $player->sendMessage($item->getName().'§fx'.$item->getCount().'§7を手に入れた');
                }
                $player->getWorld()->setBlock($block->getPosition(), BlockFactory::getInstance()->get(4, 0));
                $this->plugin->getScheduler()->scheduleDelayedTask(
                    new Callback([$this, 'resetBlock'], [$block, $id, $player->getWorld()]), 10 * 20);
            }
        }
    }

    public function resetBlock($block, $id, $level){
        $level->setBlock($block->getPosition(), BlockFactory::getInstance()->get($id, 0));
    }

    public function isOre($block){
        $id = $block->getId();
        $result = false;
        switch($id){
            case 14:
            case 15:
            case 16:
            case 56:
              $result = true;
        }

        return $result;
    }

    public function getOre($id, $player){
        switch($id){
            case 16:
                $itemid = 2;
            break;
            case 15:
                $itemid = 3;
            break;
            case 14:
                $itemid = 4;
            break;
            case 56:
                $itemid = 5;
            break;
        }
        $item = ItemManager::getItem($itemid)->getItem();
        $mine = $this->plugin->playermanager->getMine($player);
        $rand = mt_rand(0, 100);
        if($rand < (20+$mine) / ($itemid-1)**2){
          $rand++;
          if($rand > 64) $rand = 64;
          $item->setCount($rand);
        }else{
          $item->setCount(0);
        }
        return $item;
    }

}