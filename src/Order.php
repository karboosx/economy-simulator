<?php

namespace Karbo\Economy;

class Order
{
    public const CHEAPEST = 'cheapest';
    public const MOST_EXPENSIVE = 'mostExpensive';

    private string $goods;
    private int $amount;
    private float $price;
    private int $fulfilledAmount = 0;
    private string $type = self::CHEAPEST;

    public function __construct(string $goods, int $amount = 0, float $price = 0)
    {
        $this->goods = $goods;
        $this->amount = $amount;
        $this->price = $price;
    }

    public function getGoods(): string
    {
        return $this->goods;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function fulfill(int $amount): void
    {
        $this->fulfilledAmount += $amount;
    }

    public function getFulfilledAmount(): int
    {
        return $this->fulfilledAmount;
    }

    public function wasFulfilled(): bool
    {
        return $this->fulfilledAmount >= $this->amount;
    }

    public function noOneFulfilledIt(): bool
    {
        return $this->fulfilledAmount == 0;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function searchCheapest(): void
    {
        $this->type = self::CHEAPEST;
    }

    public function searchMostExpensive(): void
    {
        $this->type = self::MOST_EXPENSIVE;
    }
}