<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));
    }
    public function add_to_cart(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:99',
        ]);
        $product = Product::findOrFail($request->id);
        $price = ($product->sale_price !== null && $product->sale_price !== '' && $product->sale_price > 0)
            ? $product->sale_price
            : $product->regular_price;
        Cart::instance('cart')->add($product->id, $product->name, $request->quantity ?? 1, $price)->associate('App\Models\Product');
        return redirect()->back();
    }

     public function increase_cart_quantity($rowId)
    {
        $product=Cart::instance('cart')->get($rowId);
        $qty=$product->qty + 1;
        Cart::instance('cart')->update($rowId,$qty);
        return redirect()->back();
    }

    public function decrease_cart_quantity($rowId)
    {
        $product=Cart::instance('cart')->get($rowId);
        $qty=$product->qty - 1;
        if ($qty < 1) {
            Cart::instance('cart')->remove($rowId);
            return redirect()->back();
        }
        Cart::instance('cart')->update($rowId,$qty);
        return redirect()->back();
    }

    public function remove_item($rowId)
    {
      Cart::instance('cart')->remove($rowId);
      return redirect()->back();
    }

    public function empty_cart()
    {
      Cart::instance('cart')->destroy();
      return redirect()->back();
    }
    public function apply_coupon_code(Request $request)
    {
        $coupon_code = $request->coupon_code;
        if (isset($coupon_code)) {
            $coupon = Coupon::where('code', $coupon_code)->where('expiry_date', '>=', Carbon::today())->where('cart_value', '<=', $this->cartSubtotal())->first();
            if (!$coupon) {
                return redirect()->back()->with('error', 'geçersiz coupon code');
            } else {
                Session::put('coupon', [
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'cart_value' => $coupon->cart_value,
                ]);
                $this->calculateDiscount();
                return redirect()->back()->with('success', 'coupon başarıyla uygulandı');
            }
        } else {
            return redirect()->back()->with('error', 'geçersiz coupon code');
        }
    }
    public function calculateDiscount()
    {
        $subtotal = $this->cartSubtotal();
        $discount = 0;
        if (Session::has('coupon')) {
            $coupon = Session::get('coupon');
            if ($coupon['type'] == 'fixed') {
                $discount = $coupon['value'];
            } else {
                $discount = ($subtotal * $coupon['value']) / 100;
            }
        }
        $discount = min($discount, $subtotal);

        $subtotalAfterDiscount = $subtotal - $discount;
        $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100;
        $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;
        Session::put('discounts', [
            'discount' => round($discount, 2),
            'subtotal' => round($subtotalAfterDiscount, 2),
            'tax' => round($taxAfterDiscount, 2),
            'total' => round($totalAfterDiscount, 2),
        ]);
    }

    // Sepet ara toplamını (binlik ayracı olmadan) float olarak döndürür
    private function cartSubtotal(): float
    {
        return (float) str_replace(',', '', Cart::instance('cart')->subtotal(2, '.', ''));
    }
    public function remove_coupon_code()
    {
        Session::forget('coupon');
        Session::forget('discounts');
        return back()->with('success', 'coupon başarıyla kaldırıldı!');
    }
    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        if (Cart::instance('cart')->content()->count() == 0) {
            return redirect()->route('cart.index');
        }

        $address = Address::where('user_id', Auth::user()->id)
            ->where('is_default', 1)
            ->first();

        return view('checkout', compact('address'));
    }
    public function place_and_order(Request $request)
    {
        if (Cart::instance('cart')->content()->count() == 0) {
            return redirect()->route('cart.index')->with('error', 'Sepetiniz boş');
        }
        $request->validate([
            'mode' => 'required|in:card,paypal,cod',
        ]);

        $user_id = Auth::user()->id;
        $address = Address::where('user_id', $user_id)->where('is_default', true)->first();
        if (!$address) {
            $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits:10',
                'zip' => 'required|numeric|digits:6',
                'state' => 'required',
                'city' => 'required',
                'address' => 'required',
                'locality' => 'required',
                'landmark' => 'required',
            ]);
        }

        $order = DB::transaction(function () use ($request, $user_id, $address) {
            if (!$address) {
                $address = new Address();
                $address->name = $request->name;
                $address->phone = $request->phone;
                $address->zip = $request->zip;
                $address->state = $request->state;
                $address->city = $request->city;
                $address->address = $request->address;
                $address->locality = $request->locality;
                $address->landmark = $request->landmark;
                $address->country = 'Turkiye';
                $address->user_id = $user_id;
                $address->is_default = true;
                $address->save();
            }

            $this->setAmountforcheckout();
            $checkout = Session::get('checkout');

            $order = new Order();
            $order->user_id = $user_id;
            $order->subtotal = $checkout['subtotal'];
            $order->discount = $checkout['discount'];
            $order->tax = $checkout['tax'];
            $order->total = $checkout['total'];
            $order->name = $address->name;
            $order->phone = $address->phone;
            $order->locality = $address->locality;
            $order->address = $address->address;
            $order->city = $address->city;
            $order->state = $address->state;
            $order->country = $address->country;
            $order->landmark = $address->landmark;
            $order->zip = $address->zip;
            $order->save();

            foreach (Cart::instance('cart')->content() as $item) {
                $orderItem = new OrderItem();
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->price = $item->price;
                $orderItem->quantity = $item->qty;
                $orderItem->save();
            }

            // Her ödeme modu için işlem kaydı oluşturulur (kart/paypal entegrasyonu yapılana kadar "pending")
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = $request->mode;
            $transaction->status = 'pending';
            $transaction->save();

            return $order;
        });

        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        Session::put('order_id', $order->id);
        return redirect()->route('cart.order.confirmation');
    }
    public function setAmountforcheckout()
    {
        if (Cart::instance('cart')->content()->count() == 0) {
            Session::forget('checkout');
            return;
        }
        if (Session::has('coupon')) {
            $this->calculateDiscount();
            $d = Session::get('discounts');
            Session::put('checkout', [
                'discount' => $d['discount'],
                'subtotal' => $d['subtotal'],
                'tax' => $d['tax'],
                'total' => $d['total'],
            ]);
        } else {
            $subtotal = $this->cartSubtotal();
            $tax = round(($subtotal * config('cart.tax')) / 100, 2);
            Session::put('checkout', [
                'discount' => 0,
                'subtotal' => round($subtotal, 2),
                'tax' => $tax,
                'total' => round($subtotal + $tax, 2),
            ]);
        }
    }
   public function order_confirmation()
{
    if (Session::has('order_id')) {
        $order = Order::with('orderItems.product', 'transaction')->where('user_id', Auth::id())->find(Session::get('order_id'));

        if (!$order) {
            return redirect()->route('cart.index')->with('error', 'Order not found');
        }

        return view('order-confirmation', compact('order'));
    }

    return redirect()->route('cart.index');
}
public function remove($rowId)
{
    Cart::instance('cart')->remove($rowId);
    return redirect()->back()->with('success', 'Ürün sepetten kaldırıldı');
}

}