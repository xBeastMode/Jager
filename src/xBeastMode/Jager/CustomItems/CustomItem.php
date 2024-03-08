<?php
namespace xBeastMode\Jager\CustomItems;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use xBeastMode\Jager\Player\JagerPlayer;
abstract class CustomItem extends Item{
        const CUSTOM_ITEM_CLASS_TAG = "__custom_item_class__";

        public function __construct(int $id, int $meta = 0, string $name = "Unknown"){
                parent::__construct(new ItemIdentifier($id, $meta), $name);

                $this->getNamedTag()->setString(self::CUSTOM_ITEM_CLASS_TAG, static::class);
        }

        /** @var CustomItem[] */
        protected static $items = [];

        /**
         * @param CustomItem $item
         */
        public static function registerCustomItem(CustomItem $item){
                self::$items[get_class($item)] = $item;
        }

        /**
         * @param string $class
         *
         * @return null|CustomItem
         */
        public static function getCustomItem(string $class): ?CustomItem{
                $item = self::$items[$class] ?? null;
                if($item instanceof CustomItem){
                        return clone $item;
                }
                return null;
        }

        /**
         * @param Item $item
         *
         * @return bool
         */
        public static function isCustomItem(Item $item): bool{
                return $item->getNamedTag()->getTag(self::CUSTOM_ITEM_CLASS_TAG) !== null;
        }

        /**
         * @param Item $item
         *
         * @return null|CustomItem
         */
        public static function getCustomItemFromItem(Item $item): ?CustomItem{
                if(self::isCustomItem($item)){
                        $class = $item->getNamedTag()->getString(self::CUSTOM_ITEM_CLASS_TAG);
                        return self::getCustomItem($class);
                }
                return null;
        }

        /**
         * @return array
         */
        public static function getCustomItems(): array{
                return array_values(self::$items);
        }

        /**
         * @return CustomItem[]
         */
        public static function getHunterCustomItems(): array{
                return array_filter(self::$items, function (CustomItem $item){
                        return (
                            $item instanceof OneHitKillSword
                            || $item instanceof Scorpion
                            || $item instanceof HunterBandage
                            || $item instanceof VoltEnergyDrink
                        );
                });
        }

        /**
         * @return CustomItem[]
         */
        public static function getInnocentCustomItems(): array{
                return array_filter(self::$items, function (CustomItem $item){
                        return !(
                            $item instanceof OneHitKillSword
                            || $item instanceof Scorpion
                            || $item instanceof HunterBandage
                            || $item instanceof VoltEnergyDrink
                            || $item instanceof LethalSword
                            || $item instanceof Compass
                        );
                });
        }

        /**
         * @return CustomItem
         */
        public static function getRandomCustomItem(): CustomItem{
                return self::$items[array_rand(self::$items)];
        }

        /**
         * @return CustomItem
         */
        public static function getRandomInnocentCustomItem(): CustomItem{
                $items = self::getInnocentCustomItems();
                return $items[array_rand($items)];
        }

        /**
         * @return CustomItem
         */
        public static function getRandomHunterCustomItem(): CustomItem{
                $items = self::getHunterCustomItems();
                return $items[array_rand($items)];
        }

        /**
         * @param JagerPlayer $player
         * @param Item        $item
         * @return bool
         */
        public static function onItemUse(JagerPlayer $player, Item $item): bool{
                if(self::isCustomItem($item)){
                        $class = $item->getNamedTag()->getString(self::CUSTOM_ITEM_CLASS_TAG);
                        $custom_item = self::getCustomItem($class);

                        if($custom_item instanceof CustomItem){
                                return $custom_item->onUse($player);
                        }
                }
                return false;
        }

        /**
         * @param JagerPlayer $killer
         * @param JagerPlayer $target
         * @param Item        $item
         *
         * @return bool
         */
        public static function onItemAttack(JagerPlayer $killer, JagerPlayer $target, Item $item): bool{
                if(self::isCustomItem($item)){
                        $class = $item->getNamedTag()->getString(self::CUSTOM_ITEM_CLASS_TAG);
                        $custom_item = self::getCustomItem($class);

                        if($custom_item instanceof CustomItem){
                                return $custom_item->onAttackPlayer($killer, $target);
                        }
                }
                return false;
        }

        /**
         * @param JagerPlayer $killer
         * @param JagerPlayer $player
         *
         * @return bool
         */
        public function onAttackPlayer(JagerPlayer $killer, JagerPlayer $player): bool{
                return false;
        }

        abstract public function onUse(JagerPlayer $player): bool;
}