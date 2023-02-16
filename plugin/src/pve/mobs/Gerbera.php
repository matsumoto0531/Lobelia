<?php
namespace pve\mobs;

use pve\Type;

class Gerbera extends Mobs {

  const NAME = 'Gerbera';
  const HP = 1000;
  const ATK = 500;
  const DEF = 53;
  const EXP = 100;
  const MONEY = 200;
  const DROPS = [36 => 1, 35 => 99];

  const TYPE = TYPE::ICE;
  
}
?>