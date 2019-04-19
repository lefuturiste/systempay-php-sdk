<?php

namespace App;

class Transaction {

    public $id;
    public $amount;
    public $currency;
    public $date;

    public function generateId() {
        $this->id = sprintf('%06d', rand(1, 899999));
    }

    public function generateDate() {
        $this->date = gmdate('YmdHis');
    }
}
