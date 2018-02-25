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
     * 
     * @return redirect
     */
    protected function process(Request $request)
    {
        $validator = Validator::make(
            [
                'order_id'  => $request->get('order_id'),
                'status'    => $request->get('status'),
                'shoptoken' => $request->get('shoptoken'),
            ],
            [
                'order_id'  => 'required|exists:' . config('shop.order_table') . ',id',
                'status'    => 'required|in:success,fail',
                'shoptoken' => 'required|exists:' . config('shop.transaction_table') . ',token,order_id,' . $request->get('order_id'),
            ]
        );
        if ($validator->fails()) {
            abort(404);
        }
        $order = call_user_func(config('shop.order') . '::find', $request->get('order_id'));
        $transaction = $order->transactions()->where('token', $request->get('shoptoken'))->first();
        Shop::callback($order, $transaction, $request->get('status'), $request->all());
        $transaction->token = null;
        $transaction->save();
        return redirect()->route(config('shop.callback_redirect_route'), ['orderId' => $order->id]);
    }
}
