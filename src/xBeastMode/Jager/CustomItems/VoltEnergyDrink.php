<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use xBeastMode\Jager\Player\Hunter;
use xBeastMode\Jager\Player\JagerPlayer;
use xBeastMode\Jager\Utils;

class VoltEnergyDrink extends CustomItem{
        public function __construct(){
                parent::__construct(373, 0, "Volt Energy Drink");
                $this->setCustomName("§r§l§8» §l§cV§6O§eL§aT§b §9E§5N§cE§6R§eG§aY\n§r§7right click to use");
                $this->setLore(["§r§l§4[!] §cRemoves slowness for 15 seconds"]);
        }

        public function onUse(JagerPlayer $player): bool{
                if($player instanceof Hunter){
                        $player->speed_ticks = 15 * 20;
                        $player->getPlayer()->sendMessage("§r§l§8» §aEnergy activated for 15 seconds.");

                        Utils::playSound("ambient.weather.thunder", $player->getPlayer(), 1000, 1, true);

                        $player->getPlayer()->getInventory()->removeItem($this->setCount(1));
                        $player->getPlayer()->getInventory()->addItem(VanillaItems::GLASS_BOTTLE());

                        return true;
                }
                return false;
        }
}