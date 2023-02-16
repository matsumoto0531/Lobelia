<?php
namespace pve;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;

class LogCommand extends Command {

	const NAME = "log";
	const DESCRIPTION = "ログのON/OFFを切り替えます。";
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
        
		if(!isset($args[0])){
            $sender->sendMessage('USAGE >> /log on または /log off');
            return false;
        }

        $bool = $args[0] === 'on' ? true : false;

        $this->plugin->mob->setLog($sender, $bool);
        $sender->sendMessage($args[0].'にしました！');
        return true;
		
	}
}