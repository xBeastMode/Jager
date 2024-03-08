<?php
namespace xBeastMode\Jager\Player;
use pocketmine\item\Item;
use pocketmine\player\Player;
use xBeastMode\Jager\CustomItems\CustomItem;
use xBeastMode\Jager\Game\Game;
use xBeastMode\Jager\Jager;
use xBeastMode\Jager\Utils;
abstract class JagerPlayer{
        /** @var Jager */
        protected $plugin;
        /** @var Player */
        protected $player;
        /** @var Game */
        protected $game;

        /**
         * JagerPlayer constructor.
         *
         * @param Jager  $plugin
         * @param Player $player
         * @param Game   $game
         */
        public function __construct(Jager $plugin, Player $player, Game $game){
                $this->plugin = $plugin;
                $this->player = $player;
                $this->game = $game;
        }

        public function getPlugin(): Jager{
                return $this->plugin;
        }

        public function getPlayer(): Player{
                return $this->player;
        }

        public function getGame(): Game{
                return $this->game;
        }

        public function resetPlayer(){
                Utils::resetPlayer($this->player);
        }

        public function onItemUse(Item $item): bool{
                return CustomItem::onItemUse($this, $item);
        }

        public function onItemAttack(JagerPlayer $target, Item $item): bool{
                return CustomItem::onItemAttack($this, $target, $item);
        }

        public function onPhaseStart(int $phase){
        }

        abstract public function onStart();
        abstract public function onEnd();
        abstract public function tick(int $tick);
        abstract public function onJoin();
        abstract public function onQuit(bool $server = false);
        abstract public function onDeath();
        abstract public function onWin();
        abstract public function onForceClose();
}