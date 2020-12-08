<?php
namespace ChunkWorldEditorPlus\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\level\Level;
use pocketmine\Server;

use ChunkWorldEditorPlus\editor\BaseCommand;

class AsyncUndoTask extends AsyncTask{
	public $levelname;//Main thread access only...?
	public $command;
	public $RangePos;
	public $RealRangePos;
	public $chunks;
	public $backupchunks;
	public $args;

	public function __construct(String $levelname,BaseCommand $command,array $RangePos,array $RealRangePos,array $chunks,array $backupchunks,array $args){
		$this->levelname = $levelname;
		$this->command = serialize($command);
		$this->RangePos = serialize($RangePos);
		$this->RealRangePos = serialize($RealRangePos);
		$this->chunks = serialize($chunks);
		$this->backupchunks = serialize($backupchunks);
		$this->args = serialize($args);
	}

	public function onRun(){
		$command = (clone unserialize($this->command));//To prevent segmentation fault, we are cloning just in case.
		$argument = $command->onundo(unserialize($this->RangePos),unserialize($this->RealRangePos),unserialize($this->chunks),unserialize($this->backupchunks),unserialize($this->args));
		$this->setResult([$command,$argument]);
	}

	public function onCompletion(Server $server){
		//var_dump($this->levelname);
		$threadlabel = $this->getThreadLabel();
		if(($level = $server->getLevelByName($this->levelname)) === null){
			Server::getInstance()->broadcastMessage("[WorldEditor_Plus]".$threadlabel."[1/2] 2つ目の処理を実施中にエラーは発生致しました。(変更対象のワールドは読み込まれていない可能性は高い為、変更を実行することはことは出来ませんでした。)");
			return;
		}
		//Server::getInstance()->broadcastMessage("[WorldEditor_Plus]".$threadlabel."[1/2] 1つめの変更が終了しました。");
		Server::getInstance()->broadcastMessage("[WorldEditor_Plus]".$threadlabel."[2/2] 2つめの変更を開始します...");
		list($command,$argument) = $this->getResult();
		$command->onUndoSuccess($level,$argument);
		Server::getInstance()->broadcastMessage($command->getEndMessage());
	}

	public function getThreadLabel(){
		if(($threadId = (unserialize($this->command))->getThreadId()) !== null){
			return "[#".$threadId."]";
		}
		return "";
	}
}