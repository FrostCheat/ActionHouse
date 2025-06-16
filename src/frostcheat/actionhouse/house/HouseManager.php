<?php

namespace frostcheat\actionhouse\house;

use frostcheat\actionhouse\language\LanguageManager;
use frostcheat\actionhouse\language\TranslationKeys;
use frostcheat\actionhouse\language\TranslationMessages;
use frostcheat\actionhouse\libs\muqsit\invmenu\InvMenu;
use frostcheat\actionhouse\libs\muqsit\invmenu\transaction\InvMenuTransaction;
use frostcheat\actionhouse\libs\muqsit\invmenu\transaction\InvMenuTransactionResult;
use frostcheat\actionhouse\libs\muqsit\invmenu\type\InvMenuTypeIds;
use frostcheat\actionhouse\Loader;
use frostcheat\actionhouse\provider\Provider;
use frostcheat\actionhouse\provider\Serialize;
use frostcheat\actionhouse\utils\Utils;
use pocketmine\block\ShulkerBox;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\item\Item as BedrockItem;

class HouseManager
{
    use SingletonTrait;

    /**
     * @var array<int, Item>
     */
    private array $items = [];

    public function init(): void {
        $i = Provider::getInstance()->getConfigItems()->getAll();

        foreach($i as $id => $item) {
            $this->items[(int) $id] = new Item((int) $id, Serialize::deserialize($item["item"]), $item["player"], (int) $item["price"], (int) $item["expiryTime"]);
        }
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(Item $item): void
    {
        $this->items[$item->getId()] = $item;
    }

    public function removeItem(Item $item): void {
        unset($this->items[$item->getId()]);
    }

    public function getItem(int $id): ?Item {
        return $this->items[$id] ?? null;
    }

    public function sendMainMenu(Player $player, int $page = 1): void
    {
        $itemsPerPage = 44;
        $allItems = array_filter($this->getItems(), fn($item) => $item->getExpiryTime() > time());
        $totalPages = (int) ceil(count($allItems) / $itemsPerPage);
        $page = max(1, min($page, $totalPages));

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName(LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_TITLE, [
            TranslationKeys::CURRENT_PAGE => $page,
        ]));

        $menu->getInventory()->clearAll();

        $menu->getInventory()->setItem(45,
            VanillaItems::DIAMOND()->setNamedTag(CompoundTag::create()->setInt("menu_action", 3))->setCustomName(
                LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_YOUR_ITEMS)
            ));
        $menu->getInventory()->setItem(49, VanillaItems::GOLD_INGOT()->setCustomName(
            LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_BALANCE, [
                TranslationKeys::BALANCE => Utils::getInstance()->formatBalance(Utils::getInstance()->getBalance($player->getName())),
            ])
        ));
        $menu->getInventory()->setItem(52,
            VanillaItems::ARROW()->setNamedTag(CompoundTag::create()->setInt("menu_action", 1))->setCustomName(
                LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_PREVIOUS_PAGE)
            ));
        $menu->getInventory()->setItem(53,
            VanillaItems::ARROW()->setNamedTag(CompoundTag::create()->setInt("menu_action", 2))->setCustomName(
                LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_NEXT_PAGE)
            ));

        $start = ($page - 1) * $itemsPerPage;
        $pageItems = array_slice($allItems, $start, $itemsPerPage);

        foreach ($pageItems as $index => $itemData) {
            $menu->getInventory()->setItem($index, $this->prepareItem($itemData));
        }

        $menu->setListener(function (InvMenuTransaction $transaction) use ($page, $player): InvMenuTransactionResult {
            $item = $transaction->getItemClicked();

            if ($item->getNamedTag() !== null) {
                $tag = $item->getNamedTag();

                if ($tag->getTag("menu_action") !== null) {
                    $action = $tag->getInt("menu_action");
                    if ($action === 1) {
                        $this->sendMainMenu($player, $page - 1);
                    } elseif ($action === 2) {
                        $this->sendMainMenu($player, $page + 1);
                    } elseif ($action === 3) {
                        $this->showYourItems($player);
                    }
                    return $transaction->discard();
                }

                if ($tag->getTag("id") !== null) {
                    $i = $this->getItem($tag->getInt("id"));
                    if ($i !== null) {
                        $price = $i->getPrice();
                        $balance = Utils::getInstance()->getBalance($player->getName());

                        if ($balance < $price) {
                            $player->removeCurrentWindow();
                            $player->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::PLAYER_INSUFFICIENT_FUNDS));
                            return $transaction->discard();
                        }

                        $this->confirmBuyItem($player, $i);
                    }
                }
            }

            return $transaction->discard();
        });

        $menu->send($player);
    }



    public function prepareItem(Item $item): BedrockItem {
        $i = clone $item->getItem();
        $i->setLore(LanguageManager::getInstance()->getTranslation(TranslationMessages::ITEM_LORE, [
            TranslationKeys::SELLER => $item->getPlayer(),
            TranslationKeys::PRICE => Utils::getInstance()->formatBalance($item->getPrice()),
            TranslationKeys::EXPIRY_TIME => Utils::getInstance()->formatTimeRemaining($item->getExpiryTime() - time()),
        ]));

        $namedtag = $i->getNamedTag();
        $namedtag->setInt("id", $item->getId());
        $i->setNamedTag($namedtag);

        return $i;
    }

    public function confirmBuyItem(Player $player, Item $i): void
    {
        $player->removeCurrentWindow();

        if ($i->getItem()->getBlock() instanceof ShulkerBox) {
            $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
            $menu->getInventory()->setContents([
                37 => VanillaBlocks::REDSTONE()->asItem()->setNamedTag(CompoundTag::create()->setInt("action", 1))->setCustomName(LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_CANCEL_BUY)),
                40 => $i->getItem(),
                43 => VanillaBlocks::EMERALD()->asItem()->setNamedTag(CompoundTag::create()->setInt("action", 2))->setCustomName(LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_CONFIRM_BUY)),
            ]);
            $items = Utils::getInstance()->getShulkerBoxContents($i->getItem());

            foreach ($items as $id => $item) {
                $menu->getInventory()->setItem($id, $item);
            }
        } else {
            $menu = InvMenu::create(InvMenuTypeIds::TYPE_HOPPER);
            $menu->getInventory()->setContents([
                0 => VanillaBlocks::REDSTONE()->asItem()->setNamedTag(CompoundTag::create()->setInt("action", 1))->setCustomName(LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_CANCEL_BUY)),
                2 => $i->getItem(),
                4 => VanillaBlocks::EMERALD()->asItem()->setNamedTag(CompoundTag::create()->setInt("action", 2))->setCustomName(LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_CONFIRM_BUY)),
            ]);
        }

        $menu->setName(LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_CONFIRM_TITLE));

        $menu->setListener(function (InvMenuTransaction $transaction) use ($i): InvMenuTransactionResult {
            $item = $transaction->getItemClicked();
            $player = $transaction->getPlayer();

            $namedtag = $item->getNamedTag();
            if ($namedtag->getTag("action") === null) return $transaction->discard();

            if ($namedtag->getInt("action") === 1) {
                $player->removeCurrentWindow();

                $this->sendMainMenu($player);
            } elseif ($namedtag->getInt("action") === 2) {
                $player->removeCurrentWindow();

                if ($this->getItem($i->getId()) === null) {
                    return $transaction->discard();
                }

                if (!$player->getInventory()->canAddItem($i->getItem())) {
                    $player->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::PLAYER_INVENTORY_FULL));
                    return $transaction->discard();
                }

                Utils::getInstance()->removeBalance($player->getName(), $i->getPrice(), function (bool $success) use ($player, $i, $item) {
                    if ($success) {
                        Utils::getInstance()->addBalance($i->getPlayer(), $i->getPrice(), function (bool $success) use ($i, $player) {
                            if ($success) {
                                $p = Loader::getInstance()->getServer()->getPlayerExact($i->getPlayer());
                                if ($p !== null) {
                                    $p->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::PLAYER_SELL_NOTIFICATION, [
                                        TranslationKeys::PLAYER => $player->getName(),
                                        TranslationKeys::PRICE => Utils::getInstance()->formatBalance($i->getPrice()),
                                        TranslationKeys::ITEM => "(x" . $i->getItem()->getCount() . ") " . $i->getItem()->getName(),
                                    ]));
                                }
                            }
                        });
                        $player->getInventory()->addItem($i->getItem());
                        $player->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::PLAYER_BUY_SUCCESS, [
                            TranslationKeys::ITEM => "(x" . $i->getItem()->getCount() . ") " . $i->getItem()->getName(),
                            TranslationKeys::PRICE => Utils::getInstance()->formatBalance($i->getPrice()),
                        ]));
                        $this->removeItem($i);
                    } else {
                        $player->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::PLAYER_INSUFFICIENT_FUNDS));
                    }
                });
            }
            return $transaction->discard();
        });

        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) {
            $this->sendMainMenu($player);
        });

        $menu->send($player);
    }

    public function showYourItems(Player $player, int $page = 1): void {
        $player->removeCurrentWindow();

        $itemsPerPage = 44;
        $allItems = array_filter($this->getItems(), fn($item) => $item->getPlayer() === $player->getName());
        $totalPages = (int) ceil(count($allItems) / $itemsPerPage);
        $page = max(1, min($page, $totalPages));

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName(LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_YOUR_ITEMS_TITLE, [
            TranslationKeys::CURRENT_PAGE => $page,
        ]));

        $menu->getInventory()->clearAll();

        $start = ($page - 1) * $itemsPerPage;
        $pageItems = array_slice($allItems, $start, $itemsPerPage);

        foreach ($pageItems as $index => $itemData) {
            if ($index >= 44) break;
            $menu->getInventory()->setItem($index, $this->prepareItem($itemData));
        }

        $menu->getInventory()->setItem(45,
            VanillaItems::REDSTONE_DUST()->setNamedTag(CompoundTag::create()->setString("action", "back"))->setCustomName(LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_BACK))
        );

        $menu->getInventory()->setItem(52,
            VanillaItems::ARROW()->setNamedTag(CompoundTag::create()->setInt("page_action", 1))->setCustomName(LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_PREVIOUS_PAGE))
        );

        $menu->getInventory()->setItem(53,
            VanillaItems::ARROW()->setNamedTag(CompoundTag::create()->setInt("page_action", 2))->setCustomName(LanguageManager::getInstance()->getTranslation(TranslationMessages::MENU_ITEM_NEXT_PAGE))
        );

        $menu->setListener(function (InvMenuTransaction $transaction) use ($player, $page): InvMenuTransactionResult {
            $item = $transaction->getItemClicked();
            $tag = $item->getNamedTag();

            if ($tag !== null) {
                if ($tag->getTag("action") !== null && $tag->getString("action") === "back") {
                    $this->sendMainMenu($player);
                    return $transaction->discard();
                }

                if ($tag->getTag("page_action") !== null) {
                    $action = $tag->getInt("page_action");
                    if ($action === 1) {
                        $this->showYourItems($player, $page - 1);
                    } elseif ($action === 2) {
                        $this->showYourItems($player, $page + 1);
                    }
                    return $transaction->discard();
                }

                if ($tag->getInt("id") !== null) {
                    $i = $this->getItem($tag->getInt("id"));
                    if ($i !== null) {
                        if ($player->getInventory()->canAddItem($i->getItem())) {
                            $player->getInventory()->addItem($i->getItem());
                            $this->removeItem($i);
                            $this->showYourItems($player);
                        } else {
                            $player->sendMessage(LanguageManager::getInstance()->getPrefix() . LanguageManager::getInstance()->getTranslation(TranslationMessages::PLAYER_INVENTORY_FULL));
                        }
                        return $transaction->discard();
                    }
                }
            }
            return $transaction->discard();
        });

        $menu->send($player);
    }
}