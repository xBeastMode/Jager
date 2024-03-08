<?php
namespace xBeastMode\Jager\Game;
use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Sign;
use pocketmine\block\utils\SignText;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use xBeastMode\Jager\Jager;
use xBeastMode\Jager\Player\Hunter;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\JagerPlayer;
use xBeastMode\Jager\Player\Spectator;
class Game{
        public const ROLE_HUNTER = 0;
        public const ROLE_INNOCENT = 1;
        public const ROLE_SPECTATOR = 2;

        public const STATE_WAITING = 0;
        public const STATE_RUNNING = 1;

        public const PHASE_COLLECT_RESOURCES = 1;
        public const PHASE_NIGHTFALL = 2;
        public const PHASE_INFECTION = 3;

        public const DEFAULT_NEEDED_PLAYERS = 2;

        /** @var int */
        protected $state;
        /** @var int */
        protected $phase;

        /** @var Hunter */
        protected $hunter;

        /** @var Innocent[] */
        protected $innocents = [];
        /** @var Spectator[] */
        protected $spectators = [];

        /** @var Jager */
        protected $plugin;

        /** @var GameSettings */
        public $settings;

        /** @var Sign */
        protected $game_sign;
        /** @var int */
        protected $start_delay;
        /** @var int */
        protected $game_time;

        /**
         * Game constructor.
         *
         * @param Jager        $plugin
         * @param GameSettings $settings
         */
        public function __construct(Jager $plugin, GameSettings $settings){
                $this->plugin = $plugin;
                $this->settings = $settings;

                $this->state = self::STATE_WAITING;
                $this->phase = self::PHASE_COLLECT_RESOURCES;

                $this->game_sign = $sign = $settings->sign_position->getWorld()->getTile($settings->sign_position);

                $this->start_delay = $settings->start_delay;
                $this->game_time = $settings->game_time;
        }

        /**
         * @return int
         */
        public function getState(): int{
                return $this->state;
        }

        /**
         * @param int $state
         */
        public function setState(int $state){
                $this->state = $state;
        }

        /**
         * @return int
         */
        public function getPhase(): int{
                return $this->phase;
        }

        /**
         * @param int $phase
         */
        public function setPhase(int $phase){
                $this->phase = $phase;
        }

        /**
         * @return int
         */
        public function getLastPhase(): int{
                return --$this->phase <= self::PHASE_COLLECT_RESOURCES ? self::PHASE_COLLECT_RESOURCES : self::PHASE_NIGHTFALL;
        }

        /**
         * @return int
         */
        public function getNextPhase(): int{
                return ++$this->phase >= self::PHASE_INFECTION ? self::PHASE_INFECTION : self::PHASE_NIGHTFALL;
        }

        /**
         * @return int
         */
        public function getGameTime(): int{
                return $this->game_time;
        }

        /**
         * @param int|null $game_time
         */
        public function setGameTime(?int $game_time = null){
                $this->game_time = $game_time ?? $this->settings->game_time;
        }

        /**
         * @return null|Hunter
         */
        public function getHunter(): ?Hunter{
                return $this->hunter;
        }

        /**
         * @param Player $player
         *
         * @return bool
         */
        public function isHunter(Player $player): bool{
                return $this->hunter && $this->hunter->getPlayer() === $player;
        }

        /**
         * @return Innocent[]
         */
        public function getInnocents(): array{
                return $this->innocents;
        }

        /**
         * @return Player[]
         */
        public function getInnocentsAsRegularPlayers(): array{
                return array_map(function (Innocent $player){ return $player->getPlayer(); }, $this->innocents);
        }

        /**
         * @return Innocent[]
         */
        public function getSpectators(): array{
                return $this->spectators;
        }

        /**
         * @return Player[]
         */
        public function getSpectatorsAsRegularPlayers(): array{
                return array_map(function (Spectator $player){ return $player->getPlayer(); }, $this->spectators);
        }

        /**
         * @return JagerPlayer[]
         */
        public function getAllPlayers(): array{
                $total_players = array_merge($this->innocents, $this->spectators);
                if($this->hunter !== null) $total_players[spl_object_hash($this->hunter->getPlayer())] = $this->hunter;
                return $total_players;
        }

        /**
         * @return Player[]
         */
        public function getAllAsRegularPlayers(): array{
                return array_map(function (JagerPlayer $player){ return $player->getPlayer(); }, $this->getAllPlayers());
        }

        /**
         * @param Player $player
         */
        public function onPlayerJoin(Player $player){
                $this->innocents[spl_object_hash($player)] = new Innocent($this->plugin, $player, $this);
                $this->innocents[spl_object_hash($player)]->onJoin();
        }

        /**
         * @param Player $player
         */
        public function onSpectatorJoin(Player $player){
                $this->spectators[spl_object_hash($player)] = new Spectator($this->plugin, $player, $this);
                $this->spectators[spl_object_hash($player)]->onJoin();
        }

        /**
         * @param Player $player
         *
         * @return JagerPlayer|null
         */
        public function getPlayerType(Player $player): ?JagerPlayer{
                $players = $this->getAllPlayers();
                return $players[spl_object_hash($player)] ?? null;
        }

        /**
         * @param string $message
         */
        public function broadcastMessage(string $message){
                $this->plugin->getServer()->broadcastMessage($message, $this->getAllAsRegularPlayers());
        }

        /**
         * @param string $message
         */
        public function broadcastTip(string $message){
                $this->plugin->getServer()->broadcastTip($message, $this->getAllAsRegularPlayers());
        }

        /**
         * @param string $title
         * @param string $subtitle
         * @param int    $in
         * @param int    $stay
         * @param int    $out
         */
        public function broadcastTitle(string $title, string $subtitle = "", int $in = 20, int $stay = 60, int $out = 20){
                $this->plugin->getServer()->broadcastTitle($title, $subtitle, $in, $stay, $out, $this->getAllAsRegularPlayers());
        }

        /**
         * @param int $phase
         */
        public function onPhaseStart(int $phase){
                $this->setPhase($phase);
                $this->setGameTime();

                foreach($this->getAllPlayers() as $player) $player->onPhaseStart($phase);
        }

        /**
         * @return bool
         */
        public function pickRandomHunter(): bool{
                $index = array_rand($this->innocents);
                $player = $this->innocents[$index];

                $hunter = new Hunter($this->plugin, $player->getPlayer(), $this);
                $hunter->onJoin();

                unset($this->innocents[$index]);
                $this->hunter = $hunter;

                return true;
        }

        public function onStart(){
                $this->pickRandomHunter();

                foreach($this->getAllPlayers() as $player) $player->onStart();

                $this->setState(self::STATE_RUNNING);
                $this->onPhaseStart(self::PHASE_COLLECT_RESOURCES);
        }

        /**
         * @param JagerPlayer|null $winner
         */
        public function onEnd(JagerPlayer $winner = null){
                if($winner !== null){
                        $winner->onWin();
                }

                foreach($this->getAllPlayers() as $player) $player->onEnd();

                $this->setState(self::STATE_WAITING);
                $this->setPhase(self::PHASE_COLLECT_RESOURCES);

                $this->hunter = null;
                $this->innocents = [];
                $this->spectators = [];

                $this->game_time = $this->settings->game_time;
                $this->plugin->game_manager->resetGamePlayers($this);
        }

