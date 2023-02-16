<?php
namespace pve\mobs;

use pve\Type;

class Bellis extends Mobs {

  const NAME = 'Bellis';
  const HP = 1000;
  const ATK = 500;
  const DEF = 53;
  const EXP = 100;
  const MONEY = 200;
  const DROPS = [32 => 1, 31 => 99];

  const TYPE = TYPE::WIND;
  
}
?>