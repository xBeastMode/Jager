<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\ItemIds;
use xBeastMode\Jager\Player\JagerPlayer;
class Coat extends CustomItem{
        public function __construct(){
                parent::__construct(ItemIds::LEATHER_CHESTPLATE, 0, "Coat");
                $this->setCustomName("§r§l§8» §4COAT\n§r§7wear to use");
                $this->setLore(["§r§l§4[!] §cStops you from getting colds and bacteria."]);

                $this->getNamedTag()->setInt("customColor", 0xFF0000);
        }

        public function onUse(JagerPlayer $player): bool{
                return false;
        }
}