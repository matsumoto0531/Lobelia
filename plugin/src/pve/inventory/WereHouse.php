<?php
namespace pve\inventory;
use pve\inventory\inventoryui\CustomInventory;
use pocketmine\player\Player;
use pocketmine\item\Item;
use pve\accessory\Accessory;
use pve\PlayerManager;

class WereHouse extends CustomInventory {
    public function __construct() {
        parent::__construct(100, "倉庫");
    }

}