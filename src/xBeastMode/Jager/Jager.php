<?php

declare(strict_types=1);

namespace xBeastMode\Jager;
use pocketmine\plugin\PluginBase;
use xBeastMode\Jager\Command\Refill;
use xBeastMode\Jager\Forms\FormManager;
use xBeastMode\Jager\Game\GameSettings;
use xBeastMode\Jager\Game\JagerGame;
use xBeastMode\Jager\InventoryMenu\InventoryMenu;
use xBeastMode\Jager\Task\TickGameTask;
class Jager extends PluginBase{
        /** @var GameManager */
        public $game_manager;
        /** @var FormManager */
        public $form_manager;
        /** @var InventoryMenu */
        public $inventory_menu;

        /** @var Jager */
        public static $instance;

        public function onEnable(): void{
                $this->game_manager = new GameManager($this);
                $this->form_manager = new FormManager($this);
                $this->inventory_menu = new InventoryMenu($this);

                $this->getServer()->getCommandMap()->register("jager", new Refill($this));

                $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
                $this->getScheduler()->scheduleRepeatingTask(new TickGameTask($this), 0);

                self::$instance = $this;

                if(!file_exists($this->getGamesFolder())){
                        mkdir($this->getGamesFolder());
                }

                $this->loadGames();
        }

        public function onDisable(): void{
                $this->game_manager->save();
                $this->game_manager->onForceClose();
        }

        /**
         * @return string
         */
        public function getGamesFolder(): string{
                return $this->getDataFolder() . "games/";
        }

        public function loadGames(){
                $default_settings = GameSettings::parseFromArray([
                    "name" => "Jäger",
                    "auto_start" => true,
                    "game_time" => 30,
                    "start_delay" => 30,
                    "needed" => 2,
                    "sign" => "-37:66:-1:scary",
                    "innocents" => "51:72:-95:scary",
                    "hunter" => "-101:64:-190:scary",
                    "spawn" => "-36:65:2:scary",
                    "game_lobby" => "-36:65:2:scary",
                ]);

                $game = new JagerGame($this, $default_settings);
                $this->game_manager->loadGame($game);
        }
}
