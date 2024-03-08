<?php
namespace xBeastMode\Jager\Task;
use pocketmine\scheduler\Task;
use xBeastMode\Jager\Jager;
class TickGameTask extends Task{
        /** @var Jager */
        protected $plugin;
        private int $currentTick = 0;

        /**
         * InventoryMenuListener constructor.
         *
         * @param Jager $plugin
         */
        public function __construct(Jager $plugin){
                $this->plugin = $plugin;
        }

        public function onRun(): void{
                $this->plugin->game_manager->tick(++$this->currentTick);
        }
}