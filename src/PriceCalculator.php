<?php

namespace Karbo\Economy;

class PriceCalculator
{
    public function calculatePriceFactor($demand, $supply, $changeFactor = 1.0)
    {
        $demand = 1 + $demand;
        $supply = 1 + $supply;
        $factor = ($demand - $supply) / $supply;

        // normalized sigmoid function

        $factor = 1 / (1 + exp(-$factor));

        return ($factor - 0.5) * $changeFactor;
    }

    public function changePrice($price, $factor) {
        $factor = 1 / (1 + exp(-$factor));

        return $price + $price * ($factor - 0.5);
    }
}