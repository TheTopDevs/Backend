<?php

namespace demo;

use demo\Methods\Eth;
use demo\Methods\Net;
use demo\Methods\Personal;
use demo\Methods\Shh;
use demo\Methods\Web3;

class Client
{
    private $client;
    private $methods = [];

    public function __construct(string $url)
    {
        $this->client = Client::factory($url);
        $this->methods = [
            'net' => new Net($this->client),
            'eth' => new Eth($this->client),
            'shh' => new Shh($this->client),
            'web3' => new Web3($this->client),
            'personal' => new Personal($this->client),
        ];
    }

    public function net(): Net
    {
        return $this->methods['net'];
    }

    public function web3(): Web3
    {
        return $this->methods['web3'];
    }

    public function shh(): Shh
    {
        return $this->methods['shh'];
    }

    public function eth(): Eth
    {
        return $this->methods['eth'];
    }

    public function personal(): Personal
    {
        return $this->methods['personal'];
    }
}
