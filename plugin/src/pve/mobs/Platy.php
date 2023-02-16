<?php
namespace pve\mobs;

use pve\Type;

class Platy extends Mobs {

  const NAME = 'Platy';
  const HP = 1000;
  const ATK = 500;
  const DEF = 53;
  const EXP = 100;
  const MONEY = 200;
  const DROPS = [38 => 1, 37 => 99];

  const TYPE = TYPE::LIGHT;
  
}
?>