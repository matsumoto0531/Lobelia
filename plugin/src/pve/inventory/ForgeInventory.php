<?php
namespace pve\inventory;
use pve\inventory\inventoryui\CustomInventory;
use pocketmine\player\Player;
use pocketmine\item\Item;
use pve\accessory\Accessory;
use pve\PlayerManager;

class ForgeInventory extends CustomInventory {
    public function __construct() {
        parent::__construct(10, "強化に使う石を中に入れてください");
    }

}