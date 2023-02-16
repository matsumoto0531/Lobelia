<?php
namespace pve\item;

class Kamae extends SkillBook {  
    const SKILL_ID = 18;
    const SKILL_NAME = '§b剣術: 構え';
    const NAME = parent::NAME.self::SKILL_NAME;
    const DESCRIPTION = self::SKILL_NAME.'§f'.parent::DESCRIPTION;
    const ID = 22;
}