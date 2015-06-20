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

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

trait ShopCartTrait
{
    /**
     * Const that indicates if quantity should reset when adding an item.
     *
     * @var bool
     */
    const QUANTITY_RESET    = true;

    /**
     * Const that indicates if quantity be added to current when adding an item.
     *
     * @var bool
     */
    const QUANTITY_ADDITION = false;

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
    public function add($item, $quantity = 1, $quantityReset = self::QUANTITY_ADDITION)
    {
        if (!is_array($item) && !$item->isShoppable) return;
        // Get added item
        $item = $this->items
            ->where('sku', is_array($item) ? $item['sku'] : $item->sku)
            ->first();
        // Add new or sum quantity
        if (empty($item)) {
            $reflection = null;
            if (is_object($item)) {
                $reflection = new ReflectionClass($item);
            }
            $item = {Config::get('shop.item')}::create([
                'sku'           => is_array($item) ? $item['sku'] : $item->sku,
                'price'         => is_array($item) ? $item['price'] : $item->price,
                'quantity'      => $quantity,
                'description'   => is_array($item) ? $item['description'] : $item->shopDescription,
                'class'         => is_array($item) ? 'array' : $reflection->getName(),
                'reference_id'  => is_array($item) ? null : $item->shopId,
            ]);
            $this->items->attach($item->id);
        } else {
            $item->quantity = $quantityReset 
                ? $quantity 
                : $item->quantity + $quantity;
            $item->save();
        }
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
        $item = $this->items
            ->where('sku', is_array($item) ? $item['sku'] : $item->sku)
            ->first();
        // Remove or decrease quantity
        if (!empty($item)) {
            if (!empty($quantity)) {
                $item->quantity -= $quantity;
                $item->save();
                if ($item->quantity > 0) return true;
            }
            $this->items->detach($item->id);
            $item->delete();
            return true;
        } else {
            return false;
        }
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
        return $query->whereUser('user_id', Guard::user()->shopId);
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
        return $query->scopeWhereCurrent()->first();
    }

}
