<?php
namespace xBeastMode\Jager\Entity;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Projectile;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;
use xBeastMode\Jager\CustomItems\LethalSword;
use xBeastMode\Jager\Utils;
class Sword extends Projectile{
        /** @var Player */
        public $hunter;
        /** @var Player[] */
        public $spectators = [];

        public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null){
                parent::__construct($location, $shootingEntity, $nbt);

                $this->setCanSaveWithChunk(false);
        }

        protected function sendSpawnPacket(Player $player) : void{
                parent::sendSpawnPacket($player);

                $packet = new MobEquipmentPacket();
                $packet->actorRuntimeId = $this->id;
                $packet->item = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet(VanillaItems::IRON_SWORD()));
                $packet->hotbarSlot = 0;
                $packet->inventorySlot = 0;

                $player->getNetworkSession()->sendDataPacket($packet);
        }

        public function onCollideWithPlayer(Player $player): void{
                if($this->isClosed() || $this->hunter === null || $this->spectators === null){
                        $this->flagForDespawn();
                        return;
                }

                if($this->hunter === $player) return;
                if(in_array($player, $this->spectators, true)) return;

                Utils::playSound("armor.equip_iron", $player, 1000, 1, true);

                $player->sendMessage("§r§l§8» §aEQUIPPED LETHAL SWORD");
                $player->getInventory()->addItem(new LethalSword());

                $this->flagForDespawn();
        }

        protected function getInitialSizeInfo(): EntitySizeInfo{
                return new EntitySizeInfo(1.975, 0.5);
        }

        public static function getNetworkTypeId(): string{
                return EntityIds::ARMOR_STAND;
        }
}