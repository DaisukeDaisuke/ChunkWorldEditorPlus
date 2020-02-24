<?php
namespace ChunkWorldEditorPlus\manager;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\type\range;

use ChunkWorldEditorPlus\command\sync\setcommand;

class SyncManager extends BaseManager{
	public static function init(){
		$array = [
			setcommand::class,
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

			$RangePos = $Range->getRangePos();
			$argument = $command->Preprocessing($player,$player->getLevel(),$RangePos,$RangePos,$args);
			$argument = $command->execute($RangePos,$RangePos,$argument);
			$command->Success($player->getLevel(),$argument);
			Server::getInstance()->broadcastMessage($command->getEndMessage());
			return true;
		}
		return false;
	}
}