<?php

namespace frostcheat\actionhouse\commands\subcommands;

use frostcheat\actionhouse\house\HouseManager;
use frostcheat\actionhouse\house\Item;
use frostcheat\actionhouse\language\LanguageManager;
use frostcheat\actionhouse\language\TranslationKeys;
use frostcheat\actionhouse\language\TranslationMessages;
use frostcheat\actionhouse\libs\CortexPE\Commando\args\IntegerArgument;
use frostcheat\actionhouse\libs\CortexPE\Commando\BaseSubCommand;
use frostcheat\actionhouse\libs\CortexPE\Commando\constraint\InGameRequiredConstraint;
use frostcheat\actionhouse\libs\CortexPE\Commando\exception\ArgumentOrderException;
use frostcheat\actionhouse\Loader;
use frostcheat\actionhouse\utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class SellSubCommand extends BaseSubCommand
{
    public function __construct() {
        parent::__construct("sell", "Action House Sell item");
        $this->setPermission("actionhouse.command.sell");
    }

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerArgument(0, new IntegerArgument("price"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::COMMAND_NO_PLAYER));
            return;
        }

        $allItems = array_filter(HouseManager::getInstance()->getItems(), fn($item) => $item->getPlayer() === $sender->getName());
        $maxItems = $this->checkMaxItems($sender);
        if ($maxItems !== -1 && count($allItems) >= $maxItems) {
            $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::PLAYER_MAX_ITEMS, [
                TranslationKeys::MAX_ITEMS => $maxItems,
            ]));
            return;
        }

        $item = $sender->getInventory()->getItemInHand();
        if ($item === null) {
            $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::PLAYER_NO_ITEM_IN_HAND));
            return;
        }

        $minPrice = Loader::getInstance()->getConfig()->get("min-price");
        $maxPrice = Loader::getInstance()->getConfig()->get("max-price");

        $price = $args["price"];
        if ($price < $minPrice) {
            $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::COMMAND_SELL_MIN_PRICE_ERROR, [
                TranslationKeys::PRICE => Utils::getInstance()->formatBalance($minPrice),
            ]));
            return;
        }

        if ($price > $maxPrice) {
            $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::COMMAND_SELL_MAX_PRICE_ERROR, [
                TranslationKeys::PRICE => Utils::getInstance()->formatBalance($maxPrice),
            ]));
            return;
        }

        HouseManager::getInstance()->addItem(new Item(
            count(HouseManager::getInstance()->getItems()),
            $item,
            $sender->getName(),
            $price,
            time() + Utils::getInstance()->strToTime(
                Loader::getInstance()->getConfig()->get("sell-time", "3d"))
        ));

        $sender->getInventory()->setItemInHand(VanillaItems::AIR());
        foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $player->sendPopup(LanguageManager::getInstance()->getTranslation(TranslationMessages::PLAYER_SELL_POPUP, [
                TranslationKeys::PLAYER => $sender->getName(),
                TranslationKeys::ITEM => "(x" . $item->getCount() . ") " . $item->getName(),
            ]));
        }
        $sender->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::COMMAND_SELL_SUCCESS, [
            TranslationKeys::PRICE => Utils::getInstance()->formatBalance($price),
            TranslationKeys::ITEM => "(x" . $item->getCount() . ") " . $item->getName(),
        ]));
    }

    private function checkMaxItems(Player $player): int {
        $groups = Loader::getInstance()->getConfig()->get("groups", []);
        $max = Loader::getInstance()->getConfig()->get("default-items", 5);

        foreach ($groups as $group => $limit) {
            $permission = "actionhouse.group." . $group;

            if ($player->hasPermission($permission)) {
                if ($limit === -1) {
                    return -1;
                }
                $max = max($max, $limit);
            }
        }

        return $max;
    }

}