<?php

namespace ChunkWorldEditorPlus\manager;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\type\undo;
use ChunkWorldEditorPlus\type\range;
use ChunkWorldEditorPlus\command\boxcommand;
use ChunkWorldEditorPlus\command\setcommand;

use ChunkWorldEditorPlus\ChunkWorldEditorAPI;
use ChunkWorldEditorPlus\command\spherecommand;
use ChunkWorldEditorPlus\task\AsyncExecuteTask;

/*
 For PHP <= 7.3.0
 Thank  https://www.php.net/manual/ja/function.array-key-last.php
*/

if(function_exists("array_key_last")){
	function array_key_last($array){
		if(!is_array($array) || empty($array)){
			return null;
		}
		return array_keys($array)[count($array)-1];
	}
}


class MultiManager implements ManagerInterface{
	use ManagerTrait;

	public static function init(){
		$array = [
			"cmset" => setcommand::class,
			"cmbox" => boxcommand::class,
			"cmsphere" => spherecommand::class,
			"cmsp" => spherecommand::class,
			
		];

		foreach($array as $commandName => $className){
			self::registerCommand($commandName,$className);
		}
	}

	public function onRun(String $command,Player $player,range $Range,array $args): bool{//mainより
		if(self::hasCommand($command)){
			
			/*$threadcount = (int) $args[array_key_last($args)];
			unset($args[array_key_last($args)]);
			if(!ChunkWorldEditorAPI::is_natural($threadcount)){
				return true;//
			}*/

			$command = self::getCommand($command);//

			if($command->RequestRangePos()&&!$Range->isCompleted()){
				$player->sendMessage("範囲指定に関しましては、不正の為、コマンドを実行することは出来ません。");
				return true;
			}

			$options = ChunkWorldEditorAPI::getOptions($args) ?? "";
			$Executeundo = ChunkWorldEditorAPI::hasOption($options,"a");

			if($command->request() > count($args)){
				return true;
			}

			$threadcount = 1;
			if($command->request()+1 <= count($args)){
				$threadcount = (int) $args[array_key_last($args)];
				unset($args[array_key_last($args)]);
				if(!ChunkWorldEditorAPI::is_natural($threadcount)){
					return true;
				}
			}

			//var_dump($threadcount);

			if(!$command->check($args)){
				return true;
			}
			if($threadcount === 1){
				Server::getInstance()->broadcastMessage($command->getStartMessage($player,$Range,$args,"_async"));
			}else{
				Server::getInstance()->broadcastMessage($command->getStartMessage($player,$Range,$args,"_multi"));
			}
			
			//$RangePos = $Range->getRangePos();
			$RangePos = $command->RequestRangePos() ? $Range->getRangePos() : null;
			if(ChunkWorldEditorAPI::isExecuteundo()){
				$undo = undo::get($player->getName())->reset();
				$command->onUndoBackup($undo,$player,$player->getLevel(),$RangePos,$args);
			}
			$generator = ChunkWorldEditorAPI::onDivide($RangePos,$threadcount);
			foreach($generator as $threadId => $RealRangePos){
				$executor = clone $command;//
				$executor->setThreadId($threadcount);
				$argument = $executor->Preprocessing($player,$player->getLevel(),$RangePos,$RealRangePos,$args);
				$asyncexecutor = new AsyncExecuteTask($player->getLevel()->getName(),$executor,$RangePos,$RealRangePos,$argument);
				Server::getInstance()->getAsyncPool()->submitTask($asyncexecutor);
				//$argument = $command->execute($Range->getRangePos(),$argument);
				//$command->Success($player->getLevel(),$argument);
				//Server::getInstance()->broadcastMessage($command->getEndMessage($player,$Range,$args));
			}
			return true;
		}
		return false;
	}
}
