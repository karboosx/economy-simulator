<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Karbo\Economy\Simulation;

$sim = new Simulation();
$sim->build('house');
$sim->build('house');
$sim->build('farm');


$data = [];

for ($i = 0; $i < 300; $i++) {
    $_GET['i'] = $i;

    $sim->tick();
    $stats = $sim->getStats();

    $data[] = $stats;
}

?>
<html lang="en">
    <head>
        <title>Simulation</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            .table {
                width: 100%;
            }

            .row {
                display: flex;
            }

            .column {
                flex: 1;
                width: 33%;
            }
        </style>
    </head>
    <body>
        <h1>Simulation</h1>
        <!-- table 2 rows 2 columns -->
        <div class="table">
            <div class="row">
                <div class="column">
                    <h2>Buy Prices</h2>
                    <!-- chart of food and work_force prices -->
                    <canvas id="buy_prices"></canvas>

                </div>
                <div class="column">
                    <h2>Sell Prices</h2>
                    <!-- chart of food and work_force prices -->
                    <canvas id="sell_prices"></canvas>
                </div>
                <div class="column">
                    <h2>Supply and demand</h2>
                    <!-- chart of food and work_force supply and demand -->
                    <canvas id="supply_demand"></canvas>
                </div>
            </div>
            <div class="row">
                <div class="column">
                    <h2>Inventories</h2>
                    <!-- chart of food and work_force inventory -->
                    <canvas id="inventory"></canvas>
                </div>
                <div class="column">
                    <h2>Fulfillment</h2>
                    <!-- chart of food and work_force fulfilled demand -->
                    <canvas id="fulfilled_demand"></canvas>
                </div>
                <div class="column">
                    <h2>Buildings type money</h2>
                    <canvas id="buildings_type_money"></canvas>
                </div>
            </div>
            <div class="row">
            </div>








        <script>
            function render() {
                new Chart(document.getElementById('buy_prices'), {

                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_map(function ($i) { return $i + 1; }, array_keys($data))); ?>,
                        datasets: [
                            {
                                label: 'Food price',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['buy_prices']['food']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255,99,132,1)',
                                ],
                                borderWidth: 1
                            },
                            {
                                label: 'Work force price',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['buy_prices']['work_force']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                ],
                                borderWidth: 1
                            },
                        ]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });

                new Chart(document.getElementById('sell_prices'), {

                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_map(function ($i) { return $i + 1; }, array_keys($data))); ?>,
                        datasets: [
                            {
                                label: 'Food price',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['sell_prices']['food']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255,99,132,1)',
                                ],
                                borderWidth: 1
                            },
                            {
                                label: 'Work force price',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['sell_prices']['work_force']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                ],
                                borderWidth: 1
                            },
                        ]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });

                new Chart(document.getElementById('supply_demand'), {

                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_map(function ($i) { return $i + 1; }, array_keys($data))); ?>,
                        datasets: [
                            {
                                label: 'Food supply',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['supply']['food']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255,99,132,1)',
                                ],
                                borderWidth: 1
                            },
                            {
                                label: 'Food demand',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['demand']['food']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                ],
                                borderWidth: 1
                            },
                            {
                                label: 'Work force supply',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['supply']['work_force']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(255, 206, 86, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255, 206, 86, 1)',
                                ],
                                borderWidth: 1
                            },
                            {
                                label: 'Work force demand',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['demand']['work_force']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(75, 192, 192, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(75, 192, 192, 1)',
                                ],
                                borderWidth: 1
                            },
                        ]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });

                new Chart(document.getElementById('inventory'), {

                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_map(function ($i) { return $i + 1; }, array_keys($data))); ?>,
                        datasets: [
                            {
                                label: 'Food inventory',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['inventory']['food']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255,99,132,1)',
                                ],
                                borderWidth: 1
                            },
                            {
                                label: 'Work force inventory',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['inventory']['work_force']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                ],
                                borderWidth: 1
                            },
                        ]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });

                new Chart(document.getElementById('fulfilled_demand'), {

                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_map(function ($i) { return $i + 1; }, array_keys($data))); ?>,
                        datasets: [
                            {
                                label: 'Food fulfilled demand',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['fulfilled_demand']['food']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255,99,132,1)',
                                ],
                                borderWidth: 1
                            },
                            {
                                label: 'Work force fulfilled demand',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['fulfilled_demand']['work_force']; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                ],
                                borderWidth: 1
                            },
                        ]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });

                new Chart(document.getElementById('buildings_type_money'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_map(function ($i) { return $i + 1; }, array_keys($data))); ?>,
                        datasets: [
                            {
                                label: 'Karbo\Economy\House',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['money']['Karbo\Economy\House'] ?? 0; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255,99,132,1)',
                                ],
                                borderWidth: 1
                            },
                            {
                                label: 'Karbo\Economy\Farm',
                                data: <?php echo json_encode(array_map(function ($row) { return $row['money']['Karbo\Economy\Farm'] ?? 0; }, $data)); ?>,
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                ],
                                borderWidth: 1
                            },
                        ]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            }

            setTimeout(render, 1);
        </script>
    </body>
</html>
