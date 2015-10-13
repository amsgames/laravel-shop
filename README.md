LARAVEL SHOP (Laravel 5.1 Package)
--------------------------------

[![Latest Stable Version](https://poser.pugx.org/amsgames/laravel-shop/v/stable)](https://packagist.org/packages/amsgames/laravel-shop)
[![Total Downloads](https://poser.pugx.org/amsgames/laravel-shop/downloads)](https://packagist.org/packages/amsgames/laravel-shop)
[![Latest Unstable Version](https://poser.pugx.org/amsgames/laravel-shop/v/unstable)](https://packagist.org/packages/amsgames/laravel-shop)
[![License](https://poser.pugx.org/amsgames/laravel-shop/license)](https://packagist.org/packages/amsgames/laravel-shop)

Laravel Shop is flexible way to add shop functionality to **Laravel 5.1**. Aimed to be the e-commerce solution for artisans.

Laravel shop adds shopping cart, orders and payments to your new or existing project; letting you transform any model into a shoppable item.

**Supports**

![PayPal](http://kamleshyadav.com/demo_ky/eventmanagementsystem/assets/front/images/paypal.png) ![Omnipay](http://s18.postimg.org/g68f3fs09/omnipay.jpg)

## Contents

- [Scope](#scope)
- [Installation](#installation)
- [Configuration](#configuration)
    - [Database Setup](#database-setup)
    - [Models Setup](#models)
        - [Item](#item)
        - [Cart](#cart)
        - [Order](#order)
        - [Transaction](#transaction)
        - [User](#user)
        - [Existing Model Conversion](#existing-model-conversion)
    - [Dump Autoload](#dump-autoload)
    - [Payment Gateways](#payment-gateways)
        - [PayPal](#paypal)
        - [Omnipay](#omnipay)
- [Usage](#usage)
    - [Shop](#shop)
        - [Purchase Flow](#purchase-flow)
        - [Payment Gateway](#payment-gateway)
        - [Checkout](#checkout)
        - [Order placement](#exceptions)
        - [Payments](#payments)
        - [Exceptions](#order-placement)
    - [Shopping Cart](#shopping-cart)
        - [Adding Items](#adding-items)
        - [Removing Items](#removing-items)
        - [Placing Order](#placing-order)
        - [Cart Methods](#cart-methods)
        - [Displaying](#removing-items)
    - [Item](#item-1)
    - [Order](#order-1)
        - [Placing Transactions](#placing-transactions)
        - [Order Methods](#order-methods)
    - [Events](#events)
        - [Handler Example](#event-handler-example)
- [Payment Gateway Development](#payment-gateway-development)
  - [Transaction](#transaction-1)
  - [Callbacks](#callbacks)
  - [Exceptions](#exception)
- [License](#license)
- [Additional Information](#additional-information)
- [Change Log](#change-log)

## Scope

Current version includes:

- Shop Items (transforms existing models into shoppable items that can be added to cart and orders)
- Cart
- Orders
- Transactions
- Payment gateways support
- PayPal
- Events

On the horizon:

- Guest user cart
- Shipping orders
- Coupons
- Product and variations solution
- Backend dashboard
- Frontend templates

## Installation

With composer

```bash
composer require amsgames/laravel-shop
```

Or add

```json
"amsgames/laravel-shop": "0.2.*"
```

to your composer.json. Then run `composer install` or `composer update`.

Then in your `config/app.php` add 

```php
Amsgames\LaravelShop\LaravelShopProvider::class,
```
    
in the `providers` array.

Then add

```php
'Shop'      => Amsgames\LaravelShop\LaravelShopFacade::class,
```
    
in the `aliases` array.

## Configuration

Set the configuration values in the `config/auth.php` file. This package will use them to refer to the user table and model.

Publish the configuration for this package to further customize table names, model namespaces, currencies and other values. Run the following command:

```bash
php artisan vendor:publish
```

A `shop.php` file will be created in your app/config directory.

### Database Setup

Generate package migration file:

```bash
php artisan laravel-shop:migration
```

The command below will generate a new migration file with database commands to create the cart and item tables. The file will be located in `database/migrations`. Add additional fields if needed to fill your software needs.

The command will also create a database seeder to fill shop catalog of status and types.

Create schema in database: 

```bash
php artisan migrate
```

Add the seeder to `database/seeds/DatabaseSeeder.php`:

```php
class DatabaseSeeder extends Seeder
{

  public function run()
  {
    Model::unguard();

    $this->call('LaravelShopSeeder');

    Model::reguard();
  }

}
```

Run seeder (do `composer dump-autoload first`): 

```bash
php artisan db:seed
```

### Models

The following models must be created for the shop to function, these models can be customizable to fir your needs.

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
- `id` &mdash; Item id.
- `sku` &mdash; Stock Keeping Unit, aka your unique product identification within your store.
- `price` &mdash; Item price.
- `tax` &mdash; Item tax. Defaulted to 0.
- `shipping` &mdash; Item shipping. Defaulted to 0.
- `currency` &mdash; Current version of package will use USD as default.
- `quantity` &mdash; Item quantity.
- `class` &mdash; Class reference of the model being used as shoppable item. Optional when using array data.
- `reference_id` &mdash; Id reference of the model being used as shoppable item. Optional when using array data.
- `user_id` &mdash; Owner.
- `displayPrice` &mdash; Price value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayTax` &mdash; Tax value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayShipping` &mdash; Tax value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayName` &mdash; Based on the model's item name property.
- `shopUrl` &mdash; Based on the model's item route property.
- `wasPurchased` &mdash; Flag that indicates if item was purchased. This base on the status set in config file.
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
- `id` &mdash; Cart id.
- `user_id` &mdash; Owner.
- `items` &mdash; Items in cart.
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

#### Order

Create a Order model:

```bash
php artisan make:model Order
```

This will create the model file `app/Order.php`, edit it and make it look like (take in consideration your app's namespace):

```php
<?php

namespace App;

use Amsgames\LaravelShop\Models\ShopOrderModel;

class Order extends ShopOrderModel 
{
}
```

The `Order` model has the following main attributes:
- `id` &mdash; Order id or order number.
- `user_id` &mdash; Owner.
- `items` &mdash; Items in order.
- `transactions` &mdash; Transactions made on order.
- `statusCode` &mdash; Status code.
- `count` &mdash; Total amount of items in order.
- `totalPrice` &mdash; Total price from all items in order.
- `totalTax` &mdash; Total tax from all items in order, plus global tax set in config.
- `totalShipping` &mdash; Total shipping from all items in order.
- `total` &mdash; Total amount to be charged, sums total price, total tax and total shipping.
- `displayTotalPrice` &mdash; Total price value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayTotalTax` &mdash; Total tax value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayTotalShipping` &mdash; Total shipping value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `displayTotal` &mdash; Total amount value formatted for shop display. i.e. "$9.99" instead of just "9.99".
- `created_at` &mdash; When the order record was created in the database.
- `updated_at` &mdash; Last time when the order was updated.

#### Transaction

Create a Transaction model:

```bash
php artisan make:model Transaction
```

This will create the model file `app/Transaction.php`, edit it and make it look like (take in consideration your app's namespace):

```php
<?php

namespace App;

use Amsgames\LaravelShop\Models\ShopTransactionModel;

class Transaction extends ShopTransactionModel 
{
}
```

The `Order` model has the following main attributes:
- `id` &mdash; Order id or order number.
- `order` &mdash; Items in order.
- `gateway` &mdash; Gateway used.
- `transaction_id` &mdash; Transaction id returned by gateway.
- `detail` &mdash; Detail returned by gateway.
- `token` &mdash; Token for gateway callbacks.
- `created_at` &mdash; When the order record was created in the database.
- `updated_at` &mdash; Last time when the order was updated.

#### User

Use the `ShopUserTrait` trait in your existing `User` model. By adding `use Amsgames\LaravelShop\Traits\ShopUserTrait` and `use ShopUserTrait` like in the following example:

```php
<?php

use Amsgames\LaravelShop\Traits\ShopUserTrait;

class User extends Model {

	use Authenticatable, CanResetPassword, ShopUserTrait;

}
```

This will enable the relation with `Cart` and shop needed methods and attributes.
- `cart` &mdash; User's cart.
- `items` &mdash; Items (either order or cart).
- `orders` &mdash; User's orders.

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

Add `sku` (string) and `price` (decimal, 20, 2) fields to your model's table. You can also include `name` (string), `tax` (decimal, 20, 2) and `shipping` (decimal, 20, 2), although these are optional. You can do this by creating a new migration:

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

### Dump Autoload
Dump composer autoload

```bash
composer dump-autoload
```

### Payment Gateways

Installed payment gateways can be configured and added in the `gateways` array in the `shop.php` config file, like:

```php
'gateways' => [
    'paypal'            =>  Amsgames\LaravelShopGatewayPaypal\GatewayPayPal::class,
    'paypalExpress'     =>  Amsgames\LaravelShopGatewayPaypal\GatewayPayPalExpress::class,
],
```

#### PayPal

Laravel Shop comes with PayPal support out of the box. You can use PayPal's `Direct Credit Card` or `PayPal Express` payments.

To configure PayPal and know how to use the gateways, please visit the [PayPal Gateway Package](https://github.com/amsgames/laravel-shop-gateway-paypal) page. 

#### Omnipay

Install [Omnipay Gateway](https://github.com/amostajo/laravel-shop-gateway-omnipay) to enable other payment services like 2Checkout, Authorize.net, Stripe and to name a few.

You might need to get some extra understanding about how [Omnipay](https://github.com/thephpleague/omnipay) works.

## Usage

### Shop
Shop methods to consider:

Format prices or other values to the price format specified in config:
```php
$formatted = Shop::format(9.99);
// i.e. this will return $9.99 or the format set in the config file.
```

#### Purchase Flow

With Laravel Shop you can customize things to work your way, although we recommend standarize your purchase or checkout flow as following (will explain how to use the shop methods below):

![Purchase Flow](http://s12.postimg.org/zfmsz6krh/laravelshop_New_Page.png)

* (Step 1) - User views his cart.
* (Step 2) - Continues into selecting the gateway to use.
* (Step 3) - Continues into feeding the gateway selected with required information.
* (Step 4) - Checkouts cart and reviews cart before placing order.
* (Step 5) - Places order.

#### Payment Gateway

Before any shop method is called, a payment gateway must be set:

```php
// Select the gateway to use
Shop::setGateway('paypal');

echo Shop::getGateway(); // echos: paypal
```

You can access the gateway class object as well:

```php
$gateway = Shop::gateway();

echo $gateway; // echos: [{"id":"paypal"}] 
```

#### Checkout

Once a payment gateway has been selected, you can call cart to checkout like this:

```php
// Checkout current users' cart
$success = Shop::checkout();

// Checkout q specific cart
$success = Shop::checkout($cart);
```

This will call the `onCheckout` function in the payment gateway and perform validations. This method will return a bool flag indication if operation was successful.

#### Order Placement

Once a payment gateway has been selected and user has checkout, you can call order placement like:

```php
// Places order based on current users' cart
$order = Shop::placeOrder();

// Places order based on a specific cart
$order = Shop::placeOrder($cart);
```

**NOTE:** `placeOrder()` will create an order, relate all the items in cart to the order and empty the cart. The `Order` model doen't include methods to add or remove items, any modification to the cart must be done before the order is placed. Be aware of this when designing your checkout flow.

This will call the `onCharge` function in the payment gateway and charge the user with the orders' total amount. `placeOrder()` will return an `Order` model with which you can verify the status and retrieve the transactions generated by the gateway.

#### Payments

Payments are handled gateways, this package comes with PayPal out of the box.

You can use PayPal's `Direct Credit Card` or `PayPal Express` payments.

To configure PayPal and know how to use its gateways, please visit the [PayPal Gateway Package](https://github.com/amsgames/laravel-shop-gateway-paypal) page. 

#### Exceptions

If checkout or placeOrder had errores, you can call and see the exception related:
```php
// On checkout
if (!Shop::checkout()) {
  $exception = Shop::exception();
  echo $exception->getMessage(); // echos: error
}

// Placing order
$order = Shop::placeOrder();

if ($order->hasFailed) {
  $exception = Shop::exception();
  echo $exception->getMessage(); // echos: error
}
```

Critical exceptions are stored in laravel's log.

### Shopping Cart
Carts are created per user in the database, this means that a user can have his cart saved when logged out and when he switches to a different device.

Let's start by calling or creating the current user's cart:

```php
// From cart
$cart = Cart::current();
// Once a cart has been created, it can be accessed from user
$user->cart;
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
$product = MyCustomProduct::find(1);

// Adds 1
$cart->add($product);

// Adds 3
$cart->add($product, 3);

// Adds 2
$cart->add($product, 2);

echo $cart->count; // echos: 6

$second_product = MyCustomProduct::findBySKU('TEST');

// Adds 2 of product 'TEST'
$cart->add($second_product, 2);

// Count based on quantity
echo $cart->count; // echos: 8

// Count based on products
echo $cart->items->count(); // echos: 2
```

We can reset the quantity of an item to a given value:

```php
// Add 3
$cart->add($product, 3);

echo $cart->count; // echos: 3

// Reset quantity to 4
$cart->add($product, 4, $forceReset = true);

echo $cart->count; // echos: 4
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
$product = MyCustomProduct::find(1);

// Remove the product from cart
$cart = Cart::current()->remove($product);
```

The example below will remove the item completly, but it is possible to only remove a certain quantity from the cart:

```php
// Removes only 2 from quantity
// If the quantity is greater than 2, then 1 item will remain in cart
$cart->remove($product, 2);
```

Arrays can be used to remove unexistent model items:

```php
// Removes by sku
$cart->remove(['sku' => 'PROD0001']);
```

To empty cart:

```php
$cart->clear();
```

These methods can be chained:

```php
$cart->add($product, 5)
    ->add($product2)
    ->remove($product3)
    ->clear();
```

#### Cart Methods

```php
// Checks if cart has item with SKU "PROD0001"
$success = $cart->hasItem('PROD0001');
```

#### Placing Order

You can place an order directly from the cart without calling the `Shop` class, although this will only create the order record in the database and no payments will be processed. Same ad when using `Shop`, the cart will be empty after the order is placed.

```php
// This will create the order and set it to the status in configuration
$order = $cart->placeOrder();
```

Status can be forced in creation as well:
```php
$order = $cart->placeOrder('completed');
```

#### Displaying

Hew is an example of how to display the cart in a blade template:

Items count in cart:

```html
<span>Items in cart: {{ $cart->count }}</span>
```

Items in cart:

```html
<table>
	@foreach ($cart->items as $item) {
		<tr>
			<td>{{ $item->sku }}</td>
			<td><a href="{{ $item->shopUrl }}">{{ $item->displayName }}</a></td>
			<td>{{ $item->price }}</td>
			<td>{{ $item->displayPrice }}</td>
			<td>{{ $item->tax }}</td>
			<td>{{ $item->quantity }}</td>
			<td>{{ $item->shipping }}</td>
		</tr>
	@endforeach
</table>
```

Cart amount calculations:

```html
<table>

	<tbody>
		<tr>
			<td>Subtotal:</td>
			<td>{{ $cart->displayTotalPrice }}</td>
            <td>{{ $cart->totalPrice }}</td>
		</tr>
		<tr>
			<td>Shipping:</td>
			<td>{{ $cart->displayTotalShipping }}</td>
		</tr>
		<tr>
			<td>Tax:</td>
			<td>{{ $cart->displayTotalTax }}</td>
		</tr>
	</tbody>

	<tfoot>
		<tr>
			<th>Total:</th>
			<th>{{ $cart->displayTotal }}</th>
            <th>{{ $cart->total }}</th>
		</tr>
	</tfoot>

</table>
```

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

### Order
Find a specific order number:

```php
$order = Order::find(1);
```

Find orders form user:

```php
// Get orders from specific user ID.
$orders = Order::findByUser($userId);
// Get orders from specific user ID and status.
$canceled_orders = Order::findByUser($userId, 'canceled');
```

#### Placing Transactions

You can place a transaction directly from the order without calling the `Shop` class, although this will only create the transaction record in the database and no payments will be processed.

```php
// This will create the order and set it to the status in configuration
$transaction = $order->placeTransaction(
		$gateway 				= 'my_gateway',
		$transactionId 	= 55555,
		$detail 				= 'Custom transaction 55555'
);
```

#### Order Methods

```php
$completed = $order->isCompleted
// Checks if order is in a specific status.
$success = $order->is('completed');

// Quering
// Get orders from specific user ID.
$orders = Order::whereUser($userId)->get();
// Get orders from specific user ID and status.
$completed_orders = Order::whereUser($userId)
		->whereStatus('completed')
		->get();
```

#### Order Status Codes

Status codes out of the box:
- `in_creation` &mdash; Order status in creation. Or use `$order->isInCreation`.
- `pending` &mdash; Pending for payment. Or use `$order->isPending`.
- `in_process` &mdash; In process of shipping. In process of revision. Or use `$order->isInProcess`.
- `completed` &mdash; When payment has been made and items were delivered to client. Or use `$order->isCompleted`.
- `failed` &mdash; When payment failed. Or use `$order->hasFailed`.
- `canceled` &mdash; When an order has been canceled by the user. Or use `$order->isCanceled`.

You can use your own custom status codes. Simply add them manually to the `order_status` database table or create a custom seeder like this:

```php
class MyCustomStatusSeeder extends Seeder
{

  public function run()
  {

    DB::table('order_status')->insert([
		    [
		    		'code' 				=> 'my_status',
		    		'name' 				=> 'My Status',
		    		'description' => 'Custom status used in my shop.',
		    ],
		]);

  }
}
```

Then use it like:

```php
$myStatusCode = 'my_status';

if ($order->is($myStatusCode)) {
	echo 'My custom status work!';
}
```

### Events

Laravel Shop follows [Laravel 5 guidelines](http://laravel.com/docs/5.1/events) to fire events, create your handlers and listeners like you would normally do to use them.

| Event  | Description | Data passed |
| ------------- | ------------- | ------------- |
| Cart checkout | Event fired after a shop has checkout a cart. | `id` - Cart Id `success` - Checkout result (boolean) |
| Order placed | Event fired when an order has been placed. | `id` - Order Id |
| Order completed | Event fired when an order has been completed. | `id` - Order Id |
| Order status changed | Event fired when an order's status has been changed. | `id` - Order Id `statusCode` - New status `previousStatusCode` - Prev status |

Here are the events references:

| Event  | Reference |
| ------------- | ------------- |
| Cart checkout | `Amsgames\LaravelShop\Events\CartCheckout` |
| Order placed | `Amsgames\LaravelShop\Events\OrderPlaced` |
| Order completed | `Amsgames\LaravelShop\Events\OrderCompleted` |
| Order status changed | `Amsgames\LaravelShop\Events\OrderStatusChanged` |

#### Event Handler Example

An example of how to use an event in a handler:

```php
<?php

namespace App\Handlers\Events;

use App\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Amsgames\LaravelShop\Events\OrderCompleted;

class NotifyPurchase implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  OrderPurchased $event
     * @return void
     */
    public function handle(OrderCompleted $event)
    {
        // The order ID
        echo $event->id;

        // Get order model object
        $order = Order::find($event->id);

        // My code here...
    }
}
```

Remember to register your handles and listeners at the Event Provider:

```php
        'Amsgames\LaravelShop\Events\OrderCompleted' => [
            'App\Handlers\Events\NotifyPurchase',
        ],
```

## Payment Gateway Development
Laravel Shop has been developed for customization in mind. Allowing the community to expand its capabilities.

Missing payment gateways can be easily developed as external packages and then be configured in the config file. 

Make this proyect a required dependency of your package or laravel's setup and simply extend from Laravel Shop core class, here a PayPal example:

```php
<?php

namespace Vendor\Package;

use Amsgames\LaravelShop\Core\PaymentGateway;
use Amsgames\LaravelShop\Exceptions\CheckoutException;
use Amsgames\LaravelShop\Exceptions\GatewayException;

class GatewayPayPal extends PaymentGateway
{
    /**
     * Called on cart checkout.
     * THIS METHOD IS OPTIONAL, DONE FOR GATEWAY VALIDATIONS BEFORE PLACING AN ORDER
     *
     * @param Order $order Order.
     */
    public function onCheckout($cart)
    {
        throw new CheckoutException('Checkout failed.');
    }

    /**
     * Called by shop to charge order's amount.
     *
     * @param Order $order Order.
     *
     * @return bool
     */
    public function onCharge($order)
    {
        throw new GatewayException('Payment failed.');
        return false;
    }
}
```

The gateway will require `onCharge` method as minimun. You can add more depending your needs.

Once created, you can add it to the `shop.php` config file, like:

```php
'gateways' => [
    'paypal'              =>  Vendor\Package\GatewayPaypal::class,
],
```

And use it like:

```php
Shop::setGateway('paypal');
```

### Transaction

To properly generate the transaction there are 3 properties you must consider on setting during `onCharge`:

```php
public function onCharge($order)
{
    // The transaction id generated by the provider i.e.
    $this->transactionId = $paypal->transactionId;

    // Custom detail of 1024 chars.
    $this->detail = 'Paypal: success';

    // Order status after method call.
    $this->statusCode = 'in_process';

    return true;
}
```
- `transactionId` &mdash; Provider's transaction ID, will help identify a transaction.
- `detail` &mdash; Custom description for the transaction.
- `statusCode` &mdash; Order status code with which to update the order after onCharge has executed. By default is 'completed'.

### Callbacks

Laravel Shop supports gateways that require callbacks. For this, you will need to add 2 additional functions to your gateway:

```php
<?php

namespace Vendor\Package;

use Amsgames\LaravelShop\Core\PaymentGateway;
use Amsgames\LaravelShop\Exceptions\CheckoutException;
use Amsgames\LaravelShop\Exceptions\GatewayException;

class GatewayWithCallbacks extends PaymentGateway
{
    /**
     * Called by shop to charge order's amount.
     *
     * @param Order $order Order.
     *
     * @return bool
     */
    public function onCharge($order)
    {

        // Set the order to pending.
        $this->statusCode = 'pending';

        // Sets provider with the callback for successful transactions.
        $provider->setSuccessCallback( $this->callbackSuccess );

        // Sets provider with the callback for failed transactions.
        $provider->setFailCallback( $this->callbackFail );

        return true;
    }

    /**
     * Called on callback.
     *
     * @param Order $order Order.
     * @param mixed $data  Request input from callback.
     *
     * @return bool
     */
    public function onCallbackSuccess($order, $data = null)
    {
        $this->statusCode     = 'completed';

        $this->detail         = 'successful callback';

        $this->transactionId  = $data->transactionId;

        // My code...
    }

    /**
     * Called on callback.
     *
     * @param Order $order Order.
     * @param mixed $data  Request input from callback.
     *
     * @return bool
     */
    public function onCallbackFail($order, $data = null)
    {
        $this->detail       = 'failed callback';

        // My code...
    }
}
```
In the example above, `onCharge` instead of creating a completed transaction, it is creating a pending transaction and indicating the provider to which urls to call back with the payment results.

The methods `onCallbackSuccess` and `onCallbackFail` are called by `Shop` when the provider calls back with its reponse, the proper function will be called depending on the callback url used by the provider.

The method `onCallbackSuccess` will create a new transaction for the order it ends.

- `callbackSuccess` &mdash; Successful url callback to be used by the provider.
- `callbackFail` &mdash; i.e. Failure url callback to be used by the provider.
- `token` &mdash; i.e. Validation token.

### Exceptions

Laravel Shop provides several exceptions you can use to report errors.

For `onCheckout`:
- `CheckoutException`
- `GatewayException`
- `StoreException` &mdash; This exception will be logged in laravel, so use it only for fatal errores.

For `onChange`, `onCallbackSuccess` and `onCallbackFail`:
- `GatewayException`
- `StoreException` &mdash; This exception will be logged in laravel, so use it only for fatal errores.

**NOTE**: Laravel Shop will not catch any other exception. If a normal `Exception` or any other exceptions is thrown, it will break the method as it normally would, this will affect your checkout flow like in example when you want to get the order from `placeOrder()`.

### Examples

You can see the [PayPal gateways](https://github.com/amsgames/laravel-shop-gateway-paypal/tree/master/src) we made as examples.

- [GatewayPayPal](https://github.com/amsgames/laravel-shop-gateway-paypal/blob/master/src/GatewayPayPal.php) - Processes credit cards, uses `onCheckout` and `onCharge`.

- [GatewayPayPalExpress](https://github.com/amsgames/laravel-shop-gateway-paypal/blob/master/src/GatewayPayPalExpress.php) - Processes callbacks, uses `onCallbackSuccess` and `onCharge`.

## License

Laravel Shop is free software distributed under the terms of the MIT license.

## Additional Information

This package's architecture and design was inpired by the **Zizaco/entrust** package, we'll like to thank their contributors for their awesome woek.

## Change Log
* [v0.2.8](https://github.com/amsgames/laravel-shop/releases/tag/v0.2.8)