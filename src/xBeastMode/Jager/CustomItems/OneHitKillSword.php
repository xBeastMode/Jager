<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\ItemIds;
use xBeastMode\Jager\Player\Hunter;
use xBeastMode\Jager\Player\JagerPlayer;
use xBeastMode\Jager\Utils;
class OneHitKillSword extends CustomItem{
        public function __construct(){
                parent::__construct(ItemIds::DIAMOND_SWORD, 0, "One Hit Kill Sword");
                $this->setCustomName("§r§l§8» §4ONE HIT KILL SWORD");
        }

        public function getAttackPoints(): int{
                return 30;
        }

        public function onAttackPlayer(JagerPlayer $killer, JagerPlayer $player): bool{
                if($killer instanceof Hunter){
                        if($killer->attack_cooldown_ticks > 0){
                                $cooldown = round($killer->attack_cooldown_ticks / 20);

                                Utils::playSound("random.fizz", $killer->getPlayer(), 1000, 1, true);
                                $killer->getPlayer()->sendActionBarMessage("§r§l§8» §cSWORD ON COOLDOWN FOR §4$cooldown §cMORE SECONDS");
                                return true;
                        }else{
                                $killer->attack_cooldown_ticks = 60 * 20;
                        }
                }
                return false;
        }

        public function onUse(JagerPlayer $player): bool{
                return false;
        }
}