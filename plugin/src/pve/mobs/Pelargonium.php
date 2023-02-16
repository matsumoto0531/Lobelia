<?php
namespace pve\mobs;

use pve\Type;

class Pelargonium extends Mobs {

  const NAME = 'Pelargonium';
  const HP = 500;
  const ATK = 50;
  const DEF = 0;
  const EXP = 50;
  const MONEY = 50;
  const DROPS = [20 => 1, 19 => 99];

  const TYPE = TYPE::LIGHT;
  
}
?>