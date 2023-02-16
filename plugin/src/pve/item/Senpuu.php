<?php
namespace pve\item;

class Senpuu extends SkillBook {  
    const SKILL_ID = 5;
    const SKILL_NAME = '§a旋風';
    const NAME = parent::NAME.self::SKILL_NAME;
    const DESCRIPTION = self::SKILL_NAME.'§f'.parent::DESCRIPTION;
    const ID = 21;
}