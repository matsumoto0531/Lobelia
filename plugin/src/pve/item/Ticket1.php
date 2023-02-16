<?php
namespace pve\item;

class Ticket1 extends Ticket {
    const LEVEL = 1;
    const ID = 16;
    const NAME = parent::NAME.''.self::LEVEL;
}