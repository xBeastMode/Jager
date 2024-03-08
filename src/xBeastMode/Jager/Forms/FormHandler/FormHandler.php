<?php
namespace xBeastMode\Jager\Forms\FormHandler;
use pocketmine\player\Player;
use xBeastMode\Jager\Jager;
abstract class FormHandler{
        /** @var Jager */
        protected $plugin;

        /** @var mixed */
        protected $data; // used as extra data for forms
        protected $form_id;

        /** @var array */
        protected $vars;

        /**
         * FormHandler constructor.
         *
         * @param Jager $plugin
         * @param int   $formId
         */
        public function __construct(Jager $plugin, int $formId){
                $this->plugin = $plugin;
                $this->form_id = $formId;
        }

        /**
         * @return mixed
         */
        public function getData(){
                return $this->data;
        }

        /**
         * @param $data
         */
        public function setData($data): void{
                $this->data = $data;
        }

        /**
         * @param $index
         * @param $value
         */
        public function setVar($index, $value){
                $this->vars[$index] = $value;
        }

        /**
         * @param $index
         *
         * @return mixed|null
         */
        public function getVar($index){
                return $this->vars[$index] ?? null;
        }

        /**
         * @param Player $player
         *
         * @return mixed
         */
        abstract public function send(Player $player);

        /**
         * @param Player $player
         * @param        $formData
         *
         * @return mixed
         */
        abstract public function handleResponse(Player $player, $formData);
}