<?php
namespace xBeastMode\Jager\Player;
use pocketmine\block\Leaves;
use pocketmine\block\Water;
use pocketmine\entity\animation\HurtAnimation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use xBeastMode\Jager\CustomItems\Boots;
use xBeastMode\Jager\CustomItems\Coat;
use xBeastMode\Jager\CustomItems\CustomItem;
use xBeastMode\Jager\CustomItems\Flashlight;
use xBeastMode\Jager\CustomItems\StrongAntiBioticPill;
use xBeastMode\Jager\Game\Game;
use xBeastMode\Jager\Utils;
class Innocent extends JagerPlayer{
        /** @var bool */
        public $infected = false;
        /** @var int */
        public $infection_severity = 0;
        /** @var int */
        public $infection_interval = 40;
        /** @var int */
        public $anti_biotic_ticks = 0;
        /** @var int */
        public $alcohol_ticks = 0;
        /** @var int */
        public $vision_ticks = 0;
        /** @var int */
        public $hurt_ticks = 0;
        /** @var int */
        public $attack_cooldown_ticks = 0;

        /** @var int */
        protected $item_cooldown_ticks = 0;

        /** @var int */
        protected $in_water_ticks = 0;
        /** @var int */
        protected $game_running_ticks = 0;

        protected function isInWater(): bool{
                return $this->player->getWorld()->getBlock($this->player->getPosition()) instanceof Water || $this->player->isUnderwater();
        }

        protected function onTree(): bool{
                for($x = -1; $x < 1; $x++){
                        for($y = -1; $y < 1; $y++){
                                for($z = -1; $z < 1; $z++){
                                        $vector = $this->player->getPosition()->add($x, $y, $z);
                                        $block = $this->player->getWorld()->getBlock($vector);

                                        if($block instanceof Leaves) return true;
                                }
                        }
                }
                return false;
        }

        public function onItemUse(Item $item): bool{
                $custom_item = CustomItem::getCustomItemFromItem($item);
                if($custom_item instanceof CustomItem){
                        if($this->game->getPhase() < Game::PHASE_INFECTION && !($custom_item instanceof Flashlight)){
                                $this->player->sendPopup("§l§8» §cWait until infection phase.");
                                return true;
                        }
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

        /**
         * @param int $tick
         */
        public function tick(int $tick){
                if($this->game->getState() === Game::STATE_WAITING) return;

                ++$this->game_running_ticks;
                if((($in_water = $this->isInWater()) || $this->onTree()) && $this->game_running_ticks <= 20){
                        $this->onStart();
                        return;
                }

                if($this->game->getPhase() < Game::PHASE_NIGHTFALL) return;
                if($this->attack_cooldown_ticks > 0) --$this->attack_cooldown_ticks;

                if($this->vision_ticks <= 0 && $tick % 20 === 0){
                        $this->player->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 60, 0, false));
                }

                if($this->vision_ticks > 0){
                        --$this->vision_ticks;
                        $this->player->getEffects()->remove(VanillaEffects::BLINDNESS());
                }

                --$this->item_cooldown_ticks;
                if($this->game->getPhase() < Game::PHASE_INFECTION) return;

                if(($tick % 20 === 0) && (Utils::randomFloat(0, 100) <= 1) && ($this->infected === false)){
                        if(!CustomItem::isCustomItem($this->player->getArmorInventory()->getChestplate())){
                                $message = [
                                    "§r§l§8» §7It's too cold here, you've caught a cold! Find an anti-biotic pill quick!",
                                    "§r§l§8» §7You've caught a bacteria! Find alcohol or an anti-biotic pill quick!",
                                ];

                                $this->player->sendMessage($message[array_rand($message)]);

                                $this->infected = true;
                                $this->infection_severity = 1;
                                $this->infection_interval = 100;
                        }
                }

                if(($tick % 20 === 0) && (Utils::randomFloat(0, 100) <= 1) && ($this->infected === false)){
                        if(!CustomItem::isCustomItem($this->player->getArmorInventory()->getBoots())){
                                $message = [
                                    "§r§l§8» §7Oh no! You stepped on a piece of glass. Find a bandage and anti-biotic pills quick!"
                                ];

                                $this->player->sendMessage($message[array_rand($message)]);

                                $this->infected = true;
                                $this->infection_severity = 1;
                                $this->infection_interval = 100;
                        }
                }

                if($in_water){
                        ++$this->in_water_ticks;
                        if($this->in_water_ticks >= 100 && !$this->infected){
                                $this->infected = true;
                                $this->infection_severity = 1;
                                $this->infection_interval = 20;

                                $this->player->sendMessage("§l§8» §cOh no! You got severe hypothermia! Find anti-biotic pills before you die!");
                        }else{
                                $this->hurt_ticks += ($tick % 20 === 0 ? 1 : 0);
                                $this->player->sendTip("§l§cThe water is too cold! Get out! You'll get hypothermia!");
                        }
                }

                if($this->hurt_ticks > 0){
                        $this->player->setHealth($this->player->getHealth() - 1);
                        $this->player->broadcastAnimation(new HurtAnimation($this->player));
                }
                if($this->infected && ($tick % $this->infection_interval) === 0 && ($this->anti_biotic_ticks <= 0 || $this->alcohol_ticks <= 0)){
                        $this->player->setHealth($this->player->getHealth() - $this->infection_severity);
                        $this->player->broadcastAnimation(new HurtAnimation($this->player));
                }

                if($this->anti_biotic_ticks > 0) --$this->anti_biotic_ticks;
                if($this->alcohol_ticks > 0) --$this->alcohol_ticks;
                if($this->hurt_ticks > 0) --$this->hurt_ticks;
        }

        public function onStart(){
                $this->resetPlayer();

                $position = $this->game->settings->innocents_position;
                $position = $position->getWorld()->getSafeSpawn(Utils::getRandomPositionNearPosition($position, 30));

                $this->player->setFlying(false);

                $this->player->teleport($position);
                $this->player->setNameTag("");

                $this->player->getInventory()->addItem(...[new Boots(), new Coat(), (new StrongAntiBioticPill())->setCount(64), new Flashlight()]);
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

                $this->player->sendMessage("§r§l§8JOIN » §7You are an innocent. If you find light, there may be something useful nearby.");
        }

        public function onDeath(){
                $this->onEnd();
                $this->plugin->game_manager->onSpectatorJoin($this->player, $this->game);
        }

        public function onWin(){
        }

        public function onForceClose(){
        }
}