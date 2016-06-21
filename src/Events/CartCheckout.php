<?php

namespace Amsgames\LaravelShop\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Event fired when an order has been completed.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */
class CartCheckout
{
	use SerializesModels;

	/**
     * Cart ID.
     * @var int
     */
	public $id;

     /**
     * Flag that indicates if the checkout was successful or not.
     * @var bool
     */
     public $success;

	/**
     * Create a new event instance.
     *
     * @param int  $id      Order ID.
     * @param bool $success Checkout flag result.
     *
     * @return void
     */
	public function __construct($id, $success)
	{
		$this->id = $id;
          $this->success = $success;
	}
}