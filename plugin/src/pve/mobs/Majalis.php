<?php
namespace pve\mobs;

use pve\Type;

class Majalis extends Mobs {

  const NAME = 'Majalis';
  const HP = 20;
  const ATK = 10;
  const DEF = 0;
  const EXP = 10;
  const MONEY = 5;
  const DROPS = [18 => 15, 17 => 270];
  const SWORD = [1 => 3];
  const ARMOR = [2 => 3];
  const ORB = [18 => 3];

  const TYPE = TYPE::ICE;
  
}
?>