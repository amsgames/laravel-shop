<?php

use App;
use Shop;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ItemTest extends TestCase
{
	/**
	 * Tests item trait methods on external model.
	 */
	public function testItemMethodsAndAttributes()
	{
		$product = App\TestProduct::create([
			'price' 			=> 9.99,
			'sku'				=> str_random(15),
			'name'				=> str_random(64),
			'description'		=> str_random(500),
		]);

	    $this->assertTrue($product->isShoppable);

	    $this->assertEquals($product->displayName, $product->name);

	    $this->assertEquals($product->shopId, $product->id);

	    $this->assertEquals($product->displayPrice, Shop::format(9.99));

	    $this->assertEquals($product->displayTax, Shop::format(0.00));

	    $this->assertEquals($product->displayShipping, Shop::format(0.00));

	    $response = $this->call('GET', $product->shopUrl);

    	$this->assertResponseOk();

    	$this->assertEquals($product->id, $response->getContent());

	    $product->delete();
	}

	/**
	 * Tests item in cart functionality.
	 */
	public function testItemInCart()
	{
		$product = App\TestProduct::create([
			'price' 			=> 9.99,
			'sku'				=> str_random(15),
			'name'				=> str_random(64),
			'description'		=> str_random(500),
		]);

		$user = factory('App\User')->create();

		$cart = App\Cart::findByUser($user->id)
			->add($product)
			->add(['sku' => 'TEST0001', 'price' => 1.99]);

		foreach ($cart->items as $item) {
			
			if ($item->sku == 'TEST0001') {

				$this->assertFalse($item->hasObject);

				$this->assertNull($item->object);

	    		$this->assertEquals($item->shopUrl, '#');

			} else {

				$this->assertTrue($item->hasObject);

				$this->assertNotEmpty($item->object);

	    		$this->assertNotEquals($item->shopUrl, '#');

	    	}

		}

		$user->delete();

	    $product->delete();
	}
}