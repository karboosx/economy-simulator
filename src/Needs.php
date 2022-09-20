<?php

namespace Karbo\Economy;

class Needs
{
    private array $supplies = [];
    private array $demands = [];

    // set demand for a good
    public function setDemand(string $goods, int $amount, float $price): ?Order
    {
        if ($amount <= 0) {
            return null;
        }

        $this->demands[$goods] = new Order($goods, $amount, $price);

        return $this->demands[$goods];
    }

    // set supply for a good
    public function setSupply(string $goods, int $amount, float $price): ?Order
    {
        if ($amount <= 0) {
            return null;
        }

        $this->supplies[$goods] = new Order($goods, $amount, $price);

        return $this->supplies[$goods];
    }

    // get demands
    public function getDemand(string $goods): ?Order
    {
        return $this->demands[$goods] ?? null;
    }

    // get supplies
    public function getSupply(string $goods): ?Order
    {
        return $this->supplies[$goods] ?? null;
    }

    public function getDemands(): array
    {
        return $this->demands;
    }

    public function getSupplies(): array
    {
        return $this->supplies;
    }
}