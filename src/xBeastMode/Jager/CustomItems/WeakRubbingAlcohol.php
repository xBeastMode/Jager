<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\VanillaItems;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\JagerPlayer;
class WeakRubbingAlcohol extends CustomItem{
        public function __construct(){
                parent::__construct(373, 0, "Weak Rubbing Alcohol");
                $this->setCustomName("§r§l§8» §4Weak Rubbing Alcohol\n§r§7right click to use");
                $this->setLore(["§r§l§4[!] §cHas 25 percent chance of stopping infection for 7 seconds.", "§r§l§4[!] §cWill hurt if infection is not stopped."]);
        }

        public function onUse(JagerPlayer $player): bool{
                if($player instanceof Innocent){
                        $chance = mt_rand(1, 100);
                        if($chance <= 25){
                                $player->alcohol_ticks = 7 * 20;
                                $player->getPlayer()->sendMessage("§r§l§8» §aInfection is gone for a few moments.");
                        }else{
                                $player->hurt_ticks = 2;
                                $player->getPlayer()->sendMessage("§r§l§8» §cOuch! Infection was not stopped.");
                        }

                        $player->getPlayer()->getInventory()->removeItem($this->setCount(1));
                        $player->getPlayer()->getInventory()->addItem(VanillaItems::GLASS_BOTTLE());
                        return true;
                }
                return false;
        }
}