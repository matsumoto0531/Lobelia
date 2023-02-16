<?php
namespace pve\item;

class Iai2 extends SkillBook {  
    const SKILL_ID = 20;
    const SKILL_NAME = '§b居合 §e二§f段§f';
    const NAME = parent::NAME.self::SKILL_NAME;
    const DESCRIPTION = self::SKILL_NAME.'§f'.parent::DESCRIPTION;
    const ID = 24;
}