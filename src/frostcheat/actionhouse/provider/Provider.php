<?php

namespace frostcheat\actionhouse\provider;

use frostcheat\actionhouse\house\HouseManager;
use frostcheat\actionhouse\Loader;
use frostcheat\actionhouse\provider\task\SaveItemsAsyncTask;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class Provider
{
    use SingletonTrait;

    private Config $configItems;

    public function __construct()
    {
        $this->configItems = new Config(Loader::getInstance()->getDataFolder() . "items.yml");
    }

    public function getConfigItems(): Config
    {
        return $this->configItems;
    }

    public function saveItems(): void {
        $items = array_map(function ($item) {
            return [
                "item" => Serialize::serialize($item->getItem()),
                "price" => $item->getPrice(),
                "player" => $item->getPlayer(),
                "expiryTime" => $item->getExpiryTime(),
            ];
        }, HouseManager::getInstance()->getItems());

        $file = Loader::getInstance()->getDataFolder() . "items.yml";
        Loader::getInstance()->getServer()->getAsyncPool()->submitTask(new SaveItemsAsyncTask($file, $items));
    }
}