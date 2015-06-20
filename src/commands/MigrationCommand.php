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

        $this->line('');
        $this->info( "Tables: $cartTable, $itemTable" );

        $message = "A migration that creates '$cartTable', '$itemTable'".
        " tables will be created in database/migrations directory";

        $this->comment($message);
        $this->line('');

        if ($this->confirm("Proceed with the migration creation? [Yes|no]", "Yes")) {

            $this->line('');

            $this->info("Creating migration...");
            if ($this->createMigration($cartTable, $itemTable)) {

                $this->info("Migration successfully created!");
            } else {
                $this->error(
                    "Couldn't create migration.\n Check the write permissions".
                    " within the database/migrations directory."
                );
            }

            $this->line('');

        }
    }

    /**
     * Create the migration.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function createMigration($cartTable, $itemTable)
    {
        $migrationFile = base_path("/database/migrations")."/".date('Y_m_d_His')."_shop_setup_tables.php";

        $usersTable  = Config::get('auth.table');
        $userModel   = Config::get('auth.model');
        $userKeyName = (new $userModel())->getKeyName();

        $data = compact('cartTable', 'itemTable', 'usersTable', 'userKeyName');

        $output = $this->laravel->view->make('laravel-shop::generators.migration')->with($data)->render();

        if (!file_exists($migrationFile) && $fs = fopen($migrationFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }
}
