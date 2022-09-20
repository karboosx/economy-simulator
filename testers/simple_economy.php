<?php

require_once __DIR__ . '/../vendor/autoload.php';

$numberOfHouses = 10;
$numberOfFarms = 10;

$theoreticalFoodDemand = $numberOfHouses;
$theoreticalFoodSupply = $numberOfFarms;
$theoreticalWorkForceDemand = $numberOfFarms;
$theoreticalWorkForceSupply = $numberOfHouses;

$foodPrice = $_GET['foodPrice'] ?? 5;
$foodDemand = 0;
$foodSupply = 0;

$workForcePrice = $_GET['workForce'] ?? 9;
$workForceDemand = 0;
$workForceSupply = 0;

$priceCalculator = new \Karbo\Economy\PriceCalculator();

$history = [];
$strengthFood = 1;
$strengthWorkForce = 1;
// print the new prices
?>
<html>
<head>
    <title>Simple Economy</title>
    <style>
        table {
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }

        .collapsed {
            display: none;
        }
    </style>
</head>
<body>
<h1>Simple Economy</h1>
<div>
    <table class="collapsed">
        <tr>
            <th>Year</th>
            <th>Resource</th>
            <th>Price</th>
            <th>Demand</th>
            <th>Supply</th>
            <th>Price factor</th>
            <th>New price</th>
        </tr>
        <?php
        for ($i = 0; $i < 50; $i++) {
            $foodPriceFactor = $priceCalculator->calculatePriceFactor($foodDemand, $foodSupply);
            $workForcePriceFactor = $priceCalculator->calculatePriceFactor($workForceDemand, $workForceSupply);

            $foodPrice = $foodPrice + $foodPriceFactor * $foodPrice;
            $workForcePrice = $workForcePrice + $workForcePriceFactor * $workForcePrice;
            ?>
            <tr>
                <td><?= $i ?></td>
                <td>Food</td>
                <td><?= round($foodPrice, 2) ?></td>
                <td><?= $foodDemand ?></td>
                <td><?= $foodSupply ?></td>
                <td><?= round($foodPriceFactor, 3) ?></td>
                <td><?= round($foodPrice, 2) ?></td>
            </tr>
            <tr>
                <td><?= $i ?></td>
                <td>Work force</td>
                <td><?= round($workForcePrice, 2) ?></td>
                <td><?= $workForceDemand ?></td>
                <td><?= $workForceSupply ?></td>
                <td><?= round($workForcePriceFactor, 3) ?></td>
                <td><?= round($workForcePrice, 2) ?></td>
            </tr>
            <tr>
                <td colspan="7">
                    <?php
                    // if food price is too low, people will buy more food
                    $foodDemand = 0;
                    $foodSupply = 0;
                    $workForceDemand = 0;
                    $workForceSupply = 0;

                    // process behavior of houses
                    for ($j = 0; $j < $numberOfHouses; $j++) {
                        if ($foodPrice < $workForcePrice) {
                            $foodDemand+=1;
                        }

                        if ($workForcePrice > $foodPrice) {
                            $workForceSupply+=1;
                        }
                    }

                    // process behavior of farms
                    for ($j = 0; $j < $numberOfFarms; $j++) {
                        if ($foodPrice > $workForcePrice) {
                            $foodSupply+=1;
                        }

                        if ($workForcePrice < $foodPrice) {
                            $workForceDemand+=1;
                        }
                    }
                    ?>
                </td>
            </tr>
            <?php

            // save current state for history
            $history[] = [
                'year' => $i,
                'foodPrice' => $foodPrice,
                'foodDemand' => $foodDemand,
                'foodSupply' => $foodSupply,
                'workForcePrice' => $workForcePrice,
                'workForceDemand' => $workForceDemand,
                'workForceSupply' => $workForceSupply,
            ];
        }
        ?>

    </table>

    <h2>Food nad work force price graph</h2>
    <img src="https://quickchart.io/chart?c=<?= urlencode(json_encode([
        'type' => 'line',
        'data' => [
            'labels' => array_column($history, 'year'),
            'datasets' => [
                [
                    'label' => 'Food price',
                    'data' => array_column($history, 'foodPrice'),
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'lineTension' => 0.1
                ],
                [
                    'label' => 'Work force price',
                    'data' => array_column($history, 'workForcePrice'),
                    'fill' => false,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'lineTension' => 0.1
                ],
            ]
        ]
    ])) ?>">

    <h2>Food supply and demand graph</h2>
    <img src="https://quickchart.io/chart?c=<?= urlencode(json_encode([
        'type' => 'line',
        'data' => [
            'labels' => array_column($history, 'year'),
            'datasets' => [
                [
                    'label' => 'Food supply',
                    'data' => array_column($history, 'foodSupply'),
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'lineTension' => 0.1
                ],
                [
                    'label' => 'Food demand',
                    'data' => array_column($history, 'foodDemand'),
                    'fill' => false,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'lineTension' => 0.1
                ],
            ]
        ]
    ])) ?>">

    <h2>Work force supply and demand graph</h2>
    <img src="https://quickchart.io/chart?c=<?= urlencode(json_encode([
        'type' => 'line',
        'data' => [
            'labels' => array_column($history, 'year'),
            'datasets' => [
                [
                    'label' => 'Work force supply',
                    'data' => array_column($history, 'workForceSupply'),
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'lineTension' => 0.1
                ],
                [
                    'label' => 'Work force demand',
                    'data' => array_column($history, 'workForceDemand'),
                    'fill' => false,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'lineTension' => 0.1
                ],
            ]
        ]
    ])) ?>">


</body>
</html>
