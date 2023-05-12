<?php

namespace App\Services\Handler;

class CartHandler
{
    public $purchaseAmount;
    public $walletBalance;
    public $consumableWallet;

    public $igstRate;
    public $igstAmount;
    public $cgstRate;
    public $cgstAmount;
    public $sgstRate;
    public $sgstAmount;

    public $netPayable;
    public $onlinePayable;

    public function __construct($purchaseAmount, $useWallet = false)
    {
        $this->purchaseAmount = round($purchaseAmount, 2);
        $this->walletBalance = 0;
        $this->consumableWallet = 0;

        $this->igstRate = 0;
        $this->igstAmount = 0;

        $this->cgstRate = 9;
        $this->cgstAmount = round($this->purchaseAmount * ($this->cgstRate / 100), 2);

        $this->sgstRate = 9;
        $this->sgstAmount = round($this->purchaseAmount * ($this->cgstRate / 100), 2);

        $this->netPayable = round($this->purchaseAmount + $this->igstAmount + $this->cgstAmount + $this->sgstAmount , 2);
        $this->onlinePayable = round($this->netPayable - $this->consumableWallet , 2);
    }
}