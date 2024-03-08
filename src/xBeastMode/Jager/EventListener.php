<?php
namespace xBeastMode\Jager;
use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Sign;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\SpawnSettings;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use xBeastMode\Jager\Entity\Sword;
use xBeastMode\Jager\Forms\FormManager;
use xBeastMode\Jager\Game\Game;
use xBeastMode\Jager\Player\Hunter;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\Spectator;
use xBeastMode\Jager\Task\SendTitleTask;
class EventListener implements Listener{
        /** @var Jager */
        protected $plugin;
        /** @var int[] */
        protected $interact_cooldown = [];
        
        /** @var GameManager */
        protected $game_manager;
        /** @var FormManager */
        protected $form_manager;

        /**
         * InventoryMenuListener constructor.
         *
         * @param Jager $plugin
         */
        public function __construct(Jager $plugin){
                $this->plugin = $plugin;
                
                $this->game_manager = $plugin->game_manager;
                $this->form_manager = $plugin->form_manager;
        }

        /**
         * @param EntityRegainHealthEvent $event
         */
        public function onEntityRegainHealth(EntityRegainHealthEvent $event){
                $player = $event->getEntity();

                if($player instanceof Player && ($game = $this->game_manager->getGameByPlayer($player) instanceof Game)){
                        $event->cancel();
                }
        }

        /**
         * @param PlayerToggleSprintEvent $event
         */
        public function onPlayerToggleSprint(PlayerToggleSprintEvent $event){
                $player = $event->getPlayer();

                if(($game = $this->game_manager->getGameByPlayer($player)) instanceof Game){
                        $player_type = $game->getPlayerType($player);
                        if($event->isSprinting() && $player_type instanceof Innocent && $game->getState() === Game::STATE_RUNNING){
                                $event->cancel();
                        }
                }
        }

        /**
         * @param PlayerDropItemEvent $event
         */
        public function onPlayerDropItem(PlayerDropItemEvent $event){
                $player = $event->getPlayer();

                $game = $this->game_manager->getGameByPlayer($player);
                if($game instanceof Game){
                        $player_type = $game->getPlayerType($player);
                        if($player_type instanceof Hunter || $player_type instanceof Spectator){
                                $event->cancel();
                        }
                }
        }

        /**
         * @param EntityItemPickupEvent $event
         */
        public function onInventoryPickupItem(EntityItemPickupEvent $event){
                $inventory = $event->getInventory();
                $entity = $event->getItem();

                $player = $inventory instanceof PlayerInventory ? $inventory->getHolder() : null;

                $owner = $this->plugin->getServer()->getPlayerExact($entity instanceof ItemEntity ? $entity->getOwner() : "");
                if($player instanceof Player && $owner instanceof Player){
                        $game = $this->game_manager->getGameByPlayer($player);

                        if($game instanceof Game){
                                $player_type = $game->getPlayerType($player);

                                if($player === $owner && $player_type instanceof Hunter){
                                        $player_type->hurt_ticks = 10;
                                }else{
                                        Utils::playSound("random.door_open", $player, 1000, 1, true);
                                        Utils::playSound("random.door_open", $owner, 1000, 1, true);

                                        $player->teleport($owner->getPosition());
                                }
                        }
                }

                $entity->flagForDespawn();
                $event->cancel();
        }

        /**
         * @param PlayerDeathEvent $event
         */
        public function onPlayerDeath(PlayerDeathEvent $event){
                $event->setDeathMessage("");
                $event->setDrops([]);

                $player = $event->getPlayer();

                $game = $this->game_manager->getGameByPlayer($player);
                if($game instanceof Game){
                        $this->game_manager->onPlayerDeath($player, $game);
                }
        }

        /**
         * @param PlayerChatEvent $event
         */
        public function onPlayerChat(PlayerChatEvent $event){
                $event->setFormat("§l§f{$event->getPlayer()->getName()} §8» §7{$event->getMessage()}");
        }

        /**
         * @param PlayerQuitEvent $event
         */
        public function onPlayerQuit(PlayerQuitEvent $event){
                $player = $event->getPlayer();

                $game = $this->game_manager->getGameByPlayer($player);
                if($game instanceof Game){
                        $this->game_manager->onPlayerQuit($player, $game, true);
                }

                $event->setQuitMessage("§l§4- {$event->getPlayer()->getName()}");
        }

        /**
         * @priority HIGHEST
         *
         * @param EntityDamageEvent $event
         */
        public function onEntityDamage(EntityDamageEvent $event){
                $target = $event->getEntity();
                $final_health = $target->getHealth() - $event->getFinalDamage();

                if($target instanceof Sword){
                        $event->cancel();
                        return;
                }

                if($target instanceof Player){
                        $game = $this->game_manager->getGameByPlayer($target);
                        if(!$game instanceof Game){
                                $event->cancel();
                                return;
                        }

                        if($game->getState() === Game::STATE_WAITING){
                                $event->cancel();
                                return;
                        }

                        if($event instanceof EntityDamageByEntityEvent){
                                $killer = $event->getDamager();
                                if($killer instanceof Player){
                                        $game = $this->game_manager->getGameByPlayer($killer);
                                        if(!$game instanceof Game){
                                                $event->cancel();
                                                return;
                                        }

                                        $item = $killer->getInventory()->getItemInHand();

                                        $killer_type = $game->getPlayerType($killer);
                                        $player_type = $game->getPlayerType($target);

                                        if($player_type instanceof Spectator || $killer_type instanceof Spectator){
                                                $event->cancel();
                                                return;
                                        }

                                        if($this->game_manager->onItemAttack($killer, $target, $item, $game)){
                                                $event->cancel();
                                                return;
                                        }
                                }
                        }

                        if($final_health <= 0){
                                $this->game_manager->onPlayerDeath($target, $game);
                                $event->cancel();
                        }
                }
        }

        /**
         * @param PlayerJoinEvent $event
         */
        public function onPlayerJoin(PlayerJoinEvent $event){
                $player = $event->getPlayer();

                Utils::resetPlayer($player);
                
                $player->teleport($player->getWorld()->getSafeSpawn());
                $this->plugin->getScheduler()->scheduleDelayedTask(new SendTitleTask($player, "§l§4Jäger", "§7by xBeastMode", 20, 60, 20), 20);

                $player->setGamemode(GameMode::ADVENTURE());
                Utils::playSound("music.game.nether_wastes", $player, 1000, 1, true);

                $event->setJoinMessage("§l§a+ {$event->getPlayer()->getName()}");
        }

        /**
         * @param BlockBreakEvent $event
         */
        public function onBlockBreak(BlockBreakEvent $event){
                $block = $event->getBlock();

                $sign = $block->getPosition()->getWorld()->getTile($block->getPosition());
                if($sign instanceof Sign && ($game = $this->game_manager->getGameBySign($sign)) instanceof Game){
                        $event->cancel();
                }
        }

        /**
         * @param PlayerInteractEvent $event
         */
        public function onPlayerInteract(PlayerInteractEvent $event){
                $player = $event->getPlayer();
                $block = $event->getBlock();

                $item = $event->getItem();
                $actions = [PlayerInteractEvent::RIGHT_CLICK_BLOCK];

                $tile = $block->getPosition()->getWorld()->getTile($block->getPosition());
                if($tile instanceof Chest && ($game = $this->game_manager->getGameByPlayer($player)) instanceof Game){
                        $player_type = $game->getPlayerType($player);
                        if($player_type instanceof Spectator || $player_type instanceof Hunter){
                                $event->cancel();
                                return;
                        }
                        if($this->game_manager->onChestUse($player, $tile, $game)){
                                $event->cancel();
                                return;
                        }
                }

                if(in_array($event->getAction(), $actions) && ($game = $this->game_manager->getGameByPlayer($player)) instanceof Game){
                        if($this->game_manager->onItemUse($player, $item, $game)){
                                $event->cancel();
                                return;
                        }
                }

                if($tile instanceof Sign){
                        if(!isset($this->interact_cooldown[spl_object_hash($player)])){
                                $this->interact_cooldown[spl_object_hash($player)] = time();
                        }

                        if(!(($this->interact_cooldown[spl_object_hash($player)] - time()) <= 0)){
                                $this->interact_cooldown[spl_object_hash($player)] = time() + 1;
                                return;
                        }

                        $game = $this->game_manager->getGameByPlayer($player);
                        if($game instanceof Game){
                                $form = $this->form_manager->getFastSimpleForm($player, function (Player $_, int $response) use (&$player, &$game){
                                        if($response === 0){
                                                $this->game_manager->onPlayerQuit($player, $game);
                                        }
                                });

                                $form->setContent("§l§8» §7players in game: §f" . count($game->getAllPlayers()));

                                $form->setTitle("§l§8" . strtoupper($game->settings->name));
                                $form->setButton("§l§8LEAVE");

                                $form->send($player);
                                return;
                        }

                        $game = $this->game_manager->getGameBySign($tile);
                        if($game instanceof Game && $game->getState() === Game::STATE_WAITING){
                                $form = $this->form_manager->getFastSimpleForm($player, function (Player $_, int $response) use (&$player, &$game){
                                        if($response === 0){
                                                $this->game_manager->onPlayerJoin($player, $game);
                                        }else{
                                                $this->game_manager->onSpectatorJoin($player, $game);
                                        }
                                });

                                $form->setContent("§l§8» §7players in game: §f" . count($game->getAllPlayers()));

                                $form->setTitle("§l§8" . strtoupper($game->settings->name));

                                $form->setButton("§l§8PLAY");
                                $form->setButton("§l§8SPECTATE");

                                $form->send($player);
                        }

                        if($game instanceof Game && $game->getState() === Game::STATE_RUNNING){
                                $form = $this->form_manager->getFastSimpleForm($player, function (Player $_, int $response) use (&$player, &$game){
                                        if($response === 0){
                                                $this->game_manager->onSpectatorJoin($player, $game);
                                        }
                                });

                                $form->setContent("§l§8» §7players in game: §f" . count($game->getAllPlayers()));
                                $form->setTitle("§l§8" . strtoupper($game->settings->name));

                                $form->setButton("§l§8SPECTATE");
                                $form->send($player);
                        }
                }
        }
}