<?php

namespace Karbo\Economy;

class House extends Building
{
    private $population = 2;

    public function placeOrders(): Needs
    {
        $needs = new Needs();

        $needs->setDemand('food',$this->population, $this->getPrice('food'))->searchCheapest();
        $needs->setSupply('work_force', $this->population, $this->getPrice('work_force'));

        $this->setInventory('work_force', $this->population);

        return $needs;
    }

    public function work(): void
    {
        $this->removeInventory('food', $this->population);
    }

    private int $starvation = 0;

    public function validate(): void
    {
        if ($this->currentNeeds == null)
            return;

        if ($this->getIncome('work_force') < 0.01) {
            $this->decreasePrice('work_force');
        }

        if ($this->getOutcome('work_force') < $this->getIncome('food')) {
            if ($this->simulation->getDemand('food')) {
                $this->increasePrice('food');
            }
        }

        $foodDemand = $this->currentNeeds->getDemand('food');
        $workSupply = $this->currentNeeds->getSupply('work_force');

        if ($foodDemand !== null) {
            if ($foodDemand->noOneFulfilledIt()) {
                $this->starvation++;
            }else {
                $this->starvation = 0;
            }
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
                $this->increasePrice('work_force');
            }
        }

        if ($this->simulation->getBestDemandPrice('work_force') > $this->getPrice('work_force')) {
            $this->setPrice('work_force', $this->simulation->getBestDemandPrice('work_force', -2));
        }

        $maxFoodPrice = $this->getMoney() / $this->population;

        if ($this->getPrice('food') > $maxFoodPrice && $maxFoodPrice > 0) { // todo sprawdzic czemu sie wykrzacza jak $maxFoodPrice = 0
            $this->setPrice('food', $maxFoodPrice);
        }

        $this->setMinPrice('food', 0.01);

        $bestSupplyPrice = $this->simulation->getBestSupplyPrice('food');
        if ($this->getPrice('food') > $bestSupplyPrice && $bestSupplyPrice > 0) { // todo sprawdzic czemu sie wykrzacza jak $maxFoodPrice = 0
            $this->setPrice('food', $bestSupplyPrice);
        }

        if ($this->getPrice('work_force') < $this->simulation->getBestDemandPrice('work_force')) {
            $this->increasePrice('work_force');
        }



        if ($this->starvation >= 2) {
            $this->increasePrice('food');
            $this->decreasePrice('work_force');
        } else {
            $this->setMinPrice('work_force', $this->getPrice('food'));
            $this->setMaxPrice('food', $this->getMoney() / $this->population / 3);
            $this->setMaxPrice('food', $this->getPrice('work_force') / $this->population);
        }
    }
}