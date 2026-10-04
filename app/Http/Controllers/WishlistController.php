<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class WishlistController extends Controller
{

  public function index()
  {
    $items=Cart::instance('wishlist')->content();
    return view('wishlist',compact('items'));
  }
   public function add_to_wishlist(Request $request)
   {
    $request->validate(['id' => 'required|exists:products,id']);
    $product = Product::findOrFail($request->id);
    $price = ($product->sale_price !== null && $product->sale_price !== '' && $product->sale_price > 0)
        ? $product->sale_price
        : $product->regular_price;
    Cart::instance('wishlist')->add($product->id, $product->name, 1, $price)->associate('App\Models\Product');
    return redirect()->back();
   }
   public function remove_item($rowId)
   {
    Cart::instance('wishlist')->remove($rowId);
    return redirect()->back();
   }
   public function empty_wishlist()
   {
    Cart::instance('wishlist')->destroy();
    return redirect()->back();
   }
   public function move_to_cart($rowId)
   {
    $item=Cart::instance('wishlist')->get($rowId);
    Cart::instance('wishlist')->remove($rowId);
    Cart::instance('cart')->add($item->id,$item->name,$item->qty,$item->price)->associate('App\Models\Product');
    return redirect()->back();
   }
}