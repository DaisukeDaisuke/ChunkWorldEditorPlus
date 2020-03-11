<?php
namespace ChunkWorldEditorPlus\manager;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\ChunkWorldEditorAPI;
use ChunkWorldEditorPlus\command\setcommand;

use ChunkWorldEditorPlus\type\undo;
use ChunkWorldEditorPlus\type\range;

class SyncManager implements ManagerInterface{
	use ManagerTrait;

	public static function init(){
		$array = [
			"cset" => setcommand::class,
		];

		foreach($array as $commandName => $className){
			self::registerCommand($commandName,$className);
		}
	}

	public function onRun(String $command,Player $player,range $Range,array $args): bool{//mainより
		if(self::hasCommand($command)){
			$command = self::getCommand($command);//

			if($command->RequestRangePos()&&!$Range->isCompleted()){
				$player->sendMessage("範囲指定に関しましては、不正の為、コマンドを実行することは出来ません。");
				return true;
			}

			$Executeundo = ChunkWorldEditorAPI::isExecuteundo($args);

			if($command->request() > count($args)){
				return true;
			}
			if(!$command->check($args)){
				return true;
			}
			
			Server::getInstance()->broadcastMessage($command->getStartMessage($player,$Range,$args));

			//$RangePos = $command->RequestRangePos() ? $Range->getRangePos() : null;
			if($Executeundo){
				$undo = undo::get($player->getNane())->reset();
				$command->onundobackup($undo,$player,$player->getLevel(),$RangePos,$args);
			}
			$argument = $command->Preprocessing($player,$player->getLevel(),$RangePos,$RangePos,$args);
			$argument = $command->execute($RangePos,$RangePos,$argument);
			$command->Success($player->getLevel(),$argument);
			Server::getInstance()->broadcastMessage($command->getEndMessage());
			return true;
		}
		return false;
	}
}