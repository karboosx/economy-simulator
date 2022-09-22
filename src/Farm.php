<?php

namespace Karbo\Economy;

class Farm extends Building
{
    private $productionRate = 4;

    public function placeOrders(): Needs
    {
        $needs = new Needs();

        $this->setTargetStorage('food', $this->simulation->getBuildingCount(House::class) * 2);
        $this->setTargetStorage('work_force', $this->simulation->getBuildingCount(House::class)*2);

        $amount = $this->getAmountToTarget('food') / $this->productionRate;

        if (!$this->isInventoryFull('food') && $amount > 0) {
            $needs->setDemand('work_force', $amount, $this->getPrice('work_force'));
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

        if ($this->getIncome('food') < 0.01) {
            $this->decreasePrice('food');
        }

        if ($this->getOutcome('work_force') < $this->getIncome('food')) {
            if ($this->simulation->getDemand('food')) {
                $this->increasePrice('food');
            }
        }

        if ($this->currentNeeds->getDemand('work_force') !== null) {
            if (!$this->currentNeeds->getDemand('work_force')->wasFulfilled()) {

                if ($this->simulation->getSupply('work_force') > 0) {
                    $this->increasePrice('work_force');
                }
            } else {
                $this->decreasePrice('work_force', 0.1);
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
                if ($this->simulation->getDemand('food') > 0) {
                    $this->decreasePrice('food');
                }
            } else {
                $this->increasePrice('food');
            }
        } else {
            $this->stagnation++;
        }

        if ($this->simulation->getBestDemandPrice('food') > $this->getPrice('food')) {
            $this->setPrice('food', $this->simulation->getBestDemandPrice('food', -1));
        }

        if ($this->stagnation > 5) {
            $this->removeInventory('food', $this->getInventoryAmount('food') / 3);
            $this->decreasePrice('food');
            $this->increasePrice('work_force');
        } else {
            $this->setMinPrice('food', $this->getPrice('work_force') / $this->productionRate);
            $this->setMaxPrice('work_force', $this->getPrice('food') * $this->productionRate);
            $this->setMaxPrice('food', $this->getMoney() / $this->productionRate);
        }

        $this->setMaxPrice('work_force', $this->getMoney() / 2 / 3);
        $this->setMinPrice('food', 0.1);

    }

    // terminate method: remove building only if there is no food in inventory
    public function terminate(): bool
    {
        if ($this->getInventoryAmount('food') > 0) {
            $this->decreasePrice('food');
        }
        $test = $this->getMoney() === 0 && $this->getInventoryAmount('food') == 0;

        if ($test) {
            return true;
        }

        return false;
    }
}