<?php
namespace pve;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\math\Vector3;

class HomeCommand extends Command {

	const NAME = "home";
	const DESCRIPTION = "自分の家にTPします。";
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
            $sender->sendMessage('USAGE >> /home [番号]');
            return false;
        }

        if(!is_numeric($args[0])){
          $sender->sendMessage('§eINFO§f>>番号には数字を入力してください。');
          return false;
        }

        if($args[0] > 3){
            $sender->sendMessage('§eINFO§f>>番号が大きすぎます。');
            return false;
          }

        $name = $sender->getName();
        if(!isset($this->plugin->homeData[$name])){
            $sender->sendMessage('§eINFO§f>>homeが設定されていません。');
            $sender->sendMessage('§eINFO§f>>すでに家を購入されている場合は、opまでお声がけください。');
            return false;
        }

        if(!isset($this->plugin->homeData[$name][$args[0]])){
            $sender->sendMessage('§eINFO§f>>homeが設定されていません。');
            $sender->sendMessage('§eINFO§f>>すでに家を購入されている場合は、opまでお声がけください。');
            return false;
        }

        $data = $this->plugin->homeData[$name][$args[0]];
        $this->plugin->fieldmanager->toHomeField($sender, $data['field']);
        $pos = new Vector3($data['x'], $data['y'], $data['z']);
        $sender->teleport($pos);

        return true;
		
	}
}