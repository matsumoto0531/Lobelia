<?php
namespace pve\item;

class Ticket5 extends Ticket {
    const LEVEL = 5;
    const ID = 20;
    const NAME = parent::NAME.''.self::LEVEL;
}