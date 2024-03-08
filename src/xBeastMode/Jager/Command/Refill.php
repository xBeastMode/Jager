<?php
namespace xBeastMode\Jager\Command;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use xBeastMode\Jager\CustomItems\Bandage;
use xBeastMode\Jager\CustomItems\Boots;
use xBeastMode\Jager\CustomItems\Coat;
use xBeastMode\Jager\CustomItems\Flashlight;
use xBeastMode\Jager\CustomItems\HunterBandage;
use xBeastMode\Jager\CustomItems\LethalSword;
use xBeastMode\Jager\CustomItems\OneHitKillSword;
use xBeastMode\Jager\CustomItems\StrongAntiBioticPill;
use xBeastMode\Jager\CustomItems\StrongRubbingAlcohol;
use xBeastMode\Jager\CustomItems\UsedBandage;
use xBeastMode\Jager\CustomItems\UsedFlashlight;
use xBeastMode\Jager\CustomItems\VoltEnergyDrink;
use xBeastMode\Jager\CustomItems\WeakAntiBioticPill;
use xBeastMode\Jager\CustomItems\WeakRubbingAlcohol;
use xBeastMode\Jager\InventoryMenu\TransactionData;
use xBeastMode\Jager\Jager;
class Refill extends Command{
        /** @var Jager */
        protected $plugin;
        /**
         * Refill constructor.
         *
         * @param Jager $plugin
         */
        public function __construct(Jager $plugin){
                PermissionManager::getInstance()->addPermission(new Permission("command.refill", "permission to use /refill"));
                parent::__construct("refill", "grab jager custom items", "Usage: /refill", []);

                $this->setPermission("command.refill");
                $this->plugin = $plugin;
        }

        /**
         * @param CommandSender $sender
         * @param string        $commandLabel
         * @param string[]      $args
         *
         * @return mixed
         */
        public function execute(CommandSender $sender, string $commandLabel, array $args){
                if(!$sender instanceof Player){
                        $sender->sendMessage("Please run command in-game.");
                        return false;
                }
                if(!$this->testPermission($sender)){
                        return false;
                }
                $chest_inventory = $this->plugin->inventory_menu->openDoubleChestInventory($sender, function (TransactionData $data){
                        return false;
                }, [
                    "title" => "§l§8ITEM REFILL"
                ]);
                $chest_inventory->addItem(...[
                    (new Bandage())->setCount(64 * 9),
                    (new UsedBandage())->setCount(64 * 9),

                    (new StrongAntiBioticPill())->setCount(64 * 4),
                    (new WeakAntiBioticPill())->setCount(64 * 5),

                    (new Flashlight())->setCount(1),
                    (new Flashlight())->setCount(1),
                    (new Flashlight())->setCount(1),
                    (new Flashlight())->setCount(1),

                    (new UsedFlashlight())->setCount(1),
                    (new UsedFlashlight())->setCount(1),
                    (new UsedFlashlight())->setCount(1),
                    (new UsedFlashlight())->setCount(1),
                    (new UsedFlashlight())->setCount(1),

                    (new StrongRubbingAlcohol())->setCount(1),
                    (new StrongRubbingAlcohol())->setCount(1),
                    (new StrongRubbingAlcohol())->setCount(1),
                    (new StrongRubbingAlcohol())->setCount(1),

                    (new WeakRubbingAlcohol())->setCount(1),
                    (new WeakRubbingAlcohol())->setCount(1),
                    (new WeakRubbingAlcohol())->setCount(1),
                    (new WeakRubbingAlcohol())->setCount(1),
                    (new WeakRubbingAlcohol())->setCount(1),

                    (new HunterBandage())->setCount(64),

                    (new VoltEnergyDrink())->setCount(1),
                    (new VoltEnergyDrink())->setCount(1),
                    (new VoltEnergyDrink())->setCount(1),
                    (new VoltEnergyDrink())->setCount(1),

                    (new OneHitKillSword())->setCount(1),
                    (new LethalSword())->setCount(1),
                    (new Boots())->setCount(1),
                    (new Coat())->setCount(1),
                ]);
                return true;
        }
}