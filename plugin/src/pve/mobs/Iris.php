<?php
namespace pve\mobs;

use pve\Type;

class Iris extends Mobs {

  const NAME = 'Iris';
  const HP = 1000;
  const ATK = 500;
  const DEF = 53;
  const EXP = 100;
  const MONEY = 200;
  const DROPS = [42 => 1, 41 => 99];

  const TYPE = TYPE::THUNDER;
  
}
?>