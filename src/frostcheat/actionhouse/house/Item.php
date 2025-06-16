<?php

namespace frostcheat\actionhouse\house;

use pocketmine\item\Item as BedrockItem;

class Item
{

    private int $id;
    private BedrockItem $item;
    private string $player;
    private int $price;
    private int $expiryTime;

    public function __construct($id, $item, $player, $price, $expiryTime)
    {
        $this->id = $id;
        $this->item = $item;
        $this->player = $player;
        $this->price = $price;
        $this->expiryTime = $expiryTime;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return BedrockItem
     */
    public function getItem(): BedrockItem
    {
        return $this->item;
    }

    /**
     * @return string
     */
    public function getPlayer(): string
    {
        return $this->player;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getExpiryTime(): int
    {
        return $this->expiryTime;
    }
}