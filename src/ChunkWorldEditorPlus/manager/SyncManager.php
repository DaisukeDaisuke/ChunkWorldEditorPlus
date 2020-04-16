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

class SyncManager implements ManagerInterface{
	use ManagerTrait;

	public static function init(){
		$array = [
			"cset" => setcommand::class,
			"cbox" => boxcommand::class,
			"csphere" => spherecommand::class,
			"csp" => spherecommand::class,
			
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

			$options = ChunkWorldEditorAPI::getOptions($args) ?? "";
			//$Executeundo = ChunkWorldEditorAPI::hasOption($options,"a");

			if($command->request() > count($args)){
				$player->sendMessage("引数は不足しております為、コマンドを実行することは出来ません。");
				return true;
			}
			if(!$command->check($args)){
				$player->sendMessage("引数は不正の為、コマンドを実行することは出来ません。");
				return true;
			}
			
			Server::getInstance()->broadcastMessage($command->getStartMessage($player,$Range,$args));

			$RangePos = $command->RequestRangePos() ? $Range->getRangePos() : null;
			//$RangePos = $Range->getRangePos();
			if(ChunkWorldEditorAPI::isExecuteundo()){
				$undo = undo::get($player->getName())->reset();
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