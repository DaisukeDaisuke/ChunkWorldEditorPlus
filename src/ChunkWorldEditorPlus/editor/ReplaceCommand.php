<?php
namespace ChunkWorldEditorPlus\editor;

use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\editor\BaseCommand;
use ChunkWorldEditorPlus\type\checker;
use ChunkWorldEditorPlus\type\range;

use ChunkWorldEditorPlus\ChunkWorldEditorAPI;

class ReplaceCommand extends BaseCommand{
	public function Preprocessing(Player $player,Level $level,array $RangePos,array $RealRangePos,array $args): array{
		parent::Preprocessing($player,$level, $RangePos,$RealRangePos,$args);
		$chunks = ChunkWorldEditorAPI::getChunks($level,...$RealRangePos);
        list($id,$damage) = checker::getId($args[0]);
        list($id1,$damage1) = checker::getId($args[1]);
		return [$chunks,$id,$damage,$id1,$damage1];
	}

	public function execute(array $RangePos,array $RealRangePos,array $argument): array{
		//var_dump($RealRangePos);
		list($sx,$sy,$sz,$ex,$ey,$ez) = $RealRangePos;
		list($chunks,$id,$damage,$id1,$damage1) = $argument;
		$chunks = ChunkWorldEditorAPI::DecodeChunks($chunks);

		$id &= 0xff;
		$damage &= 0xff;

		//$currentProgress = null;

		$currentChunkX = $sx >> 4;
		$currentChunkZ = $sy >> 4;
		$currentChunkY = $sz >> 4;

		$currentChunk = null;
		$currentSubChunk = null;
		$changedchunk = [];

		//$Executeundo = $this->isExecuteundo();
		//$undodata = "";

		for($x = $sx; $x <= $ex; ++$x){
			$chunkX = $x >> 4;
			for($z = $sz; $z <= $ez; ++$z){
				$chunkZ = $z >> 4;
				if($currentChunk === null or $chunkX !== $currentChunkX or $chunkZ !== $currentChunkZ){
					$currentChunkX = $chunkX;
					$currentChunkZ = $chunkZ;
					$currentSubChunk = null;
					$hash = Level::chunkHash($chunkX, $chunkZ);
					$currentChunk = $chunks[$hash];
					$changedchunk[$hash] = true;
					if($currentChunk === null){
						continue;
					}
				}
				for($y = $sy; $y <= $ey; ++$y){
					$chunkY = $y >> 4;
					if($currentSubChunk === null or $chunkY !== $currentChunkY){
                        $currentChunkY = $chunkY;
						$currentSubChunk = $currentChunk->getSubChunk($chunkY, true);
						if($currentSubChunk === null){
							continue;
						}
					}
                    if($currentSubChunk->getBlockId() === $id1&&$currentSubChunk->getBlockData() === $damage1){
                        $currentSubChunk->setBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $id, $damage);
                    }
				}
			}
		}
		return [$chunks,$changedchunk];
	}

	public function Success($level,$argument){
		parent::Success($level,$argument);
		ChunkWorldEditorAPI::setChunks($level,$argument[0],$argument[1]);//$chunks
	}

	public function onTileUndo(Level $level,array $RangePos,array $data,array $args): bool{//...?
        list($id1,$damage1) = checker::getId($args[1]);
        $vector3 = $data[2];
		return $level->getBlockIdAt($vector3->x,$vector3->y,$vector3->z) === $id1&&$level->getBlockDataAt($vector3->x,$vector3->y,$vector3->z) === $damage1;//
	}

	public function onundo(array $RangePos,array $RealRangePos,array $chunks,array $backupchunks,array $args): array{
		list($sx,$sy,$sz,$ex,$ey,$ez) = $RealRangePos;
		$chunks = ChunkWorldEditorAPI::DecodeChunks($chunks);
		$backupchunks = ChunkWorldEditorAPI::DecodeChunks($backupchunks);

        list($id1,$damage1) = checker::getId($args[1]);

		$currentChunkX = $sx >> 4;
		$currentChunkZ = $sy >> 4;
		$currentChunkY = $sz >> 4;

		$currentChunk = null;
		$currentSubChunk = null;

		$currentBackupChunk = null;
		$currentBackupSubChunk = null;

		$changedchunk = [];

		$count = 0;

		for($x = $sx; $x <= $ex; ++$x){
			$chunkX = $x >> 4;
			for($z = $sz; $z <= $ez; ++$z){
				$chunkZ = $z >> 4;
				if($currentChunk === null or $chunkX !== $currentChunkX or $chunkZ !== $currentChunkZ){
					$currentChunkX = $chunkX;
					$currentChunkZ = $chunkZ;
					$currentSubChunk = null;
					$hash = Level::chunkHash($chunkX, $chunkZ);
					$currentChunk = $chunks[$hash];
					$currentBackupChunk = $backupchunks[$hash];
					$changedchunk[$hash] = true;
					if($currentChunk === null||$currentBackupChunk === null){
						continue;
					}
				}
				for($y = $sy; $y <= $ey; ++$y){
					$chunkY = $y >> 4;
              		if($currentSubChunk === null or $chunkY !== $currentChunkY){
						$currentChunkY = $chunkY;
						$currentSubChunk = $currentChunk->getSubChunk($chunkY, true);
						$currentBackupSubChunk = $currentBackupChunk->getSubChunk($chunkY, true);
						if($currentSubChunk === null||$currentBackupSubChunk === null){
							continue;
						}
                    }
                    if($currentBackupSubChunk->getBlockId() === $id1&&$currentBackupSubChunk->getBlockData() === $damage1){
                        $currentSubChunk->setBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $currentBackupSubChunk->getBlockId($x & 0x0f, $y & 0x0f, $z & 0x0f), $currentBackupSubChunk->getBlockData($x & 0x0f, $y & 0x0f, $z & 0x0f));
                    }
				}
			}
		}
		return [$chunks,$changedchunk];
	}

	public function onUndoSuccess(Level $level,array $argument){
		ChunkWorldEditorAPI::setChunks($level,...$argument);
	}

	/*public static function getCommand(): String{
		return "cset";
	}*/

	public function request(): int{
		return 2;
	}

	public function getargs(array $args): array{//...?
		return checker::getId($args[0]);
	}

	public function check(array $args): bool{
        return checker::checkId($args[0])&&checker::checkId($args[1]);
	}

	public function getLabel(Player $player,array $args): String{
		return "chunk_replace";// 
	}

	public function getLabelOption(Player $player,array $args): String{
        $ids = checker::getId($args[0]);
        $ids1 = checker::getId($args[1]);
		return " ".$ids[0].":".$ids[1]." => ".$ids1[0].":".$ids1[1];
	}
}
