<?php
namespace pve\item;

class Iai extends SkillBook {  
    const SKILL_ID = 19;
    const SKILL_NAME = '§b居合§f';
    const NAME = parent::NAME.self::SKILL_NAME;
    const DESCRIPTION = self::SKILL_NAME.'§f'.parent::DESCRIPTION;
    const ID = 23;
}