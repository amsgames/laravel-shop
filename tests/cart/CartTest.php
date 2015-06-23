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

	    $bool = Auth::attempt(['email' => $this->user->email, 'password' => 'laravel-shop']);
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
	 * Tests cart item addition.
	 */
	public function testAdditionByItemTrait()
	{

	    $products = [];

	    while (count($products) < 3) {
		    $products[] = App\TestProduct::create([
		    	'price' 			=> count($products) + 0.99,
		    	'sku'				=> str_random(15),
		    	'name'				=> str_random(64),
		    	'description'		=> str_random(1000),
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
	 * Removes test data.
	 */
	public function tearDown() 
	{
		$this->user->delete();

		parent::tearDown();
	}

}