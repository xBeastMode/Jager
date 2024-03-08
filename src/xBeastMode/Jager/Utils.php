<?php
namespace xBeastMode\Jager;
use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;
use PrestigeSociety\Core\PrestigeSocietyCore;

class Utils{
        /**
         * @param Position $position
         *
         * @return string
         */
        public static function positionToString(Position $position): string{
                return $position->x . ":" . $position->y . ":" . $position->z . ":" . $position->getWorld()->getDisplayName();
        }

        /**
         * @param Vector3 $vector
         *
         * @return string
         */
        public static function vectorToString(Vector3 $vector): string{
                return $vector->x . ":" . $vector->y . ":" . $vector->z;
        }

        /**
         * @param string $data
         *
         * @return null|Position
         */
        public static function parsePosition(string $data): ?Position{
                list($x, $y, $z, $level) = explode(":", $data);
                $server = Jager::$instance->getServer();
                if(!$server->getWorldManager()->isWorldLoaded($level)){
                        $server->getWorldManager()->loadWorld($level);
                }
                $level = $server->getWorldManager()->getWorldByName($level);
                return $level === null ? null : new Position((int) $x, (int) $y, (int) $z, $level);
        }

        /**
         * @param Position $center
         * @param int      $range
         *
         * @return Position
         */
        public static function getRandomPositionNearPosition(Position $center, int $range): Position{
                $center->add(mt_rand(-$range, $range), 0, mt_rand(-$range, $range));
                return $center;
        }

        /**
         * @param Player $player
         */
        public static function resetPlayer(Player $player){
                $player->setAllowFlight(false);
                $player->setNameTag($player->getName());

                $player->setHealth($player->getMaxHealth());
                $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());

                $player->extinguish();
                $player->getEffects()->clear();

                $player->getArmorInventory()->clearAll();
                $player->getInventory()->clearAll();
        }

        /**
         * @param string $soundName
         * @param        $to
         * @param int    $volume
         * @param float  $pitch
         * @param bool   $single
         */
        public static function playSound(string $soundName, $to, int $volume = 500, float $pitch = 1, bool $single = false){
                if(!($to instanceof Player) && $to instanceof Position){
                        $pk = new PlaySoundPacket;
                        $pk->soundName = $soundName;
                        $pk->x = $to->x;
                        $pk->y = $to->y;
                        $pk->z = $to->z;
                        $pk->volume = $volume;
                        $pk->pitch = $pitch;

                        $to->getWorld()->broadcastPacketToViewers($to, $pk);
                }elseif($to instanceof Player){
                        $pk = new PlaySoundPacket;
                        $pk->soundName = $soundName;
                        $pk->x = $to->getLocation()->x;
                        $pk->y = $to->getLocation()->y;
                        $pk->z = $to->getLocation()->z;
                        $pk->volume = $volume;
                        $pk->pitch = $pitch;

                        if($single){
                                $to->getNetworkSession()->sendDataPacket($pk);
                        }else{
                                $to->getWorld()->broadcastPacketToViewers($to->getLocation(), $pk);
                        }
                }
        }

        /**
         * @param Player $to
         * @param string $soundName
         * @param bool   $single
         */
        public static function stopSound(Player $to, string $soundName = "", bool $single = false){
                $packet = new StopSoundPacket();
                $packet->soundName = $soundName;

                if($soundName === "") $packet->stopAll = true;
                if($single) $to->getNetworkSession()->sendDataPacket($packet); else $to->getWorld()->broadcastPacketToViewers($to->getPosition()->asVector3(), $packet);
        }

        /**
         * @param float $min
         * @param float $max
         *
         * @return float
         */
        public static function randomFloat (float $min, float $max): float{
                return ($min + lcg_value()*(abs($max - $min)));
        }

        /**
         * @param Position $position
         *
         * @return Location
         */
        public static function positionToLocation(Position $position): Location{
                return new Location($position->x, $position->y, $position->z, $position->world, 0, 0);
        }

        /**
         * @param string $string
         *
         * @return string
         */
        public static function colorMessage(string $string): string{
                return str_replace("&", "\xc2\xa7", $string);
        }

        /**
         * @param Player[] $target
         * @param Block[]  $blocks
         * @param int      $flags
         * @param bool     $optimizeRebuilds
         */
        public static function sendBlocks(array $target, array $blocks, int $flags = UpdateBlockPacket::FLAG_NONE, bool $optimizeRebuilds = false){
                /** @var ClientboundPacket $packets */
                $packets = [];
                if($optimizeRebuilds){
                        $chunks = [];
                        foreach($blocks as $block){
                                if($block === null){
                                        continue;
                                }

                                $pk = new UpdateBlockPacket();
                                $position = $block->getPosition();

                                $first = false;
                                if(!isset($chunks[$index = World::chunkHash($position->x >> 4, $position->z >> 4)])){
                                        $chunks[$index] = true;
                                        $first = true;
                                }

                                $pk->blockPosition = BlockPosition::fromVector3($block->getPosition());

                                if($block instanceof Block){
                                        $blockId = $block->getId();
                                        $blockData = $block->getMeta();
                                }else{
                                        $fullBlock = $position->getWorld()->getChunk($position->x >> 4, $position->y >> 4)->getFullBlock($position->x, $position->y, $position->z);
                                        $blockId = $fullBlock >> 4;
                                        $blockData = $fullBlock & 0xf;
                                }


                                $pk->blockRuntimeId = RuntimeBlockMapping::getInstance()->toRuntimeId(self::legacyToInternalStateId($blockId, $blockData));
                                $pk->flags = $first ? $flags : UpdateBlockPacket::FLAG_NONE;

                                $packets[] = $pk;
                        }
                }else{
                        foreach($blocks as $block){
                                if($block === null){
                                        continue;
                                }
                                $pk = new UpdateBlockPacket();
                                $position = $block->getPosition();

                                $pk->blockPosition = BlockPosition::fromVector3($block->getPosition());

                                if($block instanceof Block){
                                        $blockId = $block->getId();
                                        $blockData = $block->getMeta();
                                }else{
                                        $fullBlock = $position->getWorld()->getChunk($position->x >> 4, $position->y >> 4)->getFullBlock($position->x, $position->y, $position->z);
                                        $blockId = $fullBlock >> 4;
                                        $blockData = $fullBlock & 0xf;
                                }

                                $pk->blockRuntimeId = RuntimeBlockMapping::getInstance()->toRuntimeId(self::legacyToInternalStateId($blockId, $blockData));
                                $pk->flags = $flags;

                                $packets[] = $pk;
                        }
                }

                Jager::$instance->getServer()->broadcastPackets($target, $packets);
        }

        /**
         * @param int $legacyId
         * @param int $legacyMeta
         *
         * @return int
         */
        public static function legacyToInternalStateId(int $legacyId, int $legacyMeta): int{
                return ($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta;
        }
}