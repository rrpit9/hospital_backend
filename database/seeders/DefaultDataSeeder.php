<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Business;
use Illuminate\Database\Seeder;

class DefaultDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** Creating Default Admins */
        User::factory(3)->create();

        /** Creating Default Clients */
        Client::factory(5)->create();

        /** Creating Default Business Based on Client */
        Business::factory(5)->create();

        /** Creating Default Employees */
        Employee::factory(30)->create();

        /** Creating Default Customers */
        Customer::factory(2)->create();
    }
}
