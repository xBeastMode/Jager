<?php
namespace xBeastMode\Jager\Game;
use pocketmine\block\tile\Chest;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\world\particle\CriticalParticle;
use xBeastMode\Jager\CustomItems\Bandage;
use xBeastMode\Jager\CustomItems\Boots;
use xBeastMode\Jager\CustomItems\Coat;
use xBeastMode\Jager\CustomItems\Compass;
use xBeastMode\Jager\CustomItems\CustomItem;
use xBeastMode\Jager\CustomItems\Flashlight;
use xBeastMode\Jager\CustomItems\HunterBandage;
use xBeastMode\Jager\CustomItems\LethalSword;
use xBeastMode\Jager\CustomItems\OneHitKillSword;
use xBeastMode\Jager\CustomItems\Scorpion;
use xBeastMode\Jager\CustomItems\StrongAntiBioticPill;
use xBeastMode\Jager\CustomItems\StrongRubbingAlcohol;
use xBeastMode\Jager\CustomItems\UsedBandage;
use xBeastMode\Jager\CustomItems\UsedFlashlight;
use xBeastMode\Jager\CustomItems\VoltEnergyDrink;
use xBeastMode\Jager\CustomItems\WeakAntiBioticPill;
use xBeastMode\Jager\CustomItems\WeakRubbingAlcohol;
use xBeastMode\Jager\Entity\Sword;
use xBeastMode\Jager\InventoryMenu\TransactionData;
use xBeastMode\Jager\Jager;
use xBeastMode\Jager\Player\Hunter;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\JagerPlayer;
use xBeastMode\Jager\Player\Spectator;
use xBeastMode\Jager\Utils;
class JagerGame extends Game{
        /** @var int */
        protected $radians;
        /** @var int */
        protected $add;
        /** @var int */
        protected $y;

        public function __construct(Jager $plugin, GameSettings $settings){
                parent::__construct($plugin, $settings);

                CustomItem::registerCustomItem(new Bandage());
                CustomItem::registerCustomItem(new Boots());
                CustomItem::registerCustomItem(new Coat());
                CustomItem::registerCustomItem(new Compass());
                CustomItem::registerCustomItem(new Flashlight());
                CustomItem::registerCustomItem(new HunterBandage());
                CustomItem::registerCustomItem(new LethalSword());
                CustomItem::registerCustomItem(new OneHitKillSword());
                CustomItem::registerCustomItem(new Scorpion());
                CustomItem::registerCustomItem(new StrongAntiBioticPill());
                CustomItem::registerCustomItem(new StrongRubbingAlcohol());
                CustomItem::registerCustomItem(new UsedBandage());
                CustomItem::registerCustomItem(new UsedFlashlight());
                CustomItem::registerCustomItem(new VoltEnergyDrink());
                CustomItem::registerCustomItem(new WeakAntiBioticPill());
                CustomItem::registerCustomItem(new WeakRubbingAlcohol());
        }

        public function onPhaseStart(int $phase){
                parent::onPhaseStart($phase);

                $phases = [
                    self::PHASE_COLLECT_RESOURCES => ["§l§k§c|§r§l§4PHASE 1§l§k§c|", "§l§7collect resources.", "§r§l§8» §gCollect resources before the hunter arrives."],
                    self::PHASE_NIGHTFALL => ["§l§k§c|§r§l§4PHASE 2§l§k§c|", "§l§7hunter has arrived, hide.", "§r§l§8» §cHunter has arrived, you must hide."],
                    self::PHASE_INFECTION => ["§l§k§c|§r§l§4PHASE 3§l§k§c|", "§l§7final phase, infection is here.", "§r§l§8» §cInfection is here."]
                ];

                $phase_messages = $phases[$phase];

                $this->broadcastTitle($phase_messages[0], $phase_messages[1]);
                $this->broadcastMessage($phase_messages[2]);

                $game_times = [
                    self::PHASE_COLLECT_RESOURCES => 90,
                    self::PHASE_NIGHTFALL => 300,
                    self::PHASE_INFECTION => 300
                ];

                $this->setGameTime($game_times[$phase]);
                Utils::playSound("ambient.cave", $this->hunter->getPlayer(), 1000, 1);
        }

        public function onStart(){
                parent::onStart();

                Utils::stopSound($this->hunter->getPlayer());

                $this->settings->innocents_position->getWorld()->setTime(18000); //midnight
                $this->settings->innocents_position->getWorld()->stopTime();

                $this->summonSword();
        }

        /**
         * @param JagerPlayer|null $winner
         */
        public function onEnd(JagerPlayer $winner = null){
                if($winner !== null){
                        if($winner instanceof Innocent){
                                $this->plugin->getServer()->broadcastMessage("§r§l§8{$this->settings->name} » §aGame ended. Innocent §2{$winner->getPlayer()->getName()} §awon.");
                        }elseif($winner instanceof Hunter){
                                $this->plugin->getServer()->broadcastMessage("§r§l§8{$this->settings->name} » §aGame ended. Hunter §2{$winner->getPlayer()->getName()} §awon.");
                        }
                }else{
                        if(count($this->getInnocents()) > 0){
                                $this->plugin->getServer()->broadcastMessage("§r§l§8{$this->settings->name} » §aGame ended. Innocents won.");
                        }else{
                                $this->plugin->getServer()->broadcastMessage("§r§l§8{$this->settings->name} » §aGame ended.");
                        }
                }

                if(!$this->sword->isClosed()){
                        $this->sword->flagForDespawn();
                        $this->sword = null;
                }

                $this->chests_used = [];
                $this->settings->innocents_position->getWorld()->startTime();

                Utils::playSound("music.game.nether_wastes", $this->settings->game_lobby_position, 1000, 1, false);
                parent::onEnd($winner);
        }

        public function onPlayerDeath(Player $player){
                if(!($this->getPlayerType($player) instanceof Spectator)){
                        $this->broadcastMessage("§r§l§8{$this->settings->name} » §4{$player->getName()} §cDIED");
                        Utils::playSound("mob.enderdragon.hit", $this->settings->innocents_position, 1000, 1, false);
                }
                parent::onPlayerDeath($player);
        }

        public function onPlayerQuit(Player $player, bool $server = false){
                parent::onPlayerQuit($player, $server);
                if($this->state === self::STATE_RUNNING){
                        $this->sword->spectators = $this->getSpectatorsAsRegularPlayers();
                }
        }

        public function onSpectatorJoin(Player $player){
                parent::onSpectatorJoin($player);
                if($this->state === self::STATE_RUNNING){
                        $this->sword->spectators = $this->getSpectatorsAsRegularPlayers();
                }
        }

        /** @var Sword */
        protected $sword;

        protected function summonSword(){
                $position = Utils::getRandomPositionNearPosition($this->settings->innocents_position, 30);


                $position = $this->settings->innocents_position->getWorld()->getSafeSpawn($position);
                $position->y -= 1.35;

                $sword = new Sword(Utils::positionToLocation($position), null);

                if($sword instanceof Sword){
                        $sword->getNetworkProperties()->setInt(EntityMetadataProperties::ARMOR_STAND_POSE_INDEX, 8);
                        $sword->setInvisible(true);

                        $sword->spawnToAll();
                        $this->sword = $sword;

                        $this->sword->teleport($this->settings->innocents_position);

                        $this->sword->hunter = $this->hunter->getPlayer();
                        $this->sword->spectators = $this->getSpectatorsAsRegularPlayers();
                }
        }

        /**
         * @param int $tick
         */
        public function tick(int $tick){
                parent::tick($tick);

                if($this->state === self::STATE_RUNNING){
                        if($tick % 20 === 0){
                                if($this->phase < self::PHASE_INFECTION){
                                        $this->broadcastTip("§r§l§8» §8{$this->game_time} §7SECONDS UNTIL NEXT PHASE");
                                }else{
                                        $this->broadcastTip("§r§l§8» §8{$this->game_time} §7SECONDS UNTIL GAME IS OVER");
                                }
                        }

                        if($tick % 5 === 0 && mt_rand(1, 100) === 1){
                                $sounds = [
                                    "mob.wolf.growl",
                                    "mob.wolf.bark",
                                    "mob.zombie.say",
                                    "mob.endermen.scream",
                                    "ambient.cave"
                                ];
                                Utils::playSound($sounds[array_rand($sounds)], $this->settings->innocents_position, 1000, 1, false);
                        }
                }

                if($this->state === self::STATE_WAITING){
                        if($this->start_delay < $this->settings->start_delay && $this->start_delay > 0 && $tick % 20 === 0){
                                $this->broadcastTip("§r§l§8» §7STARTING IN §8{$this->start_delay} §7SECONDS");
                        }

                        if($this->y >= 4){
                                $this->add = -0.1;
                        }elseif($this->y <= 0){
                                $this->add = 0.1;
                        }

                        $this->y += $this->add;

                        $this->radians += 0.05;
                        if($this->radians >= 2){
                                $this->radians = 0;
                        }

                        $x = cos(M_PI * $this->radians) * 2;
                        $z = sin(M_PI * $this->radians) * 2;

                        $position = $this->settings->sign_position;
                        $position->getWorld()->addParticle(new Vector3($x, $this->y, $z + 0.5), new CriticalParticle());
                }
        }

        public function onForceClose(){
                if($this->sword !== null && !$this->sword->isClosed()) $this->sword->flagForDespawn();
        }

        /** @var Chest[] */
        protected $chests_used = [];

        public function onItemAttack(Player $killer, Player $target, Item $item): bool{
                return $this->phase === self::PHASE_COLLECT_RESOURCES || parent::onItemAttack($killer, $target, $item);
        }

        /**
         * @param Player $player
         * @param Chest  $chest
         *
         * @return bool
         */
        public function onChestUse(Player $player, Chest $chest): bool{
                if($this->state === self::STATE_WAITING) return false;

                if($this->phase > self::PHASE_COLLECT_RESOURCES){
                        $player->sendMessage("§r§l§8» §cResource collection phase has ended");
                        return true;
                }

                if(isset($this->chests_used[Utils::vectorToString($chest->getPosition())])){
                        $player->sendMessage("§r§l§8» §cChest already looted");
                        return true;
                }
                $chest_inventory = $this->plugin->inventory_menu->openInventory($player, function (TransactionData $data){
                        return false;
                }, [
                    "title" => "§l§8" . strtoupper($this->settings->name) . " LOOT",
                    "vector" => $chest->getPosition()->asVector3()
                ]);
                if($this->isHunter($player)){
                        $items = [CustomItem::getRandomHunterCustomItem(), CustomItem::getRandomHunterCustomItem()];
                }else{
                        $items = [CustomItem::getRandomInnocentCustomItem(), CustomItem::getRandomInnocentCustomItem()];
                }
                for($i = 0; $i < 2; $i++){
                        $chest_inventory->setItem(mt_rand(0, $chest_inventory->getSize() - 1), array_shift($items));
                }
                $this->chests_used[Utils::vectorToString($chest->getPosition())] = $chest;
                return true;
        }
}