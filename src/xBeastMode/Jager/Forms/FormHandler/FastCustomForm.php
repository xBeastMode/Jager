<?php
namespace xBeastMode\Jager\Forms\FormHandler;
use Closure;
use pocketmine\player\Player;
use xBeastMode\Jager\UIForms\CustomForm;
class FastCustomForm extends FormHandler{
        public function send(Player $player){
                $this->setData(new CustomForm($this));
        }
        public function handleResponse(Player $player, $formData){
                /** @var Closure $callback */
                $callback = $this->getData();

                if($callback instanceof Closure){
                        $callback($player, $formData);
                }
        }
}