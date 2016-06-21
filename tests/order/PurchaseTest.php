<?php

use App;
use Auth;
use Hash;
use Log;
use Shop;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PurchaseTest extends TestCase
{
	/**
	 * Tests if gateway is being selected and created correctly.
	 */
	public function testGateway()
	{
		Shop::setGateway('testPass');

		$this->assertEquals(Shop::getGateway(), 'testPass');

		$gateway = Shop::gateway();

		$this->assertNotNull($gateway);

		$this->assertNotEmpty($gateway->toJson());
	}
	/**
	 * Tests if gateway is being selected and created correctly.
	 */
	public function testUnselectedGateway()
	{
		$this->assertFalse(Shop::checkout());

		$this->assertEquals(Shop::exception()->getMessage(), 'Payment gateway not selected.');
	}

	/**
	 * Tests a purchase and shop flow.
	 */
	public function testPurchaseFlow()
	{
		// Prepare

		$user = factory('App\User')->create(['password' => Hash::make('laravel-shop')]);

		$bool = Auth::attempt(['email' => $user->email, 'password' => 'laravel-shop']);

		$cart = App\Cart::current()
			->add(['sku' => '0001', 'price' => 1.99])
			->add(['sku' => '0002', 'price' => 2.99]);

		Shop::setGateway('testPass');

		Shop::checkout();

		$order = Shop::placeOrder();

		$this->assertNotNull($order);

		$this->assertNotEmpty($order->id);

		$this->assertTrue($order->isCompleted);

		$user->delete();
	}

	/**
	 * Tests a purchase and shop flow.
	 */
	public function testFailPurchase()
	{
		// Prepare

		$user = factory('App\User')->create(['password' => Hash::make('laravel-shop')]);

		$bool = Auth::attempt(['email' => $user->email, 'password' => 'laravel-shop']);

		$cart = App\Cart::current()
			->add(['sku' => '0001', 'price' => 1.99])
			->add(['sku' => '0002', 'price' => 2.99]);

		Shop::setGateway('testFail');

		$this->assertFalse(Shop::checkout());

		$this->assertEquals(Shop::exception()->getMessage(), 'Checkout failed.');

		$order = Shop::placeOrder();

		$this->assertNotNull($order);

		$this->assertNotEmpty($order->id);

		$this->assertTrue($order->hasFailed);

		$this->assertEquals(Shop::exception()->getMessage(), 'Payment failed.');

		$user->delete();
	}
	/**
	 * Tests if failed transactions are being created.
	 */
	public function testFailedTransactions()
	{
		// Prepare

		$user = factory('App\User')->create(['password' => Hash::make('laravel-shop')]);

		$bool = Auth::attempt(['email' => $user->email, 'password' => 'laravel-shop']);

		$cart = App\Cart::current()
			->add(['sku' => '0001', 'price' => 1.99])
			->add(['sku' => '0002', 'price' => 2.99]);

		Shop::setGateway('testFail');

		// Beging test

		$order = Shop::placeOrder();

		$this->assertNotNull($order);

		$this->assertNotEmpty($order->id);

		$this->assertTrue($order->hasFailed);

		$this->assertEquals(count($order->transactions), 1);

		$user->delete();
	}
}