<?php

namespace pve\bossbar;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;

class BossBarTask extends Task{

	public function __construct(){

	}

	public function onRun() : void{
		foreach (BossBarManager::getAllObjects() as $bossbar) {
			//$bossbar->move();
		}
	}

}
