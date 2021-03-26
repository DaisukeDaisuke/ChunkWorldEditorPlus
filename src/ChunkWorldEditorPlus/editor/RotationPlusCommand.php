<?php


namespace ChunkWorldEditorPlus\editor;


use ChunkWorldEditorPlus\ChunkWorldEditorAPI;
use ChunkWorldEditorPlus\type\checker;
use ChunkWorldEditorPlus\type\range;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\SubChunk;
use pocketmine\level\Level;
use pocketmine\Player;

class RotationPlusCommand extends BaseCommand{

	//crp　60 60 60
	public function Preprocessing(Player $player, Level $level, array $RangePos, array $RealRangePos, array $args): array{
		parent::Preprocessing($player, $level, $RangePos, $RealRangePos, $args);
		$chunks = ChunkWorldEditorAPI::getChunks($level, ...$RealRangePos);

		list($rsx, $rsy, $rsz, $rex, $rey, $rez) = $RangePos;
		list($sx, $sy, $sz, $ex, $ey, $ez) = $RealRangePos;

		$rx = (int) ($args[0] ?? 0);
		$ry = (int) ($args[1] ?? 0);
		$rz = (int) ($args[2] ?? 0);

		$r = deg2rad($rx);
		$cosX = cos($r);
		$sinX = sin($r);

		$r = deg2rad($ry);
		$cosY = cos($r);
		$sinY = sin($r);

		$r = deg2rad($rz);
		$cosZ = cos($r);
		$sinZ = sin($r);

		/*
		$array_z = [
			[$cosZ, -$sinZ, 0],
			[$sinZ, $cosZ, 0],
			[0, 0, 1],
		];

		$array_y = [
			[$cosY, 0, $sinY],
			[0, 1, 0],
			[-$sinY, 0, $cosY],
		];

		$array_x = [
			[1, 0, 0],
			[0, $cosX, -$sinX],
			[0, $sinX, $cosX],
		];
		*/

		//回転用 行列定義
		$array_z = [
			[$cosZ, -$sinZ, 0,0],
			[$sinZ, $cosZ, 0,0],
			[0, 0, 1, 0],
			[0, 0, 0, 1],
		];

		$array_y = [
			[$cosY, 0, $sinY,0],
			[0, 1, 0, 0],
			[-$sinY, 0, $cosY, 0],
			[0, 0, 0, 1],
		];

		$array_x = [
			[1, 0, 0, 0],
			[0, $cosX, -$sinX ,0],
			[0, $sinX, $cosX, 0],
			[0, 0, 0, 1],
		];

		//$rotation = ChunkWorldEditorAPI::multiply($array_z, $array_y);
		//$rotation = ChunkWorldEditorAPI::multiply($rotation, $array_x);

		//回転行列の合成(x+y+z)
		$rotation = ChunkWorldEditorAPI::multiply($array_x, $array_y);
		$rotation = ChunkWorldEditorAPI::multiply($rotation, $array_z);

		//$spos = ChunkWorldEditorAPI::multiply($rotation, [[0, 0, 0], [0, 0, 0], [0, 0, 0]]);

		//list($dsx, $dsy, $dsz, $dex, $dey, $dez) = range::diagonal($RangePos);

		$diffx = $ex - $sx;
		$diffy = $ey - $sy;
		$diffz = $ez - $sz;

		//回転行列実行
		//$spos = ChunkWorldEditorAPI::multiply($rotation, [[0, 0, 1], [0, 0, 1], [0, 0, 1]]);
		$epos = ChunkWorldEditorAPI::multiply($rotation, [[0, 0, 0, $diffx], [0, 0, 0, $diffy], [0, 0, 0, $diffz], [0, 0, 0, 1]]);
		$epos1 = ChunkWorldEditorAPI::multiply($rotation, [[0, 0, 0, 0], [0, 0, 0, $diffy], [0, 0, 0, $diffz], [0, 0, 0, 1]]);//24 0 0 = 23
		$epos2 = ChunkWorldEditorAPI::multiply($rotation, [[0, 0, 0, $diffx], [0, 0, 0, $diffy], [0, 0, 0, 0], [0, 0, 0, 1]]);//24 0 0 = 23

		$epos3 = ChunkWorldEditorAPI::multiply($rotation, [[0, 0, 0, $diffx], [0, 0, 0, 0], [0, 0, 0, $diffy], [0, 0, 0, 1]]);//24 0 0 = 23

		var_dump($epos3);

		/*
		 var_dump([
			self::max($epos[0][3],$epos[0][3],$epos[0][3]),
			self::max($epos1[1][3],$epos1[1][3],$epos1[1][3]),
			self::max($epos2[2][3],$epos2[2][3],$epos2[2][3]),
		]);

		var_dump([
			$epos,
			$epos2,
			$epos2
		]);
		*/

		$lx = (int) ($sx + self::max($epos[0][3],$epos1[0][3],$epos2[0][3],$epos3[0][3]));
		$ly = (int) ($sy + self::max($epos[1][3],$epos1[1][3],$epos2[1][3],$epos3[1][3]));
		$lz = (int) ($sz + self::max($epos[2][3],$epos1[2][3],$epos2[2][3],$epos3[2][3]));

		/*
		var_dump($lx,$ly,$lz);

		var_dump($epos1);

		var_dump([$epos[0][3], $epos[1][3], $epos[2][3]]);
		var_dump([[$sx, $sy, $sz], [(int) ($sx + $epos[0][3]), (int) ($sy + $epos[1][3]), (int) ($sz + $epos[2][3])]]);
		var_dump(["diff",[$diffx,$diffy,$diffz]]);
		var_dump(["RangePos",[$sx, $sy, $sz, $ex, $ey, $ez]]);
		*/

		//$targetchunks = ChunkWorldEditorAPI::getChunks($level, (int) ($rotation[0][3] + $diffx + 1), (int) ($rotation[1][3] + $diffy + 1), (int) ($rotation[2][3] + $diffz + 1), (int) ($epos[0][3] + $diffx + 1), (int) ($epos[1][3] + $diffy + 1), (int) ($epos[2][3] + $diffz + 1));
		//$targetchunks = ChunkWorldEditorAPI::getChunks($level, ...range::sortRangePos($sx, $sy, $sz, (int) ($sx + $epos[0][3]),  (int) ($sy + $epos[1][3]),  (int) ($sz + $epos[2][3])));

		$targetchunks = ChunkWorldEditorAPI::getChunks($level, ...range::sortRangePos($sx, $sy, $sz, $lx,$ly,$lz));

		//$targetchunks += ChunkWorldEditorAPI::getChunks($level, ...range::sortRangePos($ex, $sy, $sz, (int) ($ex + $epos[0][3]),  (int) ($sy + $epos[1][3]),  (int) ($sz + $epos[2][3])));
		//$targetchunks += ChunkWorldEditorAPI::getChunks($level, ...range::sortRangePos($sx, $sy, $ez, (int) ($sx + $epos[0][3]),  (int) ($sy + $epos[1][3]),  (int) ($ez + $epos[2][3])));

		//var_dump(["!!!!!",array_merge(array_keys($chunks),array_keys($targetchunks))]);

		//var_dump([$rotation, $epos, [$diffx, $diffy, $diffz]]);

		return [$chunks, $targetchunks, $rotation];
	}

