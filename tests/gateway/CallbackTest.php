<?php

use App;
use Shop;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CallbackTest extends TestCase
{

	/**
	 * User set for tests.
	 */
  	protected $user;

	/**
	 * Cart set for tests.
	 */
  	protected $cart;

	/**
	 * Product set for tests.
	 */
  	protected $product;

	/**
	 * Setups test data.
	 */
	public function setUp()
	{
		parent::setUp();

		$this->user = factory('App\User')->create(['password' => Hash::make('laravel-shop')]);

		Auth::attempt(['email' => $this->user->email, 'password' => 'laravel-shop']);

		$this->product = App\TestProduct::create([
			'price' 			=> 9.99,
			'sku'				=> str_random(15),
			'name'				=> str_random(64),
			'description'		=> str_random(500),
		]);

		$this->cart = App\Cart::current()->add($this->product);
	}

	/**
	 * Removes test data.
	 */
	public function tearDown() 
	{
		$this->user->delete();

		$this->product->delete();

		parent::tearDown();
	}

	/**
	 * Tests success callback.
	 */
	public function testSuccessCallback()
	{
		Shop::setGateway('testCallback');

		Shop::checkout();

		$order = Shop::placeOrder();

		$callback = Shop::gateway()->getCallbackSuccess();

		$this->assertTrue($order->isPending);

	    $response = $this->call('GET', $callback);

		$this->assertTrue(Shop::gateway()->getDidCallback());
	}

	/**
	 * Tests success callback.
	 */
	public function testFailCallback()
	{
		Shop::setGateway('testCallback');

		Shop::checkout();

		$order = Shop::placeOrder();

		$callback = Shop::gateway()->getCallbackFail();

		$this->assertTrue($order->isPending);

		$response = $this->call('GET', $callback);

		$this->assertTrue(Shop::gateway()->getDidCallback());
	}
}