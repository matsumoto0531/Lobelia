<?php
namespace pve\item;

class Ticket2 extends Ticket {
    const LEVEL = 2;
    const ID = 17;
    const NAME = parent::NAME.''.self::LEVEL;
}