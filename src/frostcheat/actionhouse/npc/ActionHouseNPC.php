<?php

namespace frostcheat\actionhouse\npc;

use frostcheat\actionhouse\house\HouseManager;
use frostcheat\actionhouse\language\LanguageManager;
use frostcheat\actionhouse\language\TranslationMessages;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\Position;

class ActionHouseNPC extends Human
{
    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);

        $this->setNameTag(LanguageManager::getInstance()->getTranslation(TranslationMessages::NPC_NAME));
        $this->setScoreTag(LanguageManager::getInstance()->getTranslation(TranslationMessages::NPC_SCORE_TAG));
        $this->setNameTagVisible();
        $this->setNameTagAlwaysVisible();

        $this->setNoClientPredictions();
    }


    public function onUpdate(int $currentTick): bool {
        $updated = parent::onUpdate($currentTick);

        foreach ($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy(10, 10, 10)) as $entity) {
            if ($entity instanceof Player && $entity->isOnline()) {
                $this->lookAt(new Position($entity->getPosition()->x, $entity->getPosition()->y + 1.5, $entity->getPosition()->z, $entity->getWorld()));
                break;
            }
        }

        return $updated;
    }

    public function attack(EntityDamageEvent $source): void {
        $source->cancel();

        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();

            if (!$damager instanceof Player) {
                return;
            }

            if ($damager->hasPermission('actionhouse.command.npc') && $damager->getInventory()->getItemInHand()->equals(VanillaBlocks::BEDROCK()->asItem(), false, false)) {
                $this->flagForDespawn();
                return;
            }
            HouseManager::getInstance()->sendMainMenu($damager);
        }
    }
}