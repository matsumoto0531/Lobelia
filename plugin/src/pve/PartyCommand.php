<?php
namespace pve;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;

class PartyCommand extends Command {

	const NAME = "party";
	const DESCRIPTION = "パーティーメニューを開きます。";
	const USAGE = "";
	
	const PERMISSION = true;
	
	protected $plugin;

    public function __construct(Main $plugin)
    {
		parent::__construct(static::NAME, static::DESCRIPTION, static::USAGE);
		//parent::__construct("guild", static::DESCRIPTION, static::USAGE);

        //$this->setPermission(true);

        $this->plugin = $plugin;
    }
    
	public function execute(CommandSender $sender, string $label, array $args) : bool {
		if(!$this->plugin->isEnabled())
        {
            return false;
        }
        if(!$this->testPermission($sender))
        {
            return false;
        }
		
		if(!$sender instanceof Player){
			$sender->sendMessage('コンソールからは実行できません');
			return true;
        }

        $this->plugin->party->sendMenuForm($sender);
        return true;
		
	}
}