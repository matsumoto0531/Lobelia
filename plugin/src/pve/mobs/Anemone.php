<?php
namespace pve\mobs;

use pve\Type;

class Anemone extends Mobs {

  const NAME = 'Anemone';
  const HP = 1000;
  const ATK = 500;
  const DEF = 53;
  const EXP = 100;
  const MONEY = 200;
  const DROPS = [40 => 1, 39 => 99];

  const TYPE = TYPE::DARK;
  
}
?>