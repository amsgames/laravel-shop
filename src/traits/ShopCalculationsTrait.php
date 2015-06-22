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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

trait ShopCalculationsTrait
{
    /**
     * Property used to stored calculations.
     * @var array
     */
    private $shopCalculations = null;

    /**
     * Returns total amount of items in cart.
     *
     * @return int
     */
    public function getCountAttribute()
    {
        if (empty($this->shopCalculations)) $this->runCalculations();
        return $this->shopCalculations->itemCount;
    }

    /**
     * Returns total price of all the items in cart.
     *
     * @return float
     */
    public function getTotalPriceAttribute()
    {
        if (empty($this->shopCalculations)) $this->runCalculations();
        return $this->shopCalculations->totalPrice;
    }

    /**
     * Returns total tax of all the items in cart.
     *
     * @return float
     */
    public function getTotalTaxAttribute()
    {
        if (empty($this->shopCalculations)) $this->runCalculations();
        return $this->shopCalculations->totalTax + round($this->totalPrice * Config::get('shop.tax'), 2);
    }

    /**
     * Returns total tax of all the items in cart.
     *
     * @return float
     */
    public function getTotalShippingAttribute()
    {
        if (empty($this->shopCalculations)) $this->runCalculations();
        return $this->shopCalculations->totalShipping;
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
        if (empty($this->shopCalculations)) $this->runCalculations();
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

    /**
     * Returns cache key used to store calculations.
     *
     * @return string.
     */
    public function getCalculationsCacheKeyAttribute()
    {
        return 'shop_' . $this->table . '_' . $this->attributes['id'] . '_calculations';
    }

    /**
     * Runs calculations.
     */
    private function runCalculations()
    {
        if (!empty($this->shopCalculations)) return $this->shopCalculations;
        $cacheKey = $this->calculationsCacheKey;
        if (Config::get('shop.cache_calculations')
            && Cache::has($cacheKey)
        ) {
            $this->shopCalculations = Cache::get($cacheKey);
            return $this->shopCalculations;
        }
        $this->shopCalculations = DB::table($this->table)
            ->select([
                DB::raw('sum(item.quantity) as itemCount'),
                DB::raw('sum(item.price * item.quantity) as totalPrice'),
                DB::raw('sum(item.tax * item.quantity) as totalTax'),
                DB::raw('sum(item.shipping * item.quantity) as totalShipping')
            ])
            ->join(Config::get('shop.item_table'), 'item.' . $this->table . '_id', '=', $this->table . '.id')
            ->where($this->table . '.id', $this->attributes['id'])
            ->first();
        if (Config::get('shop.cache_calculations')) {
            Cache::put(
                $cacheKey,
                $this->shopCalculations,
                Config::get('shop.cache_calculations_minutes')
            );
        }
        return $this->shopCalculations;
    }

    /**
     * Resets cart calculations.
     */
    private function resetCalculations ()
    {
        $this->shopCalculations = null;
        if (Config::get('shop.cache_calculations')) {
            Cache::forget($this->calculationsCacheKey);
        }
    }

}