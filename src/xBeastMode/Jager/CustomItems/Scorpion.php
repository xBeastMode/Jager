<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\ItemIds;
use xBeastMode\Jager\Player\Hunter;
use xBeastMode\Jager\Player\JagerPlayer;
use xBeastMode\Jager\Utils;
class Scorpion extends CustomItem{
        /** @var ItemEntity*/
        protected $entity;

        public function __construct(){
                parent::__construct(ItemIds::NETHER_STAR, 0, "Scorpion");
                $this->setCustomName("§r§l§8» §5Scorpion\n§r§7right click to use");
                $this->setLore([
                    "§r§l§4[!] §cPlayer will teleport to you if picked up.",
                    "§r§l§4[!] §cWill take 10 half hearts if picked up by you."
                ]);
        }

        public function onUse(JagerPlayer $player): bool{
                if($player instanceof Hunter){
                        $player = $player->getPlayer();

                        $motion = $player->getDirectionVector();
                        $motion->x *= 2;
                        $motion->z *= 2;

                        $item = $player->getWorld()->dropItem($player->getPosition()->add(0, 1, 0), $this, $motion, 20);
                        $item->setNameTagVisible(false);
                        $item->setNameTagAlwaysVisible(false);

                        $item->setOwner($player->getName());
                        $this->entity = $item;

                        Utils::playSound("firework.launch", $player, 1000, 1, true);

                        $player->getInventory()->removeItem($this->setCount(1));
                        return true;
                }
                return false;
        }
}