<?php

namespace App\Services\Providers;

class ZionServicios
{
    public function id()
    {
        return 'id';
    }

    public function balance()
    {
        return 'balance';
    }

    public function transaction($data, $user, $userConfig, $brand, $product)
    {
        $this->data = $data;
        $this->user = $user;
        $this->userConfig = $userConfig;
        $this->brand = $brand;
        $this->product = $product;

        return 'transaction';
    }

    public function find()
    {
        return 'find';
    }
}
