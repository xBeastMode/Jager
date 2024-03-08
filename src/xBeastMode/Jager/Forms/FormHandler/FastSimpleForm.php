<?php
namespace xBeastMode\Jager\Forms\FormHandler;
use pocketmine\player\Player;
use xBeastMode\Jager\UIForms\SimpleForm;
class FastSimpleForm extends FormHandler{
        public function send(Player $player){
                $this->setData(new SimpleForm($this));
        }
        public function handleResponse(Player $player, $formData){
                /** @var \Closure $callback */
                $callback = $this->getData();

                if($callback instanceof \Closure){
                        $callback($player, $formData);
                }
        }
}