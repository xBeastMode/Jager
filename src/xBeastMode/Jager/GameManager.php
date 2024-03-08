<?php
namespace xBeastMode\Jager;
use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Sign;
use pocketmine\item\Item;
use pocketmine\player\Player;
use xBeastMode\Jager\Game\Game;
class GameManager{
        /** @var Game[] */
        protected $games = [];

        /** @var Game[] */
        protected $players;
        /** @var Game[] */
        protected $signs;

        /** @var Jager */
        protected $plugin;

        /**
         * InventoryMenuListener constructor.
         *
         * @param Jager $plugin
         */
        public function __construct(Jager $plugin){
                $this->plugin = $plugin;
        }

        /**
         * @param string $name
         *
         * @return null|Game
         */
        public function getGameByName(string $name): ?Game{
                return $this->games[$name] ?? null;
        }

        /**
         * @param Player $player
         *
         * @return null|Game
         */
        public function getGameByPlayer(Player $player): ?Game{
                return $this->players[spl_object_hash($player)] ?? null;
        }

        /**
         * @param Sign $sign
         *
         * @return null|Game
         */
        public function getGameBySign(Sign $sign): ?Game{
                return $this->signs[Utils::positionToString($sign->getPosition())] ?? null;
        }

        /**
         * @param Player $player
         * @param Game   $game
         */
        public function onPlayerJoin(Player $player, Game $game){
                $this->players[spl_object_hash($player)] = $game;
                $game->onPlayerJoin($player);
        }

        /**
         * @param Player $player
         * @param Game   $game
         */
        public function onSpectatorJoin(Player $player, Game $game){
                $this->addPlayer($player, $game);
                $game->onSpectatorJoin($player);
        }

        /**
         * @param Player $player
         * @param Chest  $chest
         * @param Game   $game
         *
         * @return bool
         */
        public function onChestUse(Player $player, Chest $chest, Game $game): bool{
                return $game->onChestUse($player, $chest);
        }

        /**
         * @param Player $player
         * @param Item   $item
         * @param Game   $game
         *
         * @return bool
         */
        public function onItemUse(Player $player, Item $item, Game $game): bool{
                return $game->onItemUse($player, $item);
        }

        /**
         * @param Player $killer
         * @param Player $target
         * @param Item   $item
         * @param Game   $game
         *
         * @return bool
         */
        public function onItemAttack(Player $killer, Player $target, Item $item, Game $game): bool{
                return $game->onItemAttack($killer, $target, $item);
        }

        /**
         * @param Player $player
         * @param Game   $game
         * @param bool   $server
         */
        public function onPlayerQuit(Player $player, Game $game, bool $server = false){
                $this->removePlayer($player);
                $game->onPlayerQuit($player, $server);
        }

        /**
         * @param Player $player
         * @param Game   $game
         */
        public function onPlayerDeath(Player $player, Game $game){
                $this->removePlayer($player);
                $game->onPlayerDeath($player);
        }

        /**
         * @param Player $player
         * @param Game   $game
         */
        public function addPlayer(Player $player, Game $game){
                $this->players[spl_object_hash($player)] = $game;
        }

        /**
         * @param Player $player
         */
        public function removePlayer(Player $player){
                unset($this->players[spl_object_hash($player)]);
        }

        /**
         * @param Game $game
         */
        public function loadGame(Game $game){
                $this->games[$game->settings->name] = $game;
                $this->loadGameSign($game);
        }

        /**
         * @param Game $game
         */
        public function loadGameSign(Game $game){
                $sign_position = $game->settings->sign_position;
                $sign = $sign_position->getWorld()->getTile($sign_position);
                if($sign instanceof Sign){
                        $this->signs[Utils::positionToString($sign_position)] = $game;
                }
        }

        /**
         * @param Game $game
         */
        public function unloadGame(Game $game){
                unset($this->games[$game->settings->name]);
        }

        /**
         * @param Game $game
         */
        public function resetGamePlayers(Game $game){
                foreach($this->players as $hash => $player_game){
                        if($player_game === $game) unset($this->players[$hash]);
                }
        }

        /**
         * @param int $tick
         */
        public function tick(int $tick){
                foreach($this->games as $game) $game->tick($tick);
        }

        public function onForceClose(){
                foreach($this->games as $game) $game->onForceClose();
        }

        public function save(){
                foreach($this->games as $game) $game->save();
        }
}