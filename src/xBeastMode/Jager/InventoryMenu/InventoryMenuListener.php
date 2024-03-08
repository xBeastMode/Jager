<?php
namespace xBeastMode\Jager\InventoryMenu;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerQuitEvent;
use xBeastMode\Jager\Jager;
class InventoryMenuListener implements Listener{
        /** @var Jager */
        protected $plugin;

        /**
         * KitListener constructor.
         *
         * @param Jager $plugin
         */
        public function __construct(Jager $plugin){;
                $this->plugin = $plugin;
        }

        /**
         * @param PlayerQuitEvent $event
         */
        public function onPlayerQuit(PlayerQuitEvent $event){
                $this->plugin->inventory_menu->closeInventory($event->getPlayer());
        }

        /**
         * @param InventoryCloseEvent $event
         */
        public function onInventoryClose(InventoryCloseEvent $event){
                $this->plugin->inventory_menu->closeInventory($event->getPlayer());
        }

        /**
         * @param InventoryTransactionEvent $event
         */
        public function onInventoryTransaction(InventoryTransactionEvent $event){
                $this->plugin->inventory_menu->onInventoryTransaction($event);
        }

        /**
         * @param PlayerDropItemEvent $event
         */
        public function onPlayerDropItem(PlayerDropItemEvent $event){
                $this->plugin->inventory_menu->onPlayerDropItem($event);
        }
}