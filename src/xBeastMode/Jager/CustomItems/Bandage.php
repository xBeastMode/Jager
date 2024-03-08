<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\ItemIds;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\JagerPlayer;
class Bandage extends CustomItem{
        public function __construct(){
                parent::__construct(ItemIds::PAPER, 0, "Bandage");
                $this->setCustomName("§r§l§8» §4Bandage\n§r§7right click to use");
                $this->setLore(["§r§l§4[!] §cHeals 1 to 4 half hearts."]);
        }

        public function onUse(JagerPlayer $player): bool{
                if($player instanceof Innocent){
                        $heal = mt_rand(1, 4);

                        $player->getPlayer()->setHealth($player->getPlayer()->getHealth() + $heal);
                        $player->getPlayer()->sendMessage("§r§l§8» §2$heal §ahearts replenished.");

                        $player->getPlayer()->getInventory()->removeItem($this->setCount(1));

                        return true;
                }
                return false;
        }
}