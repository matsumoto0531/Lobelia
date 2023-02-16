<?php
namespace pve;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;

class PayCommand extends Command {

	const NAME = "mpay";
	const DESCRIPTION = "お金をPAYします。";
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
        
		if(!isset($args[0]) or !isset($args[1])){
            $sender->sendMessage('USAGE >> /pay name 金額');
            return false;
        }

        $player = $this->plugin->getServer()->getPlayerByPrefix($args[0]);
		if(!isset($player)){
			$sender->sendMessage('そのプレイヤーは存在しません。名前は正確に入力してください。');
			return false;
        }
        if(!$this->plugin->playermanager->hasMoney($sender, $args[1])){
            $sender->sendMessage('§eINFO>>§fお金が足りません!');
            return false;
        }
        $this->plugin->playermanager->addMoney($player, $args[1], false);
        $player->sendMessage('§eINFO§f>>'.$sender->getName().'から'.$args[1].'円受け取りました！');
        $this->plugin->playermanager->takeMoney($sender, $args[1], false);
        $sender->sendMessage('§eINFO§f>>'.$player->getName().'に'.$args[1].'円送信しました！');

        return true;
		
	}
}