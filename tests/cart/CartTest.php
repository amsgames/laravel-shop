<?php

use App;
use Auth;
use Hash;
use Log;
use Shop;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CartTest extends TestCase
{

	/**
	 * User set for tests.
	 */
  	protected $user;

	/**
	 * Setups test data.
	 */
	public function setUp()
	{
		parent::setUp();

		$this->user = factory('App\User')->create(['password' => Hash::make('laravel-shop')]);

		Auth::attempt(['email' => $this->user->email, 'password' => 'laravel-shop']);
	}

	/**
	 * Tests if cart is being created correctly.
	 */
	public function testCreationBasedOnUser()
	{
		$user = factory('App\User')->create();

		$cart = App\Cart::findByUser($user->id);

		$this->assertNotEmpty($cart);

		$user->delete();
	}

	/**
	 * Tests if cart is being created correctly.
	 */
	public function testMultipleCurrentCalls()
	{
		$cart = App\Cart::current();

		$this->assertNotEmpty($cart);

		$cart = App\Cart::findByUser($this->user->id);

		$this->assertNotEmpty($cart);

		$this->assertEquals($cart->user->id, $this->user->id);

		$cart = App\Cart::current();

		$this->assertNotEmpty($cart);

		$this->assertEquals($cart->user->id, $this->user->id);
	}

	/**
	 * Tests if cart is being created correctly.
	 */
	public function testCreationBasedOnNull()
	{
		$cart = App\Cart::findByUser(null);

		$this->assertNotEmpty($cart);
	}

	/**
	 * Tests if cart is being created correctly.
	 */
	public function testCreationBasedOnAuthUser()
	{
		$cart = App\Cart::current();

		$this->assertNotEmpty($cart);
	}


	/**
	 * Tests cart item addition and removal.
	 */
	public function testAddingRemovingItems()
	{

		$products = [];

		while (count($products) < 3) {
			$products[] = App\TestProduct::create([
				'price' 			=> count($products) + 0.99,
				'sku'				=> str_random(15),
				'name'				=> str_random(64),
				'description'		=> str_random(500),
			]);
		}

		$cart = App\Cart::current()
			->add($products[0])
			->add($products[1], 2)
			->add($products[2], 3);

		$this->assertEquals($cart->count, 6);

		$cart->add($products[2], 1, true);

		$this->assertEquals($cart->count, 4);

		$cart->remove($products[0], 1);

		$this->assertEquals($cart->count, 3);

		$cart->remove($products[2], 1);

		$this->assertEquals($cart->count, 2);

		$cart->clear();

		$this->assertEquals($cart->items->count(), 0);

		foreach ($products as $product) {
			$product->delete();
		}
	}

	/**
	 * Tests cart additional methods, such as item find and calculations.
	 */
	public function testCartMethods()
	{
		$product = App\TestProduct::create([
			'price' 			=> 1.29,
			'sku'				=> str_random(15),
			'name'				=> str_random(64),
			'description'		=> str_random(500),
		]);

		$cart = App\Cart::current()
			->add($product)
			->add(['sku' => 'TEST001', 'price' => 6.99]);

		$this->assertTrue($cart->hasItem('TEST001'));

		$this->assertFalse($cart->hasItem('XXX'));

		$this->assertEquals($cart->totalPrice, 8.28);

		$this->assertEquals($cart->totalTax, 0);

		$this->assertEquals($cart->totalShipping, 0);

		$this->assertEquals($cart->total, 8.28);

		$product->delete();
	}

	/**
	 * Tests cart order placement.
	 */
	public function testOrderPlacement()
	{
		$cart = App\Cart::current()
			->add(['sku' => str_random(15), 'price' => 1.99])
			->add(['sku' => str_random(15), 'price' => 1.99]);

		$order = $cart->placeOrder();

		$this->assertNotEmpty($order);

		$this->assertEquals($order->totalPrice, 3.98);

		$this->assertEquals($cart->count, 0);

		$this->assertEquals($order->count, 2);

		$this->assertTrue($order->isPending);
	}

	/**
	 * Removes test data.
	 */
	public function tearDown() 
	{
		$this->user->delete();

		parent::tearDown();
	}

}