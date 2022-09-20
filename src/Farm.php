<?php

namespace Karbo\Economy;

class Farm extends Building
{
    private $productionRate = 5;

    public function placeOrders(): Needs
    {
        $needs = new Needs();
        $this->setMinPrice('food', $this->simulation->getBestSupplyPrice('work_force') / $this->productionRate);
        $this->setMaxPrice('work_force', $this->simulation->getBestDemandPrice('food') * $this->productionRate);

        $this->setTargetStorage('food', 100);
        $this->setTargetStorage('work_force', 2);

        if (!$this->isInventoryFull('food') && $this->getAmountToBuy('work_force') > 0) {
            $needs->setDemand('work_force', $this->getAmountToBuy('work_force'), $this->getPrice('work_force'))->searchCheapest();
        }

        $needs->setSupply('food', $this->getInventoryAmount('food'), $this->getPrice('food'));

        return $needs;
    }

    public function work(): void
    {
        $this->addInventory('food', $this->getInventoryAmount('work_force') * $this->productionRate);
        $this->removeInventory('work_force', $this->getInventoryAmount('work_force'));
    }

    public function validate(): void
    {
        if ($this->currentNeeds == null)
            return;

        if ($this->currentNeeds->getDemand('work_force') !== null) {
            if (!$this->currentNeeds->getDemand('work_force')->wasFulfilled()) {

                if ($this->simulation->getSupply('work_force') > 0) {
                    $this->increasePrice('work_force');
                }
            } else {
                $this->decreasePrice('work_force');
            }
        }

        if ($this->currentNeeds->getSupply('food') !== null) {
            if (!$this->currentNeeds->getSupply('food')->wasFulfilled()) {

                $sales = $this->getSales('food');
                $cost = $this->getCost('work_force');

                if ($sales < $cost) {
                    $this->increasePrice('food');
                }

                if ($this->simulation->getDemand('work_force') > 0) {
                    $this->decreasePrice('food');
                }

            } else {
                if ($this->simulation->getDemand('food') > 0) {
                    $this->increasePrice('food');
                }
            }
        }

        if ($this->getPrice('work_force') > $this->simulation->getBestSupplyPrice('work_force')) {
            $this->setPrice('work_force', $this->simulation->getBestSupplyPrice('work_force'));
        }

        if ($this->isInventoryFull('food')) {
            $this->decreasePrice('food');
        }
//
//        $maxWorkForcePrice = $this->getPrice('food') / (1+$this->getInventoryAmount('food'));
//        if ($this->getPrice('work_force') > $maxWorkForcePrice) {
//            $this->setPrice('work_force', $maxWorkForcePrice);
//        }

//
//        if ($this->getPrice('work_force') > $this->getPrice('food') * $this->productionRate) {
//            $this->setPrice('work_force', $this->getPrice('food') * $this->productionRate);
//        }
    }

    private function getSales(string $goods)
    {
        if ($this->currentNeeds->getSupply($goods) == null)
            return 0;

        return $this->currentNeeds->getSupply($goods)->getFulfilledAmount() * $this->getPrice($goods);
    }

    private function getCost(string $goods)
    {
        if ($this->currentNeeds->getDemand($goods) == null)
            return 0;

        return $this->currentNeeds->getDemand($goods)->getFulfilledAmount() * $this->getPrice($goods);
    }
}