<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\VanillaItems;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\JagerPlayer;
class StrongRubbingAlcohol extends CustomItem{
        public function __construct(){
                parent::__construct(373, 0, "Strong Rubbing Alcohol");
                $this->setCustomName("§r§l§8» §4Strong Rubbing Alcohol\n§r§7right click to use");
                $this->setLore(["§r§l§4[!] §cHas 85 percent chance of stopping infection for 20 seconds.", "§r§l§4[!] §cWill hurt if infection is not stopped."]);
        }

        public function onUse(JagerPlayer $player): bool{
                if($player instanceof Innocent){
                        $chance = mt_rand(1, 100);
                        if($chance <= 85){
                                $player->alcohol_ticks = 20 * 20;
                                $player->getPlayer()->sendMessage("§r§l§8» §aInfection is gone for now.");
                        }else{
                                $player->hurt_ticks = 5;
                                $player->getPlayer()->sendMessage("§r§l§8» §cOuch! Infection was not stopped.");
                        }

                        $player->getPlayer()->getInventory()->removeItem($this->setCount(1));
                        $player->getPlayer()->getInventory()->addItem(VanillaItems::GLASS_BOTTLE());
                        return true;
                }
                return false;
        }
}