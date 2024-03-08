<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\ItemIds;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\JagerPlayer;
class Flashlight extends CustomItem{
        public function __construct(){
                parent::__construct(ItemIds::TOTEM, 0, "Flashlight");
                $this->setCustomName("§r§l§8» §gFlashlight\n§r§7right click to use");
                $this->setLore(["§r§l§4[!] §cGives vision for 10-25 seconds."]);
        }

        public function onUse(JagerPlayer $player): bool{
                if($player instanceof Innocent){
                        $seconds = mt_rand(10, 25);

                        $player->vision_ticks = $seconds * 20;
                        $player->getPlayer()->sendMessage("§r§l§8» §aFlashlight activated for §2$seconds §aseconds.");

                        $player->getPlayer()->getInventory()->removeItem($this->setCount(1));
                        return true;
                }
                return false;
        }
}