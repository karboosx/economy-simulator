<?php

namespace Karbo\Economy;

class House extends Building
{
    private $population = 2;

    public function placeOrders(): Needs
    {
        $this->setMinPrice('work_force', $this->simulation->getBestSupplyPrice('food'));
        $this->setMaxPrice('food', $this->simulation->getBestDemandPrice('work_force'));

        $this->setTargetStorage('food', $this->population * 2);

        $needs = new Needs();

        if ($this->getAmountToBuy('food') > 0) {
            $needs->setDemand('food', $this->getAmountToBuy('food'), $this->getPrice('food'))->searchCheapest();
        }

        $needs->setSupply('work_force', $this->population, $this->getPrice('work_force'));

        $this->setInventory('work_force', $this->population);

        return $needs;
    }

    public function work(): void
    {
        $this->removeInventory('food', $this->population);
    }

    public function validate(): void
    {
        if ($this->currentNeeds == null)
            return;

        $foodDemand = $this->currentNeeds->getDemand('food');
        $workSupply = $this->currentNeeds->getSupply('work_force');

        if ($foodDemand !== null) {
            if (!$foodDemand->wasFulfilled()) {
                if ($this->simulation->getSupply('food') > 0) {
                    $this->increasePrice('food');
                }
            } else {
                $this->decreasePrice('food');
            }
        }

        if ($workSupply !== null) {
            if (!$workSupply->wasFulfilled()) {
                $this->decreasePrice('work_force');
            } else {
                if ($this->simulation->getDemand('work_force') > 0) {
                    $this->increasePrice('work_force');
                }
            }
        }

//        $potentialEarnings = $this->simulation->getBestSupplyPrice('work_force') * min($this->population, $this->simulation->getSupply('work_force'));
//        $potentialCost = $this->simulation->getBestDemandPrice('food') * min($this->population, $this->simulation->getDemand('food'));
//
//        if ($potentialEarnings < $potentialCost) {
//            $this->increasePrice('work_force');
//        }
//
//        if ($this->getPrice('food') > $this->simulation->getBestSupplyPrice('food')) {
//            $this->setPrice('food', $this->simulation->getBestSupplyPrice('food'));
//        }
//
//        $bestWorkForceSalary = $this->simulation->getBestSupplyPrice('work_force');
//
//        if ($bestWorkForceSalary > $this->getPrice('work_force')) {
//            $this->setPrice('work_force', $bestWorkForceSalary);
//        }
//
        $maxFoodPrice = $this->getMoney() / $this->population;

        if ($this->getPrice('food') > $maxFoodPrice) {
            $this->setPrice('food', $maxFoodPrice);
        }

//        if ($this->getPrice('work_force') < $this->simulation->getBestSupplyPrice('food')) {
//            $this->setPrice('work_force', $this->simulation->getBestSupplyPrice('food'));
//        }
//
//        if ($this->getPrice('food') > $this->simulation->getBestSupplyPrice('food')) {
//            $this->setPrice('food', $this->simulation->getBestSupplyPrice('food'));
//        }


        // starving
//        if ($this->getInventoryAmount('food') < $this->population) {
//            $this->setPrice('food', $this->simulation->getBestSupplyPrice('food'));
//        }

        // set max price for food based on current money and work force price
//        $maxFoodPrice = $this->simulation->getBestSupplyPrice('work_force') / min($this->population, $this->simulation->getSupply('work_force'));
//
//        if ($this->getPrice('food') > $maxFoodPrice) {
//            $this->setPrice('food', $maxFoodPrice);
//        }

        // set min price for work force based on current money and food price
//
//        $minWorkForcePrice = $this->getPrice('food') / $this->population;
//
//        if ($this->getPrice('work_force') < $minWorkForcePrice) {
//            $this->setPrice('work_force', $minWorkForcePrice);
//        }

//
//        $minWorkForcePrice = $this->getPrice('food') / $this->population * 0.5;
//
//        if ($this->getPrice('work_force') * $this->population < $minWorkForcePrice) {
//            $this->setPrice('work_force', $minWorkForcePrice);
//        }
    }
}