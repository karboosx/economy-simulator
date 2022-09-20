<?php

namespace Tests;

use Karbo\Economy\PriceCalculator;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    public function testCalculatePriceFactor()
    {
        $priceCalculator = new PriceCalculator();
        $this->assertEquals(0, round($priceCalculator->calculatePriceFactor(100, 100), 3));
        $this->assertEquals(0.002, round($priceCalculator->calculatePriceFactor(101, 100), 3));
        $this->assertEquals(-0.002, round($priceCalculator->calculatePriceFactor(100, 101), 3));
        $this->assertEquals(0.231, round($priceCalculator->calculatePriceFactor(200, 100), 3));
        $this->assertEquals(-0.122, round($priceCalculator->calculatePriceFactor(100, 200), 3));

    }

    public function testChangePrice() {
        $priceCalculator = new PriceCalculator();
        $this->assertEquals(50, round($priceCalculator->changePrice(50, 0), 3));
        $this->assertEquals(56.123, round($priceCalculator->changePrice(50, 0.5), 3));
        $this->assertEquals(61.553, round($priceCalculator->changePrice(50, 1), 3));
        $this->assertEquals(615.529, round($priceCalculator->changePrice(500, 1), 3));
        $this->assertEquals(561.23, round($priceCalculator->changePrice(500, 0.5), 3));
        $this->assertEquals(43.877, round($priceCalculator->changePrice(50, -0.5), 3));
    }
}
