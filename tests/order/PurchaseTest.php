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
		Shop::setGateway('test');

		$this->assertEquals(Shop::getGateway(), 'test');

		$gateway = Shop::gateway();

		$this->assertNotNull($gateway);

		$this->assertNotEmpty($gateway->toJson());
	}

	/**
	 * Tests a purchase and shop flow.
	 */
	public function testPurchaseFlow()
	{
		// Prepare

		$this->user = factory('App\User')->create(['password' => Hash::make('laravel-shop')]);

		$bool = Auth::attempt(['email' => $this->user->email, 'password' => 'laravel-shop']);

		$cart = App\Cart::current()
			->add(['sku' => '0001', 'price' => 1.99])
			->add(['sku' => '0002', 'price' => 2.99]);

		Shop::setGateway('test');

		Shop::checkout();

		$order = Shop::placeOrder();

		$this->assertNotNull($order);

		$this->assertNotEmpty($order->id);

		$this->assertTrue($order->isCompleted);

		$this->user->delete();
	}
}