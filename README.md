# LARAVEL SHOP (Laravel 5 Package)

Laravel Shop is flexible way to add shop functionality to **Laravel 5**. Aimed to be the e-commerce solution for artisans.

Laravel shop adds shopping cart, orders and payments to your new or existing project; letting you transform any model into a shoppable item.

## Contents

- [Scope](#scope)
- [Installation](#installation)
- [Configuration](#configuration)
    - [Database Setup](#database-setup)
    - [Models Setup](#models)
        - [Shop](#shop)
        - [Item](#item)
        - [Cart](#cart)
        - [User](#user)
        - [Existing Model Conversion](#existing-model-conversion)
        		- [Item Name](#item-name)
        		- [Item Url](#item-url)
        - [Dump Autoload](#dump-autoload)
- [Usage](#usage)
    - [Cart](#cart)
        - [Adding Items](#adding-items)
        - [Removing Items](#removing-items)
        - [Displaying](#removing-items)
    - [Item](#cart)
- [License](#license)
- [Additional Information](#additional-information)

## Scope

Current version includes:

- Shop Item (transforms existing models into shoppable items that can be added to cart and orders)
- Cart

Under development:

- Order
- Payment gateways
- Coupons
- Transactions

On the horizon:

- Product and variations solution
- Backend dashboard
- Frontend templates

## Installation

In order to install Laravel Shop, just add 

    "amsgames/laravel-shop": "dev-dev"

to your composer.json. Then run `composer install` or `composer update`.

Then in your `config/app.php` add 

    'Amsgames\LaravelShop\LaravelShopProvider'
    
in the `providers` array.

Then add

    'Shop'      => 'Amsgames\LaravelShop\LaravelShopFacade',
    
in the `aliases` array.

Note: This project is still in development stage, we recommend to use this package once a release version is in place.

## Configuration

Set the configuration values in the `config/auth.php` file. This package will use them to refer to the user table and model.

Publish the configuration for this package to further customize table names, model namespaces, currencies and other values. Run the following command:

```bash
php artisan laravel-shop:publish
```

A `shop.php` file will be created in your app/config directory.

### Database Setup

Generate package migration file:

```bash
php artisan laravel-shop:migration
```

The command below will generate a new migration file with database commands to create the cart and item tables. The file will be located in `database/migrations`. Add additional fields if needed to fill your software needs.

Create schema in database: 

```bash
php artisan migrate
```

### Models

The following models must be created for the shop to function, these models can be customizable to fill your software needs.

#### Item

Create a Item model:

```bash
php artisan make:model Item
```

This will create the model file `app/Item.php`, edit it and make it look like (take in consideration your app's namespace):

```php
<?php

namespace App;

use Amsgames\LaravelShop\Models\ShopItemModel;

class Item extends ShopItemModel
{
}
```

The `Item` model has the following main attributes:
- `sku` &mdash; Stock Keeping Unit, aka your unique product identification within your store.
- `price` &mdash; Item price.
- `tax` &mdash; Item tax. Defaulted to 0.
- `shipping` &mdash; Item shipping. Defaulted to 0.
- `currecy` &mdash; Current version of package will use USD as default.
- `quantity` &mdash; Item quantity.
- `class` &mdash; Class reference of the model being used as shoppable item. Optional when using array data.
- `reference_id` &mdash; Id reference of the model being used as shoppable item. Optional when using array data.
- `user_id` &mdash; Owner.
- `displayPrice` &mdash; Price value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayTax` &mdash; Tax value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayShipping` &mdash; Tax value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `created_at` &mdash; When the item record was created in the database.
- `updated_at` &mdash; Last time when the item was updated.

Business definition: Item used as a **cart item** or an **order item**.

#### Cart

Create a Cart model:

```bash
php artisan make:model Cart
```

This will create the model file `app/Cart.php`, edit it and make it look like (take in consideration your app's namespace):

```php
<?php

namespace App;

use Amsgames\LaravelShop\Models\ShopCartModel;

class Cart extends ShopCartModel 
{
}
```

The `Item` model has the following main attributes:
- `user_id` &mdash; Owner.
- `count` &mdash; Total amount of items in cart.
- `totalPrice` &mdash; Total price from all items in cart.
- `totalTax` &mdash; Total tax from all items in cart, plus global tax set in config.
- `totalShipping` &mdash; Total shipping from all items in cart.
- `total` &mdash; Total amount to be charged, sums total price, total tax and total shipping.
- `displayTotalPrice` &mdash; Total price value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayTotalTax` &mdash; Total tax value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayTotalShipping` &mdash; Total shipping value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayTotal` &mdash; Total amount value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `created_at` &mdash; When the cart record was created in the database.
- `updated_at` &mdash; Last time when the cart was updated.

#### User

Use the `ShopUserTrait` trait in your existing `User` model. By adding `use Amsgames\LaravelShop\Traits\ShopUserTrait` and `use ShopUserTrait` like in the following example:

```php
<?php

use Amsgames\LaravelShop\Traits\ShopUserTrait;

class User extends Model {

	use Authenticatable, CanResetPassword, ShopUserTrait;

}
```

This will enable the relation with `Cart` and shop needed methods.

#### Existing Model Conversion

Laravel Shop package lets you convert any existing `Eloquent` model to a shoppable item that can be used within the shop without sacrificing any existing functionality. This feature will let the model be added to carts or orders. The will require two small steps:

Use the `ShopItemTrait` in your existing model. By adding `use Amsgames\LaravelShop\Traits\ShopItemTrait` and `use ShopItemTrait` like in the following example:

```php
<?php

use Amsgames\LaravelShop\Traits\ShopItemTrait;

class MyCustomProduct extends Model {

	use ShopItemTrait;

	// MY METHODS AND MODEL DEFINITIONS........

}
```

Add `sku` (string) and `price` (decimal, 20, 2) fields to your model's table. You can also include `name` (string) and `tax` (decimal, 20, 2), although these are optional. You can do this by creating a new migration:

```bash
php artisan make:migration alter_my_table
```

Define migration to look like the following example:

```php
<?php

class AlterMyTable extends Migration {

	public function up()
	{
		Schema::table('MyCustomProduct', function($table)
		{
			$table->string('sku')->after('id');
			$table->decimal('price', 20, 2)->after('sku');
			$table->index('sku');
			$table->index('price');
		});
	}

	public function down()
	{
		// Restore type field
		Schema::table('MyCustomProduct', function($table)
		{
			$table->dropColumn('sku');
			$table->dropColumn('price');
		});
	}

}
```

Run the migration:

```bash
php artisan migrate
```

##### Item name
By default, Laravel Shop will look for the `name` attribute to define the item's name. If your exisintg model has a different attribute assigned for the name, simply define it in a property within your model:

```php
<?php

use Amsgames\LaravelShop\Traits\ShopItemTrait;

class MyCustomProduct extends Model {

	use ShopItemTrait;

	/**
	 * Custom field name to define the item's name.
	 * @var string
	 */
	protected $itemName = 'product_name';

	// MY METHODS AND MODEL DEFINITIONS........

}
```

##### Item url
You can define the URL attribute of the item by setting `itemRouteName` and `itemRouteParams` class properties. In the following example the url defined to show the product's profile is `product/{slug}`, the following changes must be applied to the model:

```php
<?php

use Amsgames\LaravelShop\Traits\ShopItemTrait;

class MyCustomProduct extends Model {

	use ShopItemTrait;

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
    protected $itemRouteParams = ['slug'];

	// MY METHODS AND MODEL DEFINITIONS........

}
```

#### Dump Autoload
Dump composer autoload

```bash
composer dump-autoload
```

**And you are ready to go.**

## Usage

### Shop
Shop methods to consider:

Format prices or other values to the price format specified in config:
```php
$formatted = Shop::format(9.99);
// i.e. this will return $9.99 or the format set in the config file.
```

### Cart
Carts are created per user in the database, this means that a user can have his cart saved when logged out and when he switches to a different device.

Let's start by calling or creating the current user's cart:

```php
$cart = Cart::current();
```

Note: Laravel Shop doen not support guest at the moment.

Get the cart of another user:

```php
$userId = 1;

$cart = Cart::findByUser($userId);
```

#### Adding Items

Lest add one item of our test and existing model `MyCustomProduct`:

```php
$cart = Cart::current()->add(MyCustomProduct::find(1));
```

By default the add method will set a quantity of 1.

Instead lets add 3 `MyCustomProduct`;

```php
$cart = Cart::current();

$cart->add(MyCustomProduct::find(1), 3);
```

Only one item will be created per sku in the cart. If an item of the same `sku` is added, just on item will remain but its quantity will increase:

```php
// Adds 1 MyCustomProduct of id 1. Item quantity = 1
$cart->add(MyCustomProduct::find(1));

// Adds 3 MyCustomProduct of id 1. Item quantity = 4
$cart->add(MyCustomProduct::find(1), 3);

// Adds 2 MyCustomProduct of id 1. Item quantity = 6
$cart->add(MyCustomProduct::find(1), 2);

print_r( [ count( $cart->items ), $cart->items[0]->quantity ] );  // Will print 1 as items count and 6 as quantity.

// Adds 2 MyCustomProduct of sku 99.
$cart->add(MyCustomProduct::findBySKU(99), 2);

echo count( $cart->items ) ;  // Will print 2 since we added a new item with sku 99 to the cart.
```

We can reset the quantity of an item to a given value:

```php
// Adds 3 MyCustomProduct of id 1. Item quantity = 3
$cart->add(MyCustomProduct::find(1), 3);

// Adds 4 MyCustomProduct of id 1. Item quantity = 4
$cart->add(MyCustomProduct::find(1), 4, $forceReset = true);

echo count( $cart->items[0]->quantity ) ;  // Echos 4
```


#### Adding Unexistent Model Items
You can add unexistent items by inserting them as arrays, each array must contain `sku` and `price` keys:

```php
// Adds unexistent item model PROD0001
$cart->add(['sku' => 'PROD0001', 'price' => 9.99]);

// Add 4 items of SKU PROD0002
$cart->add(['sku' => 'PROD0002', 'price' => 29.99], 4);
```

#### Removing Items
Lest remove our test and existing model `MyCustomProduct` from cart:

```php
$cart = Cart::current()->remove(MyCustomProduct::find(1));
```

The example below will remove the item completly, but it is possible to only remove a certain quantity from the cart:

```php
// Removes 2 MyCustomProduct from cart
$cart->remove(MyCustomProduct::find(1), 2);
```

Arrays can be used to remove unexistent model items:

```php
// Removes unexistent item model PROD0001
$cart->remove(['sku' => 'PROD0001']);
```

#### Methods

```php
// Checks if cart has item with SKU "PROD0001"
$success = $cart->hasItem('PROD0001');
```

#### Displaying

Hew is an example of how to display the cart in a blade template:

```html
<table>
	@foreach ($cart->items as $item) {
		<tr>
			<td>{{ $item->sku }}</td>
			<td>{{ $item->displayName }}</td>
			<td>{{ $item->price }}</td>
			<td>{{ $item->displayPrice }}</td>
			<td>{{ $item->tax }}</td>
			<td>{{ $item->quantity }}</td>
			<td>{{ $item->shopUrl }}</td>
		</tr>
	@endforeach
</table>
```

Attributes to consider:
- `displayPrice` &mdash; Price formatted according to the package's config file.
- `displayName` &mdash; Based on the model's item name property. Empty string for array items.
- `shopUrl` &mdash; Based on the model's item route property. '#' string for array items.

### Item

Models or arrays inserted in a cart or order are converted into SHOP ITEMS, model `Item` is used instead within the shop.

Model objects can be retrieved from a SHOP ITEM:

```php
// Lets assume that the first Cart item is MyCustomProduct.
$item = $cart->items[0];

// Check if item has model
if ($item->hasObject) {
	$myproduct = $item->object;
}
```

`$item->object` is `MyCustomProduct` model already loaded, we can access its properties and methods directly, like:

```php
// Assuming MyCustomProduct has a types relationship.
$item->object->types;

// Assuming MyCustomProduct has myAttribute attribute.
$item->object->myAttribute;
```

The following shop methods apply to model `Item` or exiting models that uses `ShopItemTrait`:

```php
$item = Item::findBySKU('PROD0001');

$item = MyCustomProduct::findBySKU('PROD0002');

// Quering
$item = Item::whereSKU('PROD0001')->where('price', '>', 0)->get();
```

## License

Entrust is free software distributed under the terms of the MIT license.

## Additional Information

This package's architecture and design was inpired by the **Zizaco/entrust** package, we'll like to thank their contributors for their awesome woek.