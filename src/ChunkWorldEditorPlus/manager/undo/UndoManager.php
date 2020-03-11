<?php
namespace ChunkWorldEditorPlus\manager\undo;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\ChunkWorldEditorAPI;
use ChunkWorldEditorPlus\command\BaseCommand;

use ChunkWorldEditorPlus\manager\ManagerInterface;

use ChunkWorldEditorPlus\type\undo;
use ChunkWorldEditorPlus\type\range;

use ChunkWorldEditorPlus\task\AsyncUndoTask;

use pocketmine\tile\Tile;

class UndoManager implements ManagerInterface{
	//use ManagerTrait;

	public static function init(){
		
	}

	public function onRun(String $command,Player $player,range $Range,array $args): bool{//mainより
		if($command === "cundo"){
			/*
				/undo Thread数 = 1
			*/
			$undo = undo::get($player->getName());
			
			$thread = (int) ($args[0] ?? 1);
			
			if(!ChunkWorldEditorAPI::is_natural($thread)){
				return true;
			}

			if($thread < 1){
				return true;
			}

			if(!$undo->isCanUndo()){
				return true;
			}

			$backupChunks = $undo->getData();
			$ChunkTile = $undo->getChunkTile();
			$RangePos = $undo->getRangePos();
			//$AxisAlignedBB = $undo->getAxisAlignedBB();
			$level = $undo->getLevel();
			$args = $undo->getArgs();

			$command = $undo->getTarget($command);
			ChunkWorldEditorAPI::Tileundo($level,$RangePos,$ChunkTile,[$command,"onTileUndo"]);

			//foreach($datas as $ThreadId => $undodata){
			if($thread === 1){
				$chunks = ChunkWorldEditorAPI::getChunks($level,...$RangePos);
				$argument = $command->onundo($RangePos,$RangePos,$chunks,$backupChunks,$args);
				$command->onUndoSuccess($level,$argument);
				return true;
			}

			$generator = ChunkWorldEditorAPI::ondivide($RangePos,$thread);
			foreach($generator as $threadId => $RealRangePos){
				$executor = clone $command;
				$chunks = ChunkWorldEditorAPI::getChunks($level,...$RealRangePos);
				$asyncexecutor = new AsyncUndoTask($level->getName(),$executor,$RangePos,$RealRangePos,$chunks,$backupChunks,$args);
				Server::getInstance()->getAsyncPool()->submitTask($asyncexecutor);
				//$Chunks = ChunkWorldEditorAPI::getChunks($level,...$RealRangePos);
				//$command->onUndoPreprocessing($undodata,$pos[$ThreadId],$chunks);
			}
			//$undo = undo::get($player->getNane())->reset();
			//$undo->setChunkTile(ChunkWorldEditorAPI::TileBackup($player->getLevel(),$RangePos));
			//$undo->setLevelName($player->getLevel()->getName());
			//$undo->setTarget($command);
			
			
			return true;
		}
		return false;
	}
}