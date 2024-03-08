<?php

namespace xBeastMode\Jager\UIForms;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use xBeastMode\Jager\Forms\FormHandler\FormHandler;
class SimpleForm implements Form{

        /** @var string */
        public static $cache = [];

        /** @var array */
        protected $formData = [];

        /** @var FormHandler */
        protected $callable;

        /**
         * CustomForm constructor.
         *
         * @param FormHandler $callable
         */
        public function __construct(FormHandler $callable){
                $this->formData["type"] = "form";
                $this->formData["content"] = "";

                $this->callable = $callable;
        }

        /**
         *
         * @param array $formData
         *
         */
        public function setFormData(array $formData): void{
                $this->formData = $formData;
        }

        /**
         *
         * @return array
         *
         */
        public function getFormData(): array{
                return $this->formData;
        }

        /**
         *
         * @return string
         *
         */
        public function getEncodedFormData(): string{
                return json_encode($this->formData);
        }

        /**
         *
         * @param Player $player
         *
         */
        public function send(Player $player) {
                $player->sendForm($this);
        }

        /**
         *
         * @param string $title
         *
         */
        public function setTitle(string $title) {
                $this->formData["title"] = $title;
        }

        /**
         *
         * @param string $text
         *
         */
        public function setContent(string $text) {
                $this->formData["content"] = $text;
        }

        /**
         * @param string      $button
         * @param string|null $imageURL
         */
        public function setButton(string $button, string $imageURL = null) {
                $content = ['text' => $button];

                if($imageURL !== null){
                        $content['image']['type'] = 'url';
                        $content['image']['data'] = $imageURL;
                }

                $this->formData['buttons'][] = $content;
        }

        /**
         * Handles a form response from a player.
         *
         * @param Player $player
         * @param mixed  $data
         *
         * @throws FormValidationException if the data could not be processed
         */
        public function handleResponse(Player $player, $data): void{
                if($data !== null){
                        $this->callable->handleResponse($player, $data);
                }
        }

        /**
         * Specify data which should be serialized to JSON
         * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
         * @return array data which can be serialized by <b>json_encode</b>,
         * which is a value of any type other than a resource.
         * @since 5.4.0
         */
        public function jsonSerialize(){
                return $this->formData;
        }
}