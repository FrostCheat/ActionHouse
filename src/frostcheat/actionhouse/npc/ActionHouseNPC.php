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