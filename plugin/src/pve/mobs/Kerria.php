<?php
namespace pve\mobs;

use pve\Type;

class Kerria extends Mobs {

  const NAME = 'Kerria';
  const HP = 20;
  const ATK = 10;
  const DEF = 0;
  const EXP = 10;
  const MONEY = 5;
  const DROPS = [16 => 15, 15 => 270];
  const SWORD = [2 => 3];
  const ARMOR = [1 => 3];
  const ORB = [17 => 3];

  const TYPE = TYPE::FIRE;
  
}
?>