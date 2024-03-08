<?php
namespace xBeastMode\Jager\Player;
use pocketmine\entity\animation\HurtAnimation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use xBeastMode\Jager\CustomItems\CustomItem;
use xBeastMode\Jager\CustomItems\HunterBandage;
use xBeastMode\Jager\CustomItems\OneHitKillSword;
use xBeastMode\Jager\CustomItems\Scorpion;
use xBeastMode\Jager\CustomItems\VoltEnergyDrink;
use xBeastMode\Jager\Game\Game;
class Hunter extends JagerPlayer{
        /** @var int */
        public $speed_ticks = 0;
        /** @var int */
        public $attack_cooldown_ticks = 0;
        /** @var int */
        public $hurt_ticks = 0;

        /** @var int */
        protected $item_cooldown_ticks = 0;

        public function onPhaseStart(int $phase){
                if($phase === Game::PHASE_NIGHTFALL){
                        $items = [
                            new OneHitKillSword(),
                            new Scorpion(),
                            (new HunterBandage())->setCount(2),
                            (new VoltEnergyDrink())->setCount(2),
                        ];

                        $this->player->getInventory()->addItem(...$items);
                        $this->player->sendMessage("§r§l§8» §7You are released.");

                        $this->player->setInvisible(false);
                }
        }

        public function onItemUse(Item $item): bool{
                $custom_item = CustomItem::getCustomItemFromItem($item);
                if($custom_item instanceof CustomItem){
                        if($this->item_cooldown_ticks > 20){
                                $seconds = round($this->item_cooldown_ticks / 20);
                                $this->player->sendPopup("§l§8» §cItem on cooldown for §4$seconds §cseconds");
                                return true;
                        }
                        if(parent::onItemUse($item)){
                                $this->item_cooldown_ticks = 100;
                                return true;
                        }
                }
                return false;
        }

        public function tick(int $tick){
                if($this->game->getState() === Game::STATE_WAITING) return;

                if($this->game->getPhase() === Game::PHASE_COLLECT_RESOURCES){
                        if($this->player->getPosition()->distance($this->game->settings->hunter_position) > 5){
                                $this->player->sendMessage("§r§l§8» §cPlease stay in your spawn, you will be released in the next phase.");

                                $position = $this->game->settings->hunter_position;
                                $position = $position->getWorld()->getSafeSpawn($position);

                                $this->player->teleport($position);
                        }
                        return;
                }

                --$this->item_cooldown_ticks;
                if($this->hurt_ticks > 0){
                        $this->player->setHealth($this->player->getHealth() - 1);
                        $this->player->broadcastAnimation(new HurtAnimation($this->player));
                }
                if($this->speed_ticks <= 0 && $tick % 20 === 0){
                        $this->player->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 60, 1, false));
                }

                if($this->speed_ticks > 0){
                        --$this->speed_ticks;
                        $this->player->getEffects()->remove(VanillaEffects::SLOWNESS());
                }

                if($this->attack_cooldown_ticks > 0) --$this->attack_cooldown_ticks;
                if($this->hurt_ticks > 0) --$this->hurt_ticks;
        }

        public function onStart(){
                $this->resetPlayer();

                $this->player->setFlying(false);

                $position = $this->game->settings->hunter_position;
                $position = $position->getWorld()->getSafeSpawn($position);

                $this->player->teleport($position);
                $this->player->setNameTag("");

                $this->player->setInvisible(true);
        }

        public function onQuit(bool $server = false){
                if(!$server) $this->onEnd();
        }

        public function onEnd(){
                $this->resetPlayer();

                $position = $this->game->settings->game_lobby_position;
                $position = $position->getWorld()->getSafeSpawn($position);

                $this->player->teleport($position);
        }

        public function onJoin(){
                $this->resetPlayer();

                $this->player->sendMessage("§r§l§8JOIN » §7You are the Hunter. You are strong. Use your tools wisely.");
        }

        public function onDeath(){
                $this->onEnd();
        }

        public function onWin(){
        }

        public function onForceClose(){
        }
}