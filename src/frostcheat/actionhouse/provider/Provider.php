<?php

namespace frostcheat\actionhouse\provider;

use frostcheat\actionhouse\house\HouseManager;
use frostcheat\actionhouse\Loader;
use JsonException;
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

        $this->getConfigItems()->setAll($items);
        try {
            $this->getConfigItems()->save();
        } catch (JsonException $e) {
            Loader::getInstance()->getLogger()->error($e->getMessage());
        }
    }
}