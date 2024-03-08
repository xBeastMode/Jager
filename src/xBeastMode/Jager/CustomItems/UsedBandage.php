<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\ItemIds;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\JagerPlayer;
class UsedBandage extends CustomItem{
        public function __construct(){
                parent::__construct(ItemIds::PAPER, 0, "Bandage");
                $this->setCustomName("§r§l§8» §4Used Bandage\n§r§7right click to use");
                $this->setLore(["§r§l§4[!] §cHeals 1 to 4 half hearts.", "§r§l§4[!] §cMay cause infection without anti-biotic or alcohol."]);
        }

        public function onUse(JagerPlayer $player): bool{
                if($player instanceof Innocent){
                        $heal = mt_rand(1, 4);

                        if(mt_rand(1, 2) === 1 || $player->anti_biotic_ticks > 0 || $player->alcohol_ticks > 0){
                                $player->getPlayer()->setHealth($player->getPlayer()->getHealth() + $heal);
                                $player->getPlayer()->sendMessage("§r§l§8» §2$heal §ahearts replenished.");
                        }else{
                                $player->getPlayer()->sendMessage("§r§l§8» §cThe bandage did not work. You have been infected.");

                                $player->infected = true;
                                $player->infection_severity = 1;
                        }
                        $player->getPlayer()->getInventory()->removeItem($this->setCount(1));
                        return true;
                }
                return false;
        }
}