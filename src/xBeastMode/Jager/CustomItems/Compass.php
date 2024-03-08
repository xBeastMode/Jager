<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use xBeastMode\Jager\Jager;
use xBeastMode\Jager\Player\Innocent;
use xBeastMode\Jager\Player\JagerPlayer;
use xBeastMode\Jager\Player\Spectator;
class Compass extends CustomItem{
        public function __construct(){
                parent::__construct(ItemIds::COMPASS, 0, "Innocent Tracker");
                $this->setCustomName("§r§l§8» §gINNOCENT TRACKER\n§r§7right click to use");
        }

        public function onUse(JagerPlayer $jager_player): bool{
                if($jager_player instanceof Spectator){
                        $players = $jager_player->getGame()->getInnocentsAsRegularPlayers();
                        if(count($players) <= 0){
                                $jager_player->getPlayer()->sendMessage("§r§l§8» §cNo innocents found for this game.");
                                return true;
                        }

                        $form = Jager::$instance->form_manager->getFastSimpleForm($jager_player->getPlayer(), function (Player $player, int $response) use (&$players, &$jager_player){
                                /** @var Player[] $players */
                                $players = array_values($players);
                                $player_chosen = $players[$response];

                                $player_type = $jager_player->getGame()->getPlayerType($player_chosen);
                                if(!$player_type instanceof Innocent){
                                        $player->sendMessage("§r§l§8» §4{$player_chosen->getName()} §cis no longer an innocent.");
                                }else{
                                        $player->sendMessage("§r§l§8» §aTeleported you to §2{$player_chosen->getName()}§a.");
                                        $jager_player->getPlayer()->teleport($player_chosen->getPosition());
                                }
                        });

                        $form->setTitle("§l§8INNOCENT TRACKER");
                        foreach($players as $player){
                                $distance = round($jager_player->getPlayer()->getPosition()->distance($player->getPosition()));
                                $form->setButton("§8{$player->getName()}\n§7- §8{$distance} block(s) away §7-");
                        }

                        $form->send($jager_player->getPlayer());
                        return true;
                }
                return false;
        }
}