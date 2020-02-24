<?php
namespace ChunkWorldEditorPlus\command\sync;

use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\command\BaseCommand;
use ChunkWorldEditorPlus\type\checker;
use ChunkWorldEditorPlus\type\range;

use ChunkWorldEditorPlus\ChunkWorldEditorAPI;

class setCommand extends BaseCommand{

	public function Preprocessing(Player $player,Level $level,array $RangePos,array $RealRangePos,array $args,?int $thread = null): array{
		$this->setThreadId($thread);
		list($id,$damage) = checker::getId($args[0]);
		$chunks = ChunkWorldEditorAPI::getChunks($level,...$RangePos);
		return [$chunks,$id,$damage];
	}

	public function execute(array $RangePos,array $RealRangePos,array $argument): array{
		list($sx,$sy,$sz,$ex,$ey,$ez) = $RealRangePos;
		list($chunks,$id,$damage) = $argument;
		$id = $id & 0xff;
		$damage = $damage & 0xff;

		$currentProgress = null;

		$currentChunkX = $sx >> 4;
		$currentChunkZ = $sy >> 4;
		$currentChunkY = $sz >> 4;

		$currentChunk = null;
		$currentSubChunk = null;
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
					$currentSubChunk->setBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $id, $damage);
				}
			}
		}
		return [$chunks];
	}

	public function Success($level,$argument){
		ChunkWorldEditorAPI::setChunks($level,$argument[0]);//$chunks
	}

	public static function getCommand(): String{
		return "cset";
	}

	public function request(): int{
		return 1;
	}

	public function check(array $args): bool{
		return checker::checkId($args[0]);
	}

	public function getLabel(Player $player,array $args): String{
		$ids = checker::getId($args[0]);
		return "chunk_set ".$ids[0].":".$ids[1];
	}
}