        public function tick(int $tick){
                $auto_start = $this->settings->auto_start ?? true;
                $needed_players = $this->settings->needed_players ?? self::DEFAULT_NEEDED_PLAYERS;

                if($this->state === self::STATE_RUNNING && $tick % 20 === 0){
                        if(--$this->game_time <= 0 && $this->phase >= self::PHASE_INFECTION) $this->onEnd();
                        if($this->game_time <= 0) $this->onPhaseStart($this->getNextPhase());
                }

                if($auto_start && $this->state === self::STATE_WAITING && count($this->getInnocents()) >= $needed_players){
                        if($this->start_delay <= 0) $this->onStart();
                        if($tick % 20 === 0) --$this->start_delay;
                }else{
                        $this->start_delay = $this->settings->start_delay;
                }

                foreach($this->getAllPlayers() as $player){
                        $player->tick($tick);
                }

                if($this->game_sign !== null){
                        $this->game_sign->setText(new SignText([
                            "§r§l§8» §cgame: §f" . $this->settings->name,
                            "§r§l§8» §cin-game: §f" . count($this->getAllPlayers())
                        ]));
                }
        }

        public function save(){
                $this->settings->saveToJsonFile($this->plugin->getGamesFolder() . $this->settings->name . ".json");
        }

        public function onForceClose(){
                foreach($this->getAllPlayers() as $player) $player->onForceClose();
        }

        /**
         * @param Player $player
         * @param Chest  $chest
         *
         * @return bool
         */
        public function onChestUse(Player $player, Chest $chest): bool{
                return false;
        }

        /**
         * @param Player $player
         * @param Item   $item
         *
         * @return bool
         */
        public function onItemUse(Player $player, Item $item): bool{
                $player_type = $this->getPlayerType($player);
                return $player_type !== null && $player_type->onItemUse($item);
        }

        /**
         * @param Player $killer
         * @param Player $target
         * @param Item   $item
         *
         * @return bool
         */
        public function onItemAttack(Player $killer, Player $target, Item $item): bool{
                $killer_type = $this->getPlayerType($killer);
                $target_type = $this->getPlayerType($target);
                return $killer_type !== null && $target_type !== null && $killer_type->onItemAttack($target_type, $item);
        }

        /**
         * @param Player $player
         * @param bool   $server
         */
        public function onPlayerQuit(Player $player, bool $server = false){
                if($this->isHunter($player)){
                        $this->hunter->onQuit($server);

                        $innocents = $this->getInnocents();
                        if($this->state === self::STATE_RUNNING && count($innocents) === 1){
                                $this->onEnd(array_shift($innocents));
                        }else{
                                $this->onEnd();
                        }
                }else{
                        $jager_player = $this->innocents[spl_object_hash($player)] ?? $this->spectators[spl_object_hash($player)] ?? null;
                        if($jager_player !== null){
                                $jager_player->onQuit($server);
                        }
                        unset($this->innocents[spl_object_hash($player)], $this->spectators[spl_object_hash($player)]);

                        if($this->state === self::STATE_RUNNING && count($this->innocents) <= 0){
                                $this->onEnd($this->hunter);
                        }
                }
        }

        /**
         * @param Player $player
         */
        public function onPlayerDeath(Player $player){
                if($this->isHunter($player)){
                        $this->hunter->onDeath();

                        $damage_cause = $player->getLastDamageCause();
                        if($damage_cause instanceof EntityDamageByEntityEvent){
                                $killer = $damage_cause->getDamager();
                                if($killer instanceof Player && (($killer_type = $this->getPlayerType($killer)) instanceof Innocent)){
                                        $this->onEnd($killer_type);
                                        return;
                                }
                        }

                        $innocents = $this->getInnocents();
                        if($this->state === self::STATE_RUNNING && count($innocents) === 1){
                                $this->onEnd(array_shift($innocents));
                        }else{
                                $this->onEnd();
                        }
                }else{
                        $jager_player = $this->innocents[spl_object_hash($player)] ?? null;
                        if($jager_player !== null){
                                $jager_player->onDeath();
                        }

                        unset($this->innocents[spl_object_hash($player)]);

                        if($this->state === self::STATE_RUNNING && count($this->innocents) <= 0){
                                $this->onEnd($this->hunter);
                        }
                }
        }
}