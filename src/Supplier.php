<?php

namespace Karbo\Economy;

class Supplier
{
    private Building $building;
    private Order $order;

    public function __construct(Building $building, Order $order)
    {
        $this->building = $building;
        $this->order = $order;
    }

    public function getBuilding(): Building
    {
        return $this->building;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}