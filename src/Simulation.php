<?php

namespace Karbo\Economy;

use Karbo\Economy\PriceCalculator;

class Simulation
{
    /**
     * @var Building[]
     */
    private array $buildings = [];
    private PriceCalculator $priceCalculator;
    private array $stat;
    private array $inventoryStat;
    private array $lastSeenLowest = [
        'food' => 2,
        'work_force' => 5,
    ];

    public function __construct()
    {
        $this->priceCalculator = new PriceCalculator();
    }

    /**
     * Find best building that offer a good in the lowest price
     */
    public function findCheapestSupplier($demand): ?Supplier
    {
        $bestPrice = null;
        $bestBuilding = null;
        foreach ($this->buildings as $building) {
            if (!$building->isOffering($demand->getGoods())) {
                continue;
            }

            $price = $building->getPrice($demand->getGoods());

            if ($price > $demand->getPrice()) {
                continue;
            }

            if ($price < $bestPrice || $bestPrice === null) {
                $bestPrice = $price;
                $bestBuilding = $building;
            }
        }

        if ($bestBuilding === null) {
            return null;
        }

        return new Supplier($bestBuilding, $bestBuilding->getSupply($demand->getGoods()));
    }

    /**
     * Find best building that offering a good in the highest price
     */
    public function findMostExpensiveSupplier($demand): ?Supplier
    {
        $bestPrice = null;
        $bestBuilding = null;
        foreach ($this->buildings as $building) {
            if (!$building->isOffering($demand->getGoods())) {
                continue;
            }

            $price = $building->getPrice($demand->getGoods());

            if ($price < $demand->getPrice()) {
                continue;
            }

            if ($price > $bestPrice || $bestPrice === null) {
                $bestPrice = $price;
                $bestBuilding = $building;
            }
        }

        if ($bestBuilding === null) {
            return null;
        }

        return new Supplier($bestBuilding, $bestBuilding->getSupply($demand->getGoods()));
    }


    /**
     * Find best offer a good in the lowest price
     */
    public function getBestSupplyPrice(string $goods, $additionFactor = 0): float
    {
        $bestPrice = null;
        foreach ($this->buildings as $building) {
            if (!$building->isOffering($goods)) {
                continue;
            }

            $price = $building->getPrice($goods);

            if ($price < $bestPrice || $bestPrice === null) {
                $bestPrice = $price;
            }
        }

        if ($bestPrice === null) {
            $bestPrice = 0;
        }

        return $this->priceCalculator->changePrice($bestPrice, $additionFactor);
    }

    public function getSupply(string $goods): int
    {
        $supply = 0;
        foreach ($this->buildings as $building) {
            if (!$building->isOffering($goods)) {
                continue;
            }

            $supply += $building->getSupply($goods)->getAmount();
        }

        return $supply;
    }

    public function getDemand(string $goods): int
    {
        $demand = 0;
        foreach ($this->buildings as $building) {
            if (!$building->isDemanding($goods)) {
                continue;
            }

            $demand += $building->getDemand($goods)->getAmount();
        }

        return $demand;
    }

    public function getBestDemandPrice(string $goods, $additionFactor = 0): float
    {
        $bestPrice = null;
        foreach ($this->buildings as $building) {
            if (!$building->isDemanding($goods)) {
                continue;
            }

            $price = $building->getPrice($goods);

            if ($price > $bestPrice || $bestPrice === null) {
                $bestPrice = $price;
            }
        }

        if ($bestPrice === null) {
            $bestPrice = 0;
        }

        return $this->priceCalculator->changePrice($bestPrice, $additionFactor);
    }

    public function addBuilding(Building $building): void
    {
        $this->buildings[] = $building;
    }

    public function tick(): void
    {
        foreach ($this->buildings as $building) {
            $building->assumeTick();
            $this->recordLowestOffers($building);
        }

        $this->collectSupplyAndDemandStats();

        foreach ($this->buildings as $building) {
            $building->fulfillNeeds();
        }

        $this->collectBuildingInventoryStats();

        foreach ($this->buildings as $building) {
            $building->work();
        }

        $this->collectFulfilledNeedsStats();

        foreach ($this->buildings as $building) {
            $building->validate();
        }
    }

    public function build(string $type): Building
    {
        switch ($type) {
            case 'farm':
                $building = new Farm($this->priceCalculator, $this);
                $building->addInventory('food', 10);
                $building->setPrice('food', 2);
                $building->setPrice('work_force', 2);
                $building->setMoney(1000);
                break;
            case 'house':
                $building = new House($this->priceCalculator, $this);
                $building->addInventory('food', 4);
                $building->setPrice('food', 2);
                $building->setPrice('work_force', 5);
                $building->setMoney(1000);
                break;
            default:
                throw new \Exception('Unknown building type');
        }

        $this->addBuilding($building);
        return $building;
    }


    public function getStats()
    {
        return [
            'buildings' => count($this->buildings),
            'buy_prices' => [
                'food' => $this->getBestDemandPrice('food'),
                'work_force' => $this->getBestDemandPrice('work_force'),
            ],
            'sell_prices' => [
                'food' => $this->getBestSupplyPrice('food'),
                'work_force' => $this->getBestSupplyPrice('work_force'),
            ],
            'prices2' => [
                'food' => $this->getBestDemandPrice('food'),
                'work_force' => $this->getBestDemandPrice('work_force'),
            ],
            'supply' => $this->stat['supply'],
            'demand' => $this->stat['demand'],
            'inventory' => $this->inventoryStat,
            'fulfilled_demand' => $this->stat['fulfilled_demand'],
        ];
    }

    private function collectSupplyAndDemandStats()
    {
        $this->stat = [
            'supply' => [
                'food' => 0,
                'work_force' => 0,
            ],
            'demand' => [
                'food' => 0,
                'work_force' => 0,
            ],
        ];

        foreach ($this->buildings as $building) {
            foreach ($building->getNeeds()->getDemands() as $demand) {
                $this->stat['demand'][$demand->getGoods()] += $demand->getAmount();
            }

            foreach ($building->getNeeds()->getSupplies() as $supply) {
                $this->stat['supply'][$supply->getGoods()] += $supply->getAmount();
            }
        }
    }

    private function collectBuildingInventoryStats()
    {
        $this->inventoryStat = [
            'food' => 0,
            'work_force' => 0,
        ];

        foreach ($this->buildings as $building) {
            foreach ($building->getInventory() as $goods => $amount) {
                $this->inventoryStat[$goods] += $amount;
            }
        }
    }

    private function collectFulfilledNeedsStats()
    {
        $this->stat['fulfilled_demand'] = [
            'food' => 0,
            'work_force' => 0,
        ];

        foreach ($this->buildings as $building) {
            foreach ($building->getNeeds()->getDemands() as $demand) {
                $this->stat['fulfilled_demand'][$demand->getGoods()] += $demand->getFulfilledAmount();
            }
        }
    }

    private function lastSeenLowest(string $goods)
    {
        return $this->lastSeenLowest[$goods] ?? 1;
    }

    private function recordLowestOffers(Building $building)
    {
        foreach ($building->getNeeds()->getSupplies() as $supply) {
            if ($supply->getPrice() < $this->lastSeenLowest($supply->getGoods())) {
                $this->lastSeenLowest[$supply->getGoods()] = $supply->getPrice();
            }
        }
    }
}