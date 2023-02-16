<?php


declare(strict_types=1);

namespace pve\addItem;

use pocketmine\item\Armor;

class nether_armor extends Armor{
	public function __construct(int $meta = 0){
		parent::__construct(1020, $meta, "lobelia:wind_armor");
	}

	public function getDefensePoints() : int{
		return 10;
	}

	public function getMaxDurability() : int{
		return 66;
	}
}