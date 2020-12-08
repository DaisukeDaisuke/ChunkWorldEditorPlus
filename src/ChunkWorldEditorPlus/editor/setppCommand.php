<?php


namespace ChunkWorldEditorPlus\editor;


use ChunkWorldEditorPlus\ChunkWorldEditorAPI;
use ChunkWorldEditorPlus\type\checker;
use pocketmine\level\Level;
use pocketmine\Player;

class setppCommand extends BaseCommand{
	public function Preprocessing(Player $player, Level $level, array $RangePos, array $RealRangePos, array $args): array{
		parent::Preprocessing($player, $level, $RangePos, $RealRangePos, $args);
		$chunks = ChunkWorldEditorAPI::getChunks($level, ...$RealRangePos);
		list($id, $damage) = checker::getId($args[0]);
		return [$chunks, $id, $damage];
	}

	public function execute(array $RangePos, array $RealRangePos, array $argument): array{
		$time_start = microtime(true);
		//var_dump($RealRangePos);
		list($rsx, $rsy, $rsz, $rex, $rey, $rez) = $RangePos;
		list($sx, $sy, $sz, $ex, $ey, $ez) = $RealRangePos;
		list($chunks, $id, $damage) = $argument;
		$chunks = ChunkWorldEditorAPI::DecodeChunks($chunks);

		$id &= 0xff;
		$damage &= 0xff;

		$currentChunkX = $sx >> 4;
		$currentChunkZ = $sy >> 4;
		$currentChunkY = $sz >> 4;

		$currentChunk = null;
		$currentSubChunk = null;
		$changedchunk = [];

		var_dump($RangePos, $RealRangePos);

		$replace = ($damage << 4) | $damage;

		for($cx = $sx; $cx - 16 <= $ex; $cx += 16){
			$chunkX = $cx >> 4;
			for($cz = $sz; $cz - 16 <= $ez; $cz += 16){
				$chunkZ = $cz >> 4;
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
				for($y = $sy; $y - 16 <= $ey; $y += 16){
					$chunkY = $y >> 4;
					if($currentSubChunk === null or $chunkY !== $currentChunkY){
						$currentChunkY = $chunkY;
						$currentSubChunk = $currentChunk->getSubChunk($chunkY, true);
						if($currentSubChunk === null){
							continue;
						}
					}
					if(($rex >= ($cx + 16)&&$rsx <= ($cx - 16))&&($rey >= ($y + 16)&&$rsy <= ($y - 16))&&($rez >= ($cz + 16)&&$rsz <= ($cz - 16))){
						var_dump("!! ".$cx.":".$cz);
					}else{
						$y1 = min($chunkY << 4, $sy);
						$y2 = min($y1, $ey);

						$z1 = min($chunkZ << 4, $sz);
						$z2 = min($z1, $ez);

						$x1 = min($chunkX << 4, $sx);
						$x2 = min($x1, $ez);

						$replace_bytes = $y2 - $y1 + 1;
						$data = str_repeat("\x62", $replace_bytes);//y2 => y1

						$part = (int) (($y1 % 2) === 1);
						$replace_bytes1 = ((int) (($y2 - $y1) / 2)) + ((int) ($y1 % 2 === 0&&$y2 % 2 === 1));//0, 1//+1 +$part3 ????????

						$data5 = str_repeat(chr($replace), $replace_bytes1);//y2 => y1


						var_dump("?? ".$cx.":".$cz.", ".$y1.". ".$y2);
						var_dump([[$x1,$x2],[$y1,$y2],[$z1,$z2]]);
						$output1 = $currentSubChunk->getBlockIdArray();
						$output5 = $currentSubChunk->getBlockDataArray();

						if($y1 - $y2 <= 1){//$y << 4 //
							for($chx = $x1; $chx <= $x2; ++$chx){
								for($chz = $z1; $chz <= $z2; ++$chz){
									$output1 = substr_replace($output1, $data, (($chx << 8) | ($chz << 4) | $y1), $replace_bytes);

									$i = ($chx << 7) | ($chz << 3) | ($y2 >> 1);
									$shift = ($y2 & 1) << 2;
									$output5[$i] = chr((ord($output5[$i]) & ~(0xf << $shift)) | (($replace & 0xf) << $shift));

									$i = ($chx << 7) | ($chz << 3) | ($y1 >> 1);
									$shift = ($y1 & 1) << 2;
									$output5[$i] = chr((ord($output5[$i]) & ~(0xf << $shift)) | (($replace & 0xf) << $shift));
								}
							}
						}else{
							for($chx = $x1; $chx <= $x2; ++$chx){
								for($chz = $z1; $chz <= $z2; ++$chz){
									$output1 = substr_replace($output1, $data, (($chx << 8) | ($chz << 4) | $y1), $replace_bytes);

									$output5 = substr_replace($output5, $data5, (($chx << 7) | ($chz << 3) | ($y1 >> 1)) + $part, $replace_bytes1);//+1

									$i = ($chx << 7) | ($chz << 3) | ($y2 >> 1);
									$shift = ($y2 & 1) << 2;
									$output5[$i] = chr((ord($output5[$i]) & ~(0xf << $shift)) | (($replace & 0xf) << $shift));

									$i = ($chx << 7) | ($chz << 3) | ($y1 >> 1);
									$shift = ($y1 & 1) << 2;
									$output5[$i] = chr((ord($output5[$i]) & ~(0xf << $shift)) | (($replace & 0xf) << $shift));
								}
							}
						}
					}
				}

			}
		}
		$time = microtime(true) - $time_start;
		echo "{$time} 秒";

		return [$chunks, $changedchunk];
	}

	public
	function Success($level, $argument){
		parent::Success($level, $argument);
		ChunkWorldEditorAPI::setChunks($level, $argument[0], $argument[1]);//$chunks
	}

	public
	function onTileUndo(Level $level, array $RangePos, array $data, array $args): bool{
		return true;
	}

	public
	function onundo(array $RangePos, array $RealRangePos, array $chunks, array $backupchunks, array $args): array{
		list($sx, $sy, $sz, $ex, $ey, $ez) = $RealRangePos;
		$chunks = ChunkWorldEditorAPI::DecodeChunks($chunks);
		$backupchunks = ChunkWorldEditorAPI::DecodeChunks($backupchunks);

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
					$currentSubChunk->setBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $currentBackupSubChunk->getBlockId($x & 0x0f, $y & 0x0f, $z & 0x0f), $currentBackupSubChunk->getBlockData($x & 0x0f, $y & 0x0f, $z & 0x0f));
				}
			}
		}
		return [$chunks, $changedchunk];
	}

	public
	function onUndoSuccess(Level $level, array $argument){
		ChunkWorldEditorAPI::setChunks($level, ...$argument);
	}

	/*public static function getCommand(): String{
		return "cset";
	}*/

	public
	function request(): int{
		return 1;
	}

	public
	function getargs(array $args): array{
		return checker::getId($args[0]);
	}

	public
	function check(array $args): bool{
		return checker::checkId($args[0]);
	}

	public
	function getLabel(Player $player, array $args): string{
		return "chunk_set";//
	}

	public
	function getLabelOption(Player $player, array $args): string{
		$ids = checker::getId($args[0]);
		return " ".$ids[0].":".$ids[1];
	}
}