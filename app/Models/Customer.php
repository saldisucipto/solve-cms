<?php

namespace App\Models;

class Customer extends Model
{
    public $id;
    public $name;
    public $email;
    public $npwp;
    public $customer_person;
    public $coa;

    public function __construct($id, $name, $email, $npwp, $customer_person, $coa)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->npwp = $npwp;
        $this->customer_person = $customer_person;
        $this->coa = $coa;
    }
}
