<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\ItemIds;
use xBeastMode\Jager\Player\Hunter;
use xBeastMode\Jager\Player\JagerPlayer;
class HunterBandage extends CustomItem{
        public function __construct(){
                parent::__construct(ItemIds::PAPER, 0, "Hunter Bandage");
                $this->setCustomName("§r§l§8» §4Hunter Bandage\n§r§7right click to use");
                $this->setLore(["§r§l§4[!] §cHeals 5 to 10 half hearts."]);
        }

        public function onUse(JagerPlayer $player): bool{
                if($player instanceof Hunter){
                        $heal = mt_rand(5, 10);

                        $player->getPlayer()->setHealth($player->getPlayer()->getHealth() + $heal);
                        $player->getPlayer()->sendMessage("§r§l§8» §2$heal §ahearts replenished.");

                        $player->getPlayer()->getInventory()->removeItem($this->setCount(1));

                        return true;
                }
                return false;
        }
}