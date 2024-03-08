<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\ItemIds;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\JagerPlayer;
class WeakAntiBioticPill extends CustomItem{
        public function __construct(){
                parent::__construct(ItemIds::IRON_NUGGET, 0, "Weak Anti-Biotic Pill");
                $this->setCustomName("§r§l§8» §4Weak Anti-Biotic Pill\n§r§7right click to use");
                $this->setLore(["§r§l§4[!] §cHas 30 percent chance of stopping infection."]);
        }

        public function onUse(JagerPlayer $player): bool{
                if($player instanceof Innocent){
                        $chance = mt_rand(1, 100);
                        if($chance <= 35){
                                $player->infected = false;
                                $player->infection_severity = 0;
                                $player->getPlayer()->sendMessage("§r§l§8» §aAh... Infection is gone.");
                        }else{
                                $player->getPlayer()->sendMessage("§r§l§8» §cInfection was not stopped!");
                        }

                        $player->getPlayer()->getInventory()->removeItem($this->setCount(1));
                        return true;
                }
                return true;
        }
}