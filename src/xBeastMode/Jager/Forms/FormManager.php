<?php
namespace xBeastMode\Jager\Forms;
use Closure;
use pocketmine\player\Player;
use xBeastMode\Jager\Forms\FormHandler\FastCustomForm;
use xBeastMode\Jager\Forms\FormHandler\FastSimpleForm;
use xBeastMode\Jager\Forms\FormHandler\FormHandler;
use xBeastMode\Jager\Jager;
use xBeastMode\Jager\UIForms\CustomForm;
use xBeastMode\Jager\UIForms\SimpleForm;
class FormManager{
        /** @var Jager */
        protected $plugin;

        /** @var string[] */
        protected $form_handlers = [];

        /** @var int */
        protected static $form_count = 0;

        /**
         * FormManager constructor.
         *
         * @param Jager $plugin
         */
        public function __construct(Jager $plugin){
                $this->plugin = $plugin;
        }

        /**
         * @return int
         */
        public static function getNextFormId(): int{
                return ++self::$form_count;
        }

        /**
         * @param int $id
         *
         * @return bool
         */
        public function handlerExists(int $id): bool{
                return isset($this->form_handlers[$id]);
        }

        /**
         * @param int    $id
         * @param string $class
         * @param bool   $force
         *
         * @return bool
         */
        public function registerHandler(int $id, string $class, bool $force = false): bool{
                if(!$this->handlerExists($id) || $force){
                        $this->form_handlers[$id] = $class;
                        return true;
                }
                return false;
        }

        /**
         * @param int $id
         *
         * @return bool
         */
        public function unregisterHandler(int $id): bool{
                if($this->handlerExists($id)){
                        unset($this->form_handlers[$id]);
                        return true;
                }
                return false;
        }

        /**
         * @param int $id
         *
         * @return null|string
         */
        public function getHandlerClass(int $id): ?string {
                return $this->handlerExists($id) ? $this->form_handlers[$id] : null;
        }

        /**
         * @param int $id
         *
         * @return null|FormHandler
         */
        public function getHandler(int $id): ?FormHandler {
                return $this->handlerExists($id) ? new $this->form_handlers[$id]($this->plugin, $id) : null;
        }

        /**
         * @param string $class
         *
         * @return string[]
         */
        public function filterFormHandlers(string $class): array{
                return array_filter($this->form_handlers, function (string $value) use ($class){ return stripos($value, $class) !== false; });
        }

        /**
         * @param string $class
         *
         * @return string[]
         */
        public function filterFormHandlerIds(string $class): array{
                return array_keys($this->filterFormHandlers($class));
        }

        /**
         * @param int        $id
         * @param            $players
         * @param null       $extraData
         *
         * @return FormHandler
         */
        public function sendForm(int $id, $players, $extraData = null): ?FormHandler{
                $handler = $this->getHandler($id);

                if(!is_array($players)) $players = [$players];

                if($handler !== null){
                        foreach($players as $player){
                                /** @var Player $player */
                                $handler->setData($extraData);
                                $handler->send($player);
                        }
                        return $handler;
                }
                return null;
        }

        /**
         * @param Player   $player
         * @param Closure $callback
         *
         * @return CustomForm
         */
        public function getFastCustomForm(Player $player, Closure $callback): CustomForm{
                $handler = new FastCustomForm($this->plugin, self::getNextFormId());
                $handler->send($player);

                /** @var CustomForm $form */
                $form = $handler->getData();
                $handler->setData($callback);

                return $form;
        }

        /**
         * @param Player   $player
         * @param Closure $callback
         *
         * @return SimpleForm
         */
        public function getFastSimpleForm(Player $player, Closure $callback): SimpleForm{
                $handler = new FastSimpleForm($this->plugin, self::getNextFormId());
                $handler->send($player);

                /** @var SimpleForm $form */
                $form = $handler->getData();
                $handler->setData($callback);

                return $form;
        }
}