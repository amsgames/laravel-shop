<?php 

namespace Amsgames\LaravelShop;

/**
 * This file is part of LaravelShop,
 * A shop solution for Laravel.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class MigrationCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'laravel-shop:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the LaravelShop specifications.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->laravel->view->addNamespace('laravel-shop', substr(__DIR__, 0, -8).'views');

        $cartTable          = Config::get('shop.cart_table');
        $itemTable          = Config::get('shop.item_table');
        $couponTable        = Config::get('shop.coupon_table');
        $orderStatusTable   = Config::get('shop.order_status_table');
        $orderTable         = Config::get('shop.order_table');
        $transactionTable   = Config::get('shop.transaction_table');

        // Migrations
        $this->line('');
        $this->info( "Tables: $cartTable, $itemTable" );

        $message = "A migration that creates '$cartTable', '$itemTable', '$orderTable'".
        " tables will be created in database/migrations directory";

        $this->comment($message);
        $this->line('');

        if ($this->confirm('Proceed with the migration creation? [Yes|no]', 'Yes')) {

            $this->line('');

            $this->info('Creating migration...');
            if ($this->createMigration(compact(
                    'cartTable',
                    'itemTable',
                    'couponTable',
                    'orderStatusTable',
                    'orderTable',
                    'transactionTable'
                ))
            ) {

                $this->info('Migration successfully created!');
            } else {
                $this->error(
                    "Couldn't create migration.\n Check the write permissions".
                    " within the database/migrations directory."
                );
            }

        }

        // Seeder

        $this->line('');
        $this->info( "Table seeders: $orderStatusTable" );
        $message = "A seeder that seeds '$orderStatusTable' table(s) with data. Will be created in database/seeds directory";

        $this->comment($message);
        $this->line('');

        if ($this->confirm('Proceed with the seeder creation? [Yes|no]', 'Yes')) {

            $this->line('');

            $this->info('Creating seeder...');
            if ($this->createSeeder(compact(
                    'cartTable',
                    'itemTable',
                    'couponTable',
                    'orderStatusTable',
                    'orderTable',
                    'transactionTable'
                ))
            ) {
                $this->info('Seeder successfully created!');
            } else {
                $this->error(
                    "Couldn't create seeder.\n Check the write permissions".
                    " within the database/seeds directory."
                );
            }

        }
    }

    /**
     * Create the migration.
     *
     * @param array $data Data with table names.
     *
     * @return bool
     */
    protected function createMigration($data)
    {
        $migrationFile = base_path('/database/migrations') . '/' . date('Y_m_d_His') . '_shop_setup_tables.php';

        $usersTable  = Config::get('auth.table');
        $userModel   = Config::get('auth.providers.users.model');
        $userKeyName = (new $userModel())->getKeyName();

        $data = array_merge($data, compact('usersTable', 'userKeyName'));

        $output = $this->laravel->view->make('laravel-shop::generators.migration')->with($data)->render();

        if (!file_exists($migrationFile) && $fs = fopen($migrationFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }

    /**
     * Create the seeder.
     *
     * @param array $data Data with table names.
     *
     * @return bool
     */
    protected function createSeeder($data)
    {
        $seederFile = base_path('/database/seeds') . '/LaravelShopSeeder.php';

        $output = $this->laravel->view->make('laravel-shop::generators.seeder')->with($data)->render();

        if (!file_exists($seederFile) && $fs = fopen($seederFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }
}