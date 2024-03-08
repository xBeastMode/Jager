<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\ItemIds;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\JagerPlayer;
class UsedFlashlight extends CustomItem{
        public function __construct(){
                parent::__construct(ItemIds::TOTEM, 0, "Used Flashlight");
                $this->setCustomName("§r§l§8» §gUsed Flashlight\n§r§7right click to use");
                $this->setLore(["§r§l§4[!] §c50 percent change of giving vision for 5-10 seconds."]);
        }

        public function onUse(JagerPlayer $player): bool{
                if($player instanceof Innocent){
                        $seconds = mt_rand(5, 10);

                        if(mt_rand(0, 100) <= 50){
                                $player->vision_ticks = $seconds * 20;
                                $player->getPlayer()->sendMessage("§r§l§8» §aFlashlight activated for §2$seconds §aseconds.");
                        }else{
                                $player->getPlayer()->sendMessage("§r§l§8» §cFlashlight failed.");
                        }

                        $player->getPlayer()->getInventory()->removeItem($this->setCount(1));
                        return true;
                }
                return false;
        }
}