<?php

namespace Amsgames\LaravelShop\Traits;

/**
 * This file is part of LaravelShop,
 * A shop solution for Laravel.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

use Shop;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

trait ShopCartTrait
{
    /**
     * Property used to stored calculations.
     * @var array
     */
    private $cartCalculations = null;

    /**
     * Boot the user model
     * Attach event listener to remove the relationship records when trying to delete
     * Will NOT delete any records if the user model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function($user) {
            if (!method_exists(Config::get('auth.model'), 'bootSoftDeletingTrait')) {
                $user->shopitems()->sync([]);
            }

            return true;
        });
    }

    /**
     * One-to-One relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->belongsTo(Config::get('auth.model'), 'user_id');
    }

    /**
     * One-to-Many relations with Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items()
    {
        return $this->hasMany(Config::get('shop.item'), 'cart_id');
    }

    /**
     * Adds item to cart.
     *
     * @param mixed $item     Item to add, can be an Store Item, a Model with ShopItemTrait or an array.
     * @param int   $quantity Item quantity in cart.
     */
    public function add($item, $quantity = 1, $quantityReset = false)
    {
        if (!is_array($item) && !$item->isShoppable) return;
        // Get added item
        $cartItem = $this->items
            ->where('sku', is_array($item) ? $item['sku'] : $item->sku)
            ->first();
        // Add new or sum quantity
        if (empty($cartItem)) {
            $reflection = null;
            if (is_object($item)) {
                $reflection = new \ReflectionClass($item);
            }
            $cartItem = call_user_func( Config::get('shop.item') . '::create', [
                'user_id'       => $this->user->shopId,
                'sku'           => is_array($item) ? $item['sku'] : $item->sku,
                'price'         => is_array($item) ? $item['price'] : $item->price,
                'tax'           => is_array($item) 
                                    ? (array_key_exists('tax', $item)
                                        ?   $item['tax']
                                        :   0
                                    ) 
                                    : (isset($item->tax) && !empty($item->tax)
                                        ?   $item->tax
                                        :   0
                                    ),
                'shipping'      => is_array($item) 
                                    ? (array_key_exists('shipping', $item)
                                        ?   $item['shipping']
                                        :   0
                                    ) 
                                    : (isset($item->shipping) && !empty($item->shipping)
                                        ?   $item->shipping
                                        :   0
                                    ),
                'currency'      => Config::get('shop.currency'),
                'quantity'      => $quantity,
                'class'         => is_array($item) ? null : $reflection->getName(),
                'reference_id'  => is_array($item) ? null : $item->shopId,
            ]);
            $this->items()->save($cartItem);
        } else {
            $cartItem->quantity = $quantityReset 
                ? $quantity 
                : $cartItem->quantity + $quantity;
            $cartItem->save();
        }
        $this->resetCalculations();
        return $this;
    }

    /**
     * Removes an item from the cart or decreases its quantity.
     * Returns flag indicating if removal was successful.
     *
     * @param mixed $item     Item to remove, can be an Store Item, a Model with ShopItemTrait or an array.
     * @param int   $quantity Item quantity to decrease. 0 if wanted item to be removed completly.
     *
     * @return bool
     */
    public function remove($item, $quantity = 0)
    {
        // Get item
        $cartItem = $this->items
            ->where('sku', is_array($item) ? $item['sku'] : $item->sku)
            ->first();
        // Remove or decrease quantity
        if (!empty($cartItem)) {
            if (!empty($quantity)) {
                $cartItem->quantity -= $quantity;
                $cartItem->save();
                if ($cartItem->quantity > 0) return true;
            }
            $cartItem->delete();
        }
        $this->resetCalculations();
        return $this;
    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param string|array $name       Role name or array of role names.
     * @param bool         $requireAll All roles in the array are required.
     *
     * @return bool
     */
    public function hasItem($sku, $requireAll = false)
    {
        if (is_array($sku)) {
            foreach ($sku as $skuSingle) {
                $hasItem = $this->hasItem($skuSingle);

                if ($hasItem && !$requireAll) {
                    return true;
                } elseif (!$hasItem && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->items as $item) {
                if ($item->sku == $sku) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Scope class by a given user ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query  Query.
     * @param mixed                                 $userId User ID.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to current user cart.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query  Query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereCurrent($query)
    {
        if (Auth::guest()) return $query;
        return $query->whereUser('user_id', Auth::user()->shopId);
    }

    /**
     * Scope to current user cart and returns class model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query  Query.
     *
     * @return this
     */
    public function scopeCurrent($query)
    {
        if (Auth::guest()) return;
        $cart = $query->whereCurrent()->first();
        if (empty($cart)) {
            $cart = call_user_func( Config::get('shop.cart') . '::create', [
                'user_id' =>  Auth::user()->shopId
            ]);
        }
        return $cart;
    }

    /**
     * Scope to current user cart and returns class model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query  Query.
     *
     * @return this
     */
    public function scopeFindByUser($query, $userId)
    {
        $cart = $query->whereUser($userId)->first();
        if (empty($cart)) {
            $cart = call_user_func( Config::get('shop.cart') . '::create', [
                'user_id' =>  $userId
            ]);
        }
        return $cart;
    }

    /**
     * Returns total amount of items in cart.
     *
     * @return int
     */
    public function getCountAttribute()
    {
        if (empty($this->cartCalculations)) $this->runCalculations();
        return $this->cartCalculations->itemCount;
    }

    /**
     * Returns total price of all the items in cart.
     *
     * @return float
     */
    public function getTotalPriceAttribute()
    {
        if (empty($this->cartCalculations)) $this->runCalculations();
        return $this->cartCalculations->totalPrice;
    }

    /**
     * Returns total tax of all the items in cart.
     *
     * @return float
     */
    public function getTotalTaxAttribute()
    {
        if (empty($this->cartCalculations)) $this->runCalculations();
        return $this->cartCalculations->totalTax + round($this->totalPrice * Config::get('shop.tax'), 2);
    }

    /**
     * Returns total tax of all the items in cart.
     *
     * @return float
     */
    public function getTotalShippingAttribute()
    {
        if (empty($this->cartCalculations)) $this->runCalculations();
        return $this->cartCalculations->totalShipping;
    }

    /**
     * Returns total discount amount based on all coupons applied.
     *
     * @return float
     */
    public function getTotalDiscountAttribute() { /* TODO */ }

    /**
     * Returns total amount to be charged base on total price, tax and discount.
     *
     * @return float
     */
    public function getTotalAttribute()
    {
        if (empty($this->cartCalculations)) $this->runCalculations();
        return $this->totalPrice + $this->totalTax + $this->totalShipping;
    }

    /**
     * Returns formatted total price of all the items in cart.
     *
     * @return string
     */
    public function getDisplayTotalPriceAttribute()
    {
        return Shop::format($this->totalPrice);
    }

    /**
     * Returns formatted total tax of all the items in cart.
     *
     * @return string
     */
    public function getDisplayTotalTaxAttribute()
    {
        return Shop::format($this->totalTax);
    }

    /**
     * Returns formatted total tax of all the items in cart.
     *
     * @return string
     */
    public function getDisplayTotalShippingAttribute()
    {
        return Shop::format($this->totalShipping);
    }

    /**
     * Returns formatted total discount amount based on all coupons applied.
     *
     * @return string
     */
    public function getDisplayTotalDiscountAttribute() { /* TODO */ }

    /**
     * Returns formatted total amount to be charged base on total price, tax and discount.
     *
     * @return string
     */
    public function getDisplayTotalAttribute()
    {
        return Shop::format($this->total);
    }

    private function runCalculations()
    {
        if (!empty($this->cartCalculations)) return $this->cartCalculations;
        $cacheKey = 'shop_cart_' . $this->attributes['id'] . '_calculations';
        if (Config::get('shop.cache_in_cart_calculations')
            && Cache::has($cacheKey)
        ) {
            $this->cartCalculations = Cache::get($cacheKey);
            return $this->cartCalculations;
        }
        $this->cartCalculations = DB::table(Config::get('shop.cart_table'))
            ->select([
                DB::raw('sum(item.quantity) as itemCount'),
                DB::raw('sum(item.price * item.quantity) as totalPrice'),
                DB::raw('sum(item.tax * item.quantity) as totalTax'),
                DB::raw('sum(item.shipping * item.quantity) as totalShipping')
            ])
            ->join(Config::get('shop.item_table'), 'item.cart_id', '=', 'cart.id')
            ->where('cart.id', $this->attributes['id'])
            ->first();
        if (Config::get('shop.cache_in_cart_calculations')) {
            Cache::put(
                $cacheKey,
                $this->cartCalculations,
                Config::get('shop.cache_cart_calculations_minutes')
            );
        }
        return $this->cartCalculations;
    }

    /**
     * Resets cart calculations.
     */
    private function resetCalculations ()
    {
        $this->cartCalculations = null;
        if (Config::get('shop.cache_in_cart_calculations')) {
            Cache::forget('shop_cart_' . $this->attributes['id'] . '_calculations');
        }
    }

}