<?php
namespace pve\item;

class Ticket3 extends Ticket {
    const LEVEL = 3;
    const ID = 18;
    const NAME = parent::NAME.''.self::LEVEL;
}