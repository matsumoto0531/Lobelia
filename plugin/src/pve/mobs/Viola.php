<?php
namespace pve\mobs;

use pve\Type;

class Viola extends Mobs {

  const NAME = 'Viola';
  const HP = 500;
  const ATK = 50;
  const DEF = 0;
  const EXP = 50;
  const MONEY = 50;
  const DROPS = [28 => 1, 27 => 99];

  const TYPE = TYPE::THUNDER;
  
}
?>