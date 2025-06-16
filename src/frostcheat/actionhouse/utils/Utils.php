<?php

namespace frostcheat\actionhouse\utils;

use Closure;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;
use cooldogedev\BedrockEconomy\database\constant\Search;
use frostcheat\actionhouse\Loader;
use pocketmine\block\tile\Container;
use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\utils\SingletonTrait;

class Utils
{
    use SingletonTrait;

    public function getBalance(string $player): int {
        $cacheEntry = GlobalCache::ONLINE()->get($player);
        return $cacheEntry !== null ? $cacheEntry->amount : 0;
    }

    public function formatBalance(int $amount): string {
        return BedrockEconomy::getInstance()->getCurrency()->formatter->format($amount, 0);
    }

    public function addBalance(string $player, int $amount, Closure $callback): void {
        BedrockEconomyAPI::CLOSURE()->add(
            Search::EMPTY,
            $player,
            $amount,
            0,
            fn () => $callback(true),
            fn () => $callback(false)
        );
    }

    public function removeBalance(string $player, int $amount, Closure $callback): void {
        BedrockEconomyAPI::CLOSURE()->subtract(
            Search::EMPTY,
            $player,
            $amount,
            0,
            fn () => $callback(true),
            fn () => $callback(false)
        );
    }


    public function formatTimeRemaining(int $seconds): string {
        if ($seconds <= 0) return "0s";

        $days = floor($seconds / 86400);
        $seconds %= 86400;

        $hours = floor($seconds / 3600);
        $seconds %= 3600;

        $minutes = floor($seconds / 60);
        $seconds %= 60;

        $result = "";
        if ($days > 0) $result .= "{$days}d ";
        if ($hours > 0) $result .= "{$hours}h ";
        if ($minutes > 0) $result .= "{$minutes}m ";
        if ($seconds > 0 || $result === "") $result .= "{$seconds}s";

        return trim($result);
    }

    public function strToTime(string $input): int {
        $units = [
            's' => 1,
            'm' => 60,
            'h' => 3600,
            'd' => 86400,
            'w' => 604800,
            'mo' => 2592000,
            'y' => 31536000
        ];

        if (preg_match('/^(\d+)(mo|[smhdwy])$/', strtolower($input), $matches)) {
            $value = (int)$matches[1];
            $unit = $matches[2];

            if (isset($units[$unit])) {
                return $value * $units[$unit];
            }
        }
        return 0;
    }


    public function getShulkerBoxContents(Item $shulkerItem): array {
        $contents = [];

        $tag = $shulkerItem->getNamedTag();
        if(($inventoryTag = $tag->getTag(Container::TAG_ITEMS)) instanceof ListTag && $inventoryTag->getTagType() === NBT::TAG_Compound){
            /** @var CompoundTag $itemNBT */
            foreach($inventoryTag as $itemNBT){
                try{
                    $contents[$itemNBT->getByte(SavedItemStackData::TAG_SLOT)] = Item::nbtDeserialize($itemNBT);
                }catch(SavedDataLoadingException $e){
                    Loader::getInstance()->getLogger()->error($e);
                    continue;
                }
            }
        }

        return $contents;
    }

}