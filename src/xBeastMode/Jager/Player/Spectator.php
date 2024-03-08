<?php
namespace xBeastMode\Jager\Player;
use pocketmine\player\GameMode;
use xBeastMode\Jager\CustomItems\Compass;
class Spectator extends JagerPlayer{
        public function tick(int $tick){
        }

        public function onStart(){
                $this->onJoin();
        }

        public function onQuit(bool $server = false){
                if(!$server) $this->onEnd();
        }

        public function onEnd(){
                $this->resetPlayer();

                $this->player->setFlying(false);
                $this->player->setInvisible(false);
                $this->player->setAllowFlight(false);
                $this->player->setGamemode(GameMode::ADVENTURE());

                $position = $this->game->settings->spawn_position;
                $position = $position->getWorld()->getSafeSpawn($position);

                $this->player->teleport($position);
        }

        public function onJoin(){
                $this->resetPlayer();

                $this->player->getInventory()->addItem((new Compass())->setCount(1));
                $this->player->sendMessage("§r§l§8JOIN » §7You are now in spectator mode. You may fly. Others can not see you.");

                $this->player->setInvisible();
                $this->player->setAllowFlight(true);

                $this->player->setNameTag("");
        }

        public function onDeath(){
        }

        public function onWin(){
        }

        public function onForceClose(){
        }
}