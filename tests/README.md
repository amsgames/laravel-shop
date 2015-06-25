# Test Case Configuration

In order to run test cases you must setup your laravel package development environment and create the TestProduct model.

## Create testing environment

There are multiple solutions to test this package. 

Our testing environment uses a new installation of **Laravel** and [studio](https://github.com/franzliedke/studio).

After studio is install in composer and created as dependency of the project. Laravel's `composer.json` autoload section must configured to map the paths to package's `src` and `commands` folder:

```json
"autoload": {
    "classmap": [
        "database",
        "Amsgames/laravel-shop/src/commands"
    ],
    "psr-4": {
        "App\\": "app/",
        "Amsgames\\LaravelShop\\": "Amsgames/laravel-shop/src"
    }
},
```

The `tests` directory must be added to the project's `phpunit.xml` file:
```xml
<testsuites>
    <testsuite name="Application Test Suite">
        <directory>./tests/</directory>
        <directory>./amsgames/laravel-shop/tests/</directory>
    </testsuite>
</testsuites>
```

Then be sure to setup and configure the package in the laravel's project as stated in the package's readme file.

## Gateways

Add the following test gateways the array in `shop.php` config file:

```php
'gateways' => [
    'testFail'          =>  Amsgames\LaravelShop\Gateways\GatewayFail::class,
    'testPass'          =>  Amsgames\LaravelShop\Gateways\GatewayPass::class,
    'testCallback'      =>  Amsgames\LaravelShop\Gateways\GatewayCallback::class,
],
```

## Test Product

Create the TestProduct model and database table used in the test cases:

```bash
php artisan make:model TestProduct --migration
```

This will create a model file `app\TestProduct.php` and a new migration in `database\migrations` folder.

Modify the migration to look like:

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sku');
            $table->string('name');
            $table->decimal('price', 20, 2)->nullable();
            $table->string('description', 1024)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('test_products');
    }
}
```

And the model file to look like:

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Amsgames\LaravelShop\Traits\ShopItemTrait;

class TestProduct extends Model
{
    use ShopItemTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'sku', 'description', 'price'];

    /**
    * Name of the route to generate the item url.
    *
    * @var string
    */
    protected $itemRouteName = 'product';

    /**
    * Name of the attributes to be included in the route params.
    *
    * @var string
    */
    protected $itemRouteParams = ['sku'];
		
}
```

Additionally add the following route in `app\Http\routes.php`:

```php
Route::get('/product/{sku}', ['as' => 'product', function ($sku) {
    return view('product', ['product' => App\TestProduct::findBySKU($sku)]);
}]);
```

Then add the following view, called `product.blade.php`, in `resources\views\` folder:

```php
{{ $product->id }}
```