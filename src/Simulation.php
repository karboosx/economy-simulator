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
    private $turn = 0;

    public function __construct()
    {
        $this->priceCalculator = new PriceCalculator();
    }

    /**
     * Find best building that offer a good in the lowest price
     */
    public function findCheapestSupplier(Order $demand): ?Supplier
    {
        $bestPrice = null;
        $bestBuilding = null;
        foreach ($this->buildings as $building) {
            if (!$building->isOffering($demand->getGoods())) {
                continue;
            }

            $price = $building->getPrice($demand->getGoods());

            if (!$this->priceIsAcceptable($demand->getPrice(), $price) ) {
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

    private function priceIsAcceptable(float $price, float $offeringPrice)
    {

        if ($offeringPrice < $price) {
            return true;
        }

        return abs($price - $offeringPrice) < 0.01;
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
        $this->collectBuildingMoneyStats();

        foreach ($this->buildings as $building) {
            $building->work();
            if ($building->getMoney() <1) {
                if ($building->terminate()){
                    $this->removeBuilding($building);
                }
            }
        }

        $this->collectFulfilledNeedsStats();
        $this->collectBuildingCountStats();

        foreach ($this->buildings as $building) {
            $building->validate();
        }
    }

    public function build(string $type): Building
    {
        switch ($type) {
            case 'farm':
                $building = new Farm($this->priceCalculator, $this);
                $building->addInventory('food', 4);
                $building->setPrice('food', 0.5);
                $building->setPrice('work_force', 4);
                $building->setMoney(100+10*$this->getBuildingCount('farm'));
                break;
            case 'house':
                $building = new House($this->priceCalculator, $this);
                $building->addInventory('work_force', 2);
                $building->setPrice('food', 2);
                $building->setPrice('work_force', 4);
                $building->setMoney(100+100*$this->getBuildingCount('house'));
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
            'money' => $this->stat['money'],
            'buildings_count' => $this->stat['buildings_count'],
        ];
    }


    private function collectBuildingCountStats(): void
    {
        $this->stat['buildings_count'] = [
            'farm' => $this->getBuildingCount(Farm::class),
            'house' => $this->getBuildingCount(House::class),
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

    private function collectBuildingMoneyStats()
    {
        $this->stat['money'] = [];

        foreach ($this->buildings as $building) {
            if (!isset($this->stat['money'][get_class($building)])) {
                $this->stat['money'][get_class($building)] = 0;
            }

            $this->stat['money'][get_class($building)] += $building->getMoney();
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

    public function removeBuilding(Building $building)
    {
        echo '['.get_class($building) . ' removed at turn '.str_pad($this->turn, 4, '_', STR_PAD_LEFT).']'.PHP_EOL;
        $key = array_search($building, $this->buildings, true);
        if ($key === false) {
            throw new \Exception('Building not found');
        }

        unset($this->buildings[$key]);
    }

    public function getBuildingCount(string $type): int
    {
        $count = 0;
        foreach ($this->buildings as $building) {
            if (get_class($building) === $type) {
                $count++;
            }
        }

        return $count;
    }

    public function setTurn(int $turn)
    {
        $this->turn = $turn;
    }

}