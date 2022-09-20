<?php

namespace Karbo\Economy;

class Farm extends Building
{
    private $productionRate = 2;

    public function placeOrders(): Needs
    {
        $needs = new Needs();
//        $this->setMinPrice('food', $this->simulation->getBestSupplyPrice('work_force') / $this->productionRate);
//        $this->setMaxPrice('work_force', $this->simulation->getBestDemandPrice('food') * $this->productionRate);

        //$this->setMinPrice('food', $this->simulation->getBestDemandPrice('food'));

        $this->setTargetStorage('food', 10);
        $this->setTargetStorage('work_force', 3);

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
    private int $stagnation = 0;

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

        $foodSupply = $this->currentNeeds->getSupply('food');
        if ($foodSupply !== null) {
            if ($foodSupply->noOneFulfilledIt()) {
                $this->stagnation++;
            }else {
                $this->stagnation = 0;
            }
            if (!$foodSupply->wasFulfilled()) {

                $sales = $this->getSales('food');
                $cost = $this->getCost('work_force');

                if ($sales < $cost) {
                    $this->increasePrice('food');
                }

                $this->decreasePrice('food');


            } else {
                if ($this->simulation->getDemand('food') > 0) {
                    $this->increasePrice('food');
                }
            }
        }

        if ($this->getPrice('work_force') > $this->getMoney() / $this->getAmountToBuy('work_force')) {
            $this->setPrice('work_force', $this->getMoney() / $this->getAmountToBuy('work_force'));
        }

        if ($this->isInventoryFull('food')) {
            $this->decreasePrice('food');
        }

        if ($this->simulation->getBestDemandPrice('food') > $this->getPrice('food')) {
            $this->increasePrice('food');
        }

        if ($this->stagnation > 5) {
            $this->decreasePrice('food');
            $this->increasePrice('work_force');
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