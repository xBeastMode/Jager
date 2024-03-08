<?php
namespace xBeastMode\Jager\InventoryMenu\Task;
use pocketmine\block\inventory\DoubleChestInventory;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use xBeastMode\Jager\Jager;
class InventoryOpenTask extends Task{
        /** @var Jager */
        protected $plugin;
        /** @var Player */
        protected $player;
        /** @var DoubleChestInventory */
        protected $chest_inventory;

        /**
         * InventoryOpenTask constructor.
         *
         * @param Jager                $plugin
         * @param Player               $player
         * @param DoubleChestInventory $chestInventory
         */
        public function __construct(Jager $plugin, Player $player, DoubleChestInventory $chestInventory){
                $this->plugin = $plugin;
                $this->player = $player;
                $this->chest_inventory = $chestInventory;
        }

        public function onRun(): void{
                if($this->player->isConnected()){
                        $this->player->setCurrentWindow($this->chest_inventory);
                }else{
                        $this->plugin->inventory_menu->closeInventory($this->player);
                }
        }
}