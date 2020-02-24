<?php
namespace ChunkWorldEditorPlus\manager;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\command\async\asyncsetcommand;

use ChunkWorldEditorPlus\type\range;

use ChunkWorldEditorPlus\task\AsyncExecuteTask;

use ChunkWorldEditorPlus\ChunkWorldEditorAPI;

class MultiManager extends BaseManager{
	public static function init(){
		$array = [
			multisetcommand::class,
		];

		foreach($array as $className){
			self::registerCommand($className);
		}
	}

	public function onRun(String $command,Player $player,range $Range,array $args): bool{//mainより
		if(self::hasCommand($command)){
			$command = self::getCommand($command);//
			if($command->request() > count($args)){
				return true;
			}
			if(!$command->check($args)){
				return true;
			}
			Server::getInstance()->broadcastMessage($command->getStartMessage($player,$Range,$args));

			$thread = 4;

			$RangePos = $Range->getRangePos();
			$generator = ChunkWorldEditorAPI::onDivide($RangePos,$thread);
			foreach($generator as $RealRangePos){
				$argument = $command->Preprocessing($player,$player->getLevel(),$RangePos,$RealRangePos,$args);
				$asyncexecuter = new AsyncExecuteTask($player->getLevel(),$command,$RangePos,$RealRangePos,$argument);
				//$argument = $command->execute($Range->getRangePos(),$argument);
				//$command->Success($player->getLevel(),$argument);
				//Server::getInstance()->broadcastMessage($command->getEndMessage($player,$Range,$args));
			}
			return true;
		}
		return false;
	}
}