	public function execute(array $RangePos, array $RealRangePos, array $argument): array{
		//var_dump($RealRangePos);
		list($sx, $sy, $sz, $ex, $ey, $ez) = $RealRangePos;
		list($rsx, $rsy, $rsz, $rex, $rey, $rez) = $RangePos;
		list($chunks, $targetchunks, $rotation) = $argument;

		//var_dump($targetchunks);

		$basechunks = ChunkWorldEditorAPI::DecodeChunks($chunks);

		$chunks = ChunkWorldEditorAPI::DecodeChunks($chunks);
		$targetchunks = ChunkWorldEditorAPI::DecodeChunks($targetchunks);

		$chunks = $chunks + $targetchunks;

		//var_dump(array_keys($chunks));

		$id = 0;
		$damage = 0;

		//$currentProgress = null;

		$currentChunkX = $sx >> 4;
		$currentChunkZ = $sy >> 4;
		$currentChunkY = $sz >> 4;

		/** @var ?Chunk $currentChunk */
		$currentChunk = null;
		/** @var ?SubChunk $currentSubChunk */
		$currentSubChunk = null;


		$currentChunkX1 = null;
		$currentChunkZ1 = null;
		$currentChunkY1 = null;

		/** @var ?Chunk $currentChunk */
		$currentChunk1 = null;
		/** @var ?SubChunk $currentSubChunk */
		$currentSubChunk1 = null;

		/** @var ?Chunk $currentChunk */
		$basecurrentChunk = null;
		/** @var ?SubChunk $currentSubChunk */
		$basecurrentsubChunk = null;

		$changedchunk = [];

		$return = [];

		//$Executeundo = $this->isExecuteundo();
		//$undodata = "";

		$tmparray = [[0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 1]];

		for($x = $sx; $x <= $ex; ++$x){
			$chunkX = $x >> 4;
			for($z = $sz; $z <= $ez; ++$z){
				$chunkZ = $z >> 4;
				if($currentChunk === null or $chunkX !== $currentChunkX or $chunkZ !== $currentChunkZ){
					$currentChunkX = $chunkX;
					$currentChunkZ = $chunkZ;
					$currentSubChunk = null;
					//var_dump([$x, $z]);

					$hash = Level::chunkHash($chunkX, $chunkZ);
					$currentChunk = $chunks[$hash];
					$basecurrentChunk = $basechunks[$hash];

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
						$basecurrentsubChunk = $basecurrentChunk->getSubChunk($chunkY, true);
						if($currentSubChunk === null){
							continue;
						}
					}

					$tmparray[0][3] = $x - $rsx;//2
					$tmparray[1][3] = $y - $rsy;
					$tmparray[2][3] = $z - $rsz;

					$return = ChunkWorldEditorAPI::multiply($rotation, $tmparray);

					$tx = (int) ($rsx + $return[0][3]);//self::test(]);
					$ty = (int) ($rsy + $return[1][3]);//self::test();
					$tz = (int) ($rsz + $return[2][3]);//self::test();

					/*
					var_dump(["input", $tmparray]);
					var_dump(["output",$return]);
					var_dump(["output", [$x, $y, $z], [$rsx, $rsy, $rsz], [$tx, $ty, $tz], $return]);
					*/

					$chunkX1 = $tx >> 4;
					$chunkY1 = $ty >> 4;
					$chunkZ1 = $tz >> 4;

					if($currentChunk1 === null or $chunkX1 !== $currentChunkX1 or $chunkZ1 !== $currentChunkZ1){
						$currentChunkX1 = $chunkX1;
						$currentChunkZ1 = $chunkZ1;
						$currentSubChunk1 = null;
						$hash1 = Level::chunkHash($chunkX1, $chunkZ1);
						$currentChunk1 = $chunks[$hash1];
						$changedchunk[$hash1] = true;
						if($currentChunk1 === null){
							var_dump("skip");
							continue;
						}
					}

					if($currentSubChunk1 === null or $chunkY1 !== $currentChunkY1){
						$currentChunkY1 = $chunkY1;
						$currentSubChunk1 = $currentChunk1->getSubChunk($chunkY1, true);
						if($currentSubChunk1 === null){
							continue;
						}
					}

					//var_dump(["?",[$tx, $ty, $tz], [$x, $y, $z]]);// & 0x0f

					$id = $basecurrentsubChunk->getBlockId($x & 0x0f, $y & 0x0f, $z & 0x0f);
					$damage = $basecurrentsubChunk->getBlockData($x & 0x0f, $y & 0x0f, $z & 0x0f);

					$currentSubChunk->setBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, 0, 0);
					$currentSubChunk1->setBlock($tx & 0x0f, $ty & 0x0f, $tz & 0x0f, $id, $damage);
				}
			}
		}
		return [$chunks, $changedchunk];
	}

	public function Success($level, $argument){
		parent::Success($level, $argument);
		ChunkWorldEditorAPI::setChunks($level, $argument[0], $argument[1]);//$chunks
	}

	public function onTileUndo(Level $level, array $RangePos, array $data, array $args): bool{
		return true;
	}

	public function onundo(array $RangePos, array $RealRangePos, array $chunks, array $backupchunks, array $args): array{
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

	public function onUndoSuccess(Level $level, array $argument){
		ChunkWorldEditorAPI::setChunks($level, ...$argument);
	}

	//crp 1:0 30 30 30;

	/*public static function getCommand(): String{
		return "cset";
	}*/

	public function request(): int{
		return 2;
	}

	public function getargs(array $args): array{
		return checker::getId($args[0]);
	}

	public function check(array $args): bool{
		return checker::checkInt($args[0])&&checker::checkInt($args[1])&&checker::checkInt($args[2]);
	}

	public function getLabel(Player $player, array $args): string{
		return "chunk_rotation_plus";//
	}

	public function getLabelOption(Player $player, array $args): string{
		$ids = checker::getId($args[0]);
		return " ".$ids[0].":".$ids[1];
	}

	/**
	 * @param float $v
	 * @return float
	 */
	public static function test(float $v){
		if($v == 0){
			return 0;
		}
		/*return ((int) $v) + ($v <=> 0.0);*/
		//return (int) $v;
		return $v + (($v <=> 0.0) * 16);
	}

	/**
	 * @param float ...$v
	 * @return float
	 */
	public static function max(...$v){
		if($v == 0){
			return 0;
		}

		$tmp = min($v);
		if($tmp < 0){
			return $tmp;
		}

		return max($v);
	}
}
