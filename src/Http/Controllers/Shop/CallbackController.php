<?php

namespace Amsgames\LaravelShop\Http\Controllers\Shop;

use Validator;
use Shop;
use Illuminate\Http\Request;
use Amsgames\LaravelShop\Http\Controllers\Controller;

class CallbackController extends Controller
{
    /**
     * Process payment callback.
     *
     * @param Request $request   Request.
     * @param string  $status    Callback status.
     * @param int     $id        Order ID.
     * @param string  $shoptoken Transaction token for security.
     *
     * @return redirect
     */
    protected function process(Request $request, $status, $id, $shoptoken)
    {
        $validator = Validator::make(
            [
                'id'        => $id,
                'status'    => $status,
                'shoptoken' => $shoptoken,
            ],
            [
                'id'        => 'required|exists:' . config('shop.order_table') . ',id',
                'status'    => 'required|in:success,fail',
                'shoptoken' => 'required|exists:' . config('shop.transaction_table') . ',token,order_id,' . $id,
            ]
        );

        if ($validator->fails()) {
            abort(404);
        }

        $order = call_user_func(config('shop.order') . '::find', $id);

        $transaction = $order->transactions()->where('token', $shoptoken)->first();

        Shop::callback($order, $transaction, $status, $request->all());

        $transaction->token = null;

        $transaction->save();

        return redirect()->route(config('shop.callback_redirect_route'), ['order' => $order->id]);
    }
}
