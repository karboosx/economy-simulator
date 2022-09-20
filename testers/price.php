<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Karbo\Economy\PriceCalculator;

if (isset($_GET['demand']) && isset($_GET['supply'])) {
    $demand = $_GET['demand'];
    $supply = $_GET['supply'];
    $price = $_GET['price'];
    $priceCalculator = new PriceCalculator();
    $priceFactor = $priceCalculator->calculatePriceFactor($demand, $supply);
    $newPrice = $price + $priceFactor * $price;
    ?>
    <div>
        <p>Price: <?= $price ?></p>
        <p>Demand: <?= $demand ?></p>
        <p>Supply: <?= $supply ?></p>
        <p>Price factor: <?= round($priceFactor, 3) ?></p>
        <p>New price: <?= round($newPrice,2) ?></p>
        <p>Price difference: <?= round($newPrice - $price, 2) ?></p>
        <div>
            <button onclick="acceptNewPrice(<?php echo round($newPrice,2) ?>)">Accept new price</button>
        </div>
    <?php
    die;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Price Calculator</title>
</head>
<body>
<!-- sliders for demand and supply -->
<div>
    <label for="demand">Demand</label>
    <input type="range" id="demand" name="demand" min="0" max="100" value="50">
    <span id="demand-value">50</span>
</div>
<div>
    <label for="supply">Supply</label>
    <input type="range" id="supply" name="supply" min="0" max="100" value="50">
    <span id="supply-value">50</span>
</div>
<div>
    <label for="price">Price</label>
    <input type="range" id="price" name="price" min="0" max="100" value="50">
    <span id="price-value">50</span>
</div>

<div>
    <div>Output</div>
    <div id="output"></div>
</div>

<script>
    const demand = document.getElementById('demand');
    const demandValue = document.getElementById('demand-value');
    demandValue.innerHTML = demand.value;
    demand.oninput = function() {
        demandValue.innerHTML = this.value;
        updateNewPrice();
    }

    const supply = document.getElementById('supply');
    const supplyValue = document.getElementById('supply-value');
    supplyValue.innerHTML = supply.value;
    supply.oninput = function() {
        supplyValue.innerHTML = this.value;
        updateNewPrice();
    }

    const price = document.getElementById('price');
    const priceValue = document.getElementById('price-value');
    priceValue.innerHTML = price.value;
    price.oninput = function() {
        priceValue.innerHTML = this.value;
        updateNewPrice();
    }

    function updateNewPrice() {
        console.log('test');
        const demand = document.getElementById('demand').value;
        const supply = document.getElementById('supply').value;
        const price = document.getElementById('price').value;
        const url = `price.php?demand=${demand}&supply=${supply}&price=${price}`;
        fetch(url)
            .then(response => response.text())
            .then(data => {
                document.getElementById('output').innerHTML = data;
            });
    }

    function acceptNewPrice(newPrice) {
        document.getElementById('price').value = newPrice;
        document.getElementById('price-value').innerHTML = newPrice;
        updateNewPrice();
    }
</script>
</body>
</html>
