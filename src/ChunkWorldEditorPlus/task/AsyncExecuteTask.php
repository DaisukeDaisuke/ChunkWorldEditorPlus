<?php
namespace ChunkWorldEditorPlus\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\level\Level;
use pocketmine\Server;

use ChunkWorldEditorPlus\command\BaseCommand;

class AsyncExecuteTask extends AsyncTask{
	public $level;//Main thread access only
	public $command;
	public $RangePos;
	public $RealRangePos;
	public $argument;

	public function __construct(Level $level,BaseCommand $command,array $RangePos,array $RealRangePos,array $argument){
		$this->level = $level;
		$this->command = serialize($command);
		$this->RangePos = serialize($RangePos);
		$this->RealRangePos = serialize($RealRangePos);
		$this->argument = serialize($argument);
	}

	public function onRun(){
		$command = (clone unserialize($this->command));//To prevent segmentation fault, we are cloning just in case.
		$argument = $command->execute(unserialize($this->RangePos),unserialize($this->RealRangePos),unserialize($this->argument));
		$this->setResult([$command,$argument]);
	}

	public function onCompletion(Server $server){
		Server::getInstance()->broadcastMessage("[WorldEditor_Plus]".$label."[1/2] 1つめの変更が終了しました。");
		Server::getInstance()->broadcastMessage("[WorldEditor_Plus]".$label."[2/2] 2つめの変更を開始します...");
		list($command,$argument) = $this->getResult();
		$command->Success($this->level,$argument);
		Server::getInstance()->broadcastMessage($command->getEndMessage());
	}
}