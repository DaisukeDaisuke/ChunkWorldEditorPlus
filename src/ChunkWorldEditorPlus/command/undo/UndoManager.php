<?php
namespace ChunkWorldEditorPlus\command\undo;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\editor\BaseCommand;

use ChunkWorldEditorPlus\ChunkWorldEditorAPI;

use ChunkWorldEditorPlus\command\ManagerInterface;

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
			
			if(!ChunkWorldEditorAPI::checkInt($args[0] ?? 0)){
				$player->sendMessage("スレッド数は不正の為、コマンドを実行することは出来ません。");
				return true;
			}

			$thread = (int) ($args[0] ?? 0);

			/*if($thread < 0){
				$player->sendMessage("スレッド数は不正の為、コマンドを実行することは出来ません。");
				return true;
			}*/

			if(!$undo->isCanUndo()){
				$player->sendMessage("undoデーターに関しましては、存在致しません為、コマンドを実行することは出来ません。");
				return true;
			}

			$backupChunks = $undo->getData();
			$ChunkTile = $undo->getChunkTile();
			$RangePos = $undo->getRangePos();
			//$AxisAlignedBB = $undo->getAxisAlignedBB();
			$level = $undo->getLevel();
			$args = $undo->getArgs();

			$TotalBlocks = range::CountBlocksByRangePos(...$RangePos);//
			
			$command = $undo->getTarget();//$command
			ChunkWorldEditorAPI::Tileundo($level,$RangePos,$ChunkTile,$args,[$command,"onTileUndo"]);

			//foreach($datas as $ThreadId => $undodata){
			if($thread === 0){
				Server::getInstance()->broadcastMessage("[WorldEditor_Plus] ".$player->getName()."が変更を開始します…(undo) : ".$TotalBlocks."ブロック)");

				$chunks = ChunkWorldEditorAPI::getChunks($level,...$RangePos);
				$argument = $command->onundo($RangePos,$RangePos,$chunks,$backupChunks,$args);
				$command->onUndoSuccess($level,$argument);
				return true;
			}

			Server::getInstance()->broadcastMessage("[WorldEditor_Plus] ".$player->getName()."が変更を開始します…(undo_multi ".$thread."スレッド) : ".$TotalBlocks."ブロック)");

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