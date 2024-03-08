<?php
namespace xBeastMode\Jager\Task;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
class SendTitleTask extends Task{
        /** @var Player */
        protected $player;
        /** @var string $title */
        /** @var string $subtitle */
        protected $title, $subtitle;
        /** @var int $fade_in */
        /** @var int $stay */
        /** @var int $fade_out */
        protected $fade_in, $stay, $fade_out;


        /**
         * SendTitleTask constructor.
         *
         * @param Player $player
         * @param string $title
         * @param string $subtitle
         * @param int    $fade_in
         * @param int    $stay
         * @param int    $fade_out
         */
        public function __construct(Player $player, string $title, string $subtitle = "", int $fade_in = 20, int $stay = 60, int $fade_out = 20){
                $this->player = $player;
                $this->title = $title;
                $this->subtitle = $subtitle;
                $this->fade_in = $fade_in;
                $this->stay = $stay;
                $this->fade_out = $fade_out;
        }

        public function onRun(): void{
                $this->player->sendTitle($this->title, $this->subtitle, $this->fade_in, $this->stay, $this->fade_out);
        }
}