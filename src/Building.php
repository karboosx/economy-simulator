<?php

namespace Karbo\Economy;

abstract class Building
{
    protected ?Needs $lastNeeds = null;
    protected ?Needs $currentNeeds = null;
    protected array $inventory = [];
    protected array $targetStorage = [];

    private $money = 100;
    private $prices = [];

    protected PriceCalculator $priceCalculator;
    protected Simulation $simulation;

    public function __construct(PriceCalculator $priceCalculator, Simulation $simulation)
    {
        $this->priceCalculator = $priceCalculator;
        $this->simulation = $simulation;
    }

    public function getMoney(): int
    {
        return $this->money;
    }

    public function setMoney(int $money): void
    {
        $this->money = $money;
    }

    public function addMoney(int $money): void
    {
        $this->money += $money;
    }

    public function removeMoney(int $money): void
    {
        $this->money -= $money;
    }

    public function getPrices(): array
    {
        return $this->prices;
    }

    public function isOffering($goods): bool
    {
        if (isset($this->currentNeeds)) {
            if ($this->currentNeeds->getSupply($goods) === null) {
                return false;
            }
        }

        if ($this->getInventoryAmount($goods) > 0) {
            return true;
        }

        return false;
    }

    public function getSupply($getGoods): ?Order
    {
        if (isset($this->currentNeeds)) {
            return $this->currentNeeds->getSupply($getGoods);
        }

        return null;
    }

    public function isDemanding($goods): bool
    {
        if (isset($this->currentNeeds)) {
            return $this->currentNeeds->getDemand($goods) !== null;
        }

        return false;
    }

    public function getDemand($getGoods): ?Order
    {
        if (isset($this->currentNeeds)) {
            return $this->currentNeeds->getDemand($getGoods);
        }

        return null;
    }

    public function setPrice(string $goods, float $price): void
    {
        if ($price <= 0) {
            throw new \InvalidArgumentException('Price must be greater than 0');
        }

        $this->prices[$goods] = $price;
    }

    public function getPrice(string $goods): float
    {
        if (!isset($this->prices[$goods])) {
            $this->prices[$goods] = 1;
        }

        return $this->prices[$goods];
    }

    protected function decreasePrice(string $goods, float $factor = 0.1): void
    {
        $this->setPrice($goods, $this->priceCalculator->changePrice($this->getPrice($goods), -$factor));
    }

    public function getNeeds(): ?Needs
    {
        return $this->currentNeeds;
    }

    protected function increasePrice(string $goods, float $factor = 0.1): void
    {
        $this->setPrice($goods, $this->priceCalculator->changePrice($this->getPrice($goods), $factor));
    }

    public function getInventory(): array
    {
        return $this->inventory;
    }

    public function getInventoryAmount(string $goods): int
    {
        return $this->inventory[$goods] ?? 0;
    }

    public function addInventory(string $goods, int $amount): void
    {
        if (!isset($this->inventory[$goods])) {
            $this->inventory[$goods] = 0;
        }

        $this->inventory[$goods] += $amount;

        if (isset($this->targetStorage[$goods])) {
            if ($this->inventory[$goods] > $this->targetStorage[$goods]) {
                $this->overstock($goods);
            }
        }
    }

    public function removeInventory(string $goods, int $amount): void
    {
        if (!isset($this->inventory[$goods])) {
            $this->inventory[$goods] = 0;
        }

        if ($this->inventory[$goods] < $amount) {
            $amount = $this->inventory[$goods];
            $this->understock($goods);
        }

        $this->inventory[$goods] -= $amount;
    }

    public function setInventory(string $goods, int $amount): void
    {
        $this->inventory[$goods] = $amount;
    }

    public function fulfillNeeds()
    {
        if (!isset($this->currentNeeds)) {
            return;
        }

        foreach ($this->currentNeeds->getDemands() as $demand) {

            $max = 20;
            /** @var Order $demand */
            while ($demand->getAmount() > 0 && $max > 0) {
                $max -= 1;

                if ($demand->wasFulfilled())
                    continue;

                if ($demand->getType() === Order::CHEAPEST) {
                    $supplierOrder = $this->simulation->findCheapestSupplier($demand);
                } else if ($demand->getType() === Order::MOST_EXPENSIVE) {
                    $supplierOrder = $this->simulation->findMostExpensiveSupplier($demand);
                } else {
                    $supplierOrder = null;
                }

                if ($supplierOrder !== null) {
                    $supply = $supplierOrder->getOrder();
                    $building = $supplierOrder->getBuilding();

                    $amount = min($this->getMoney() / $supply->getPrice(), $demand->getAmount(), $supply->getAmount(), $building->getInventoryAmount($supply->getGoods()));

                    if ($amount == 0) {
                        break;
                    }

                    $this->removeMoney($supply->getPrice() * $amount);
                    $supply->fulfill($amount);
                    $demand->fulfill($amount);
                    $building->addMoney($supply->getPrice() * $amount);
                    $building->removeInventory($supply->getGoods(), $amount);
                    $this->addInventory($supply->getGoods(), $amount);
                } else {
                    break;
                }

            }
        }
    }

    abstract public function placeOrders(): Needs;
    abstract public function validate(): void;
    abstract public function work(): void;

    protected function understock(string $goods)
    {
        $this->decreasePrice($goods);
    }

    protected function overstock(string $goods)
    {
        $this->increasePrice($goods);
    }

    protected function getAmountToTarget(string $goods, int $default = 0): int
    {
        $targetStorage = $this->getTargetStorage($goods);
        $inventoryAmount = $this->getInventoryAmount($goods);

        if ($targetStorage > $inventoryAmount) {
            return $targetStorage - $inventoryAmount;
        }

        return $default;
    }

    public function terminate(): bool
    {
        return $this->getMoney() == 0;
    }

    protected function getAmountToBuy(string $goods): int
    {
        $targetStorage = $this->getTargetStorage($goods);
        $inventoryAmount = $this->getInventoryAmount($goods);

        if ($targetStorage > $inventoryAmount) {
            return min($targetStorage - $inventoryAmount, (int)$this->getMoney()/ $this->getPrice($goods));
        }

        return 0;
    }

    public function isInventoryFull(string $goods): bool
    {
        return $this->getAmountToTarget($goods) === 0;
    }
    public function isInventoryEmpty(string $goods): bool
    {
        return $this->getInventoryAmount($goods) === 0;
    }

    public function assumeTick() {
        $this->lastNeeds = $this->currentNeeds;
        $this->currentNeeds = $this->placeOrders();
    }

    private function getTargetStorage(string $goods)
    {
        return $this->targetStorage[$goods] ?? 0;
    }

    public function setTargetStorage(string $goods, int $amount): void
    {
        $this->targetStorage[$goods] = $amount;
    }

    public function setMinPrice(string $goods, float $price, float $factor = 1): void
    {
        if ($price == 0) {
            return;
        }

        $price = $this->priceCalculator->changePrice($price, -$factor);

        if ($this->getPrice($goods) < $price) {
            $this->setPrice($goods, $price);
        }
    }

    public function setMaxPrice(string $goods, float $price, float $factor = 1): void
    {
        if ($price == 0) {
            return;
        }

        $price = $this->priceCalculator->changePrice($price, $factor);

        if ($this->getPrice($goods) > $price) {
            $this->setPrice($goods, $price);
        }
    }


    protected function getIncome(string $goods)
    {
        if ($this->currentNeeds->getSupply($goods) == null)
            return 0;

        return $this->currentNeeds->getSupply($goods)->getFulfilledAmount() * $this->getPrice($goods);
    }

    protected function getOutcome(string $goods)
    {
        if ($this->currentNeeds->getDemand($goods) == null)
            return 0;

        return $this->currentNeeds->getDemand($goods)->getFulfilledAmount() * $this->getPrice($goods);
    }
}