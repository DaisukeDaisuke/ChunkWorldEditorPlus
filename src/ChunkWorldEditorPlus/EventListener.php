<?php
namespace ChunkWorldEditorPlus;

use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\item\Item;
use pocketmine\utils\Config;

use pocketmine\event\player\PlayerInteractEvent;

use ChunkWorldEditorPlus\type\range;

class EventListener implements Listener{
	public $ItemId;
	public $ItemDamage;
	public $ItemCustomName;

	public function __construct(Config $config){
		$this->ItemId = (int) $config->get("ItemId",369);
		$this->ItemDamage = (int) $config->get("ItemDamage",0);
		$this->ItemCustomName = (String) $config->get("Item_CustomName","§eChunkWorldEditor範囲指定");
		$Item = (Item::get($this->ItemId,$this->ItemDamage))->setCustomName($this->ItemCustomName);
		Item::addCreativeItem($Item);
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		if(
			$event->getItem()->getId() === $this->ItemId&&
			$event->getItem()->getDamage() === $this->ItemDamage&&
			$event->getItem()->getCustomName() === $this->ItemCustomName
		){
			$event->setCancelled();
			$player = $event->getPlayer();
			$name = $player->getName();

			$range = range::get($name);

			if($player->isSneaking()){
				$range->unsetPos();
				$player->sendMessage("[ChunkWorldEditor_Plus] 座標データは削除されました。");
				return;
			}

			$pos = $event->getBlock()->asVector3();
			if(!$range->hasPos(0)){
				$range->setPos(0,$pos);
				$player->sendMessage("[ChunkWorldEditor_Plus] POS1が設定されました。: $pos->x, $pos->y, $pos->z");
				if($range->isCompleted()){
					$player->sendMessage("(計".$range->CountBlocks()."ブロック)");
				}
			}else if(!$range->hasPos(1)){
				$range->setPos(1,$pos);
				$player->sendMessage("[ChunkWorldEditor_Plus] POS2が設定されました。: $pos->x, $pos->y, $pos->z");
				if($range->isCompleted()){
					$player->sendMessage("(計".$range->CountBlocks()."ブロック)");
				}
			}
		}
	}
}
