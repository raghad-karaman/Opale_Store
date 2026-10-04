<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;




class ShopController extends Controller
{
    public function index(Request $request)
    {
        $size = in_array((int) $request->query('size'), [12, 24, 48, 102]) ? (int) $request->query('size') : 12;
        $o_column = "";
        $o_order = "";
        $order = (int) ($request->query('order') ?: -1);
        $f_brands = $request->query('brands');
        $f_categories = $request->query('categories');
        $min_price = is_numeric($request->query('min')) ? $request->query('min') : 1;
        $max_price = is_numeric($request->query('max')) ? $request->query('max') : 1000;
        $filter_price = $request->filled('min') || $request->filled('max');
        switch ($order) {
            case 1:
                $o_column = 'created_at';
                $o_order = 'DESC';
                break;
            case 2:
                $o_column = 'created_at';
                $o_order = 'ASC';
                break;
            case 3:
                $o_column = 'sale_price';
                $o_order = 'ASC';
                break;
            case 4:
                $o_column = 'sale_price';
                $o_order = 'DESC';
                break;
            default:
                $o_column = 'id';
                $o_order = 'DESC';
                break;
        }
        $brands = Brand::orderBy('name', 'ASC')->get();
        $categories = Category::orderBy('name', 'ASC')->get();
        $products = Product::query()
            ->when($f_brands, function ($query) use ($f_brands) {
                $query->whereIn('brand_id', array_filter(explode(',', $f_brands), 'is_numeric'));
            })
            ->when($f_categories, function ($query) use ($f_categories) {
                $query->whereIn('category_id', array_filter(explode(',', $f_categories), 'is_numeric'));
            })
            ->when($filter_price, function ($query) use ($min_price, $max_price) {
                $query->where(function ($q) use ($min_price, $max_price) {
                    $q->whereBetween('regular_price', [$min_price, $max_price])
                      ->orWhereBetween('sale_price', [$min_price, $max_price]);
                });
            })
            ->orderBy($o_column, $o_order)->paginate($size);
        return view('shop', compact('products', 'size', 'order','brands','f_brands','categories','f_categories','min_price','max_price'));
    }
    public function product_details($product_slug)
{
    $product = Product::where('slug', $product_slug)->firstOrFail();
    $rproducts = Product::where('slug', '<>', $product_slug)->get()->take(8);
    return view('details', compact('product', 'rproducts'));
}
}