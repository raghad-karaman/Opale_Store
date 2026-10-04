<?php

namespace App\Http\Controllers;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use App\Models\Slide;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;



class AdminController extends Controller
{
    public function index()
    {
    $orders = Order::with('orderItems')->orderBy('created_at','DESC')->take(10)->get();

    $dashboardDatas = DB::select("Select sum(total) As TotalAmount,
        sum(if(status='ordered',total,0)) As TotalOrderedAmount,
        sum(if(status='delivered',total,0)) As TotalDeliveredAmount,
        sum(if(status='canceled',total,0)) As TotalCancelledAmount,
        Count(*) As Total,
        sum(if(status='ordered',1,0)) As TotalOrdered,
        sum(if(status='delivered',1,0)) As TotalDelivered,
        sum(if(status='canceled',1,0)) As TotalCancelled
        From orders
    ");

    $monthlyDatas = DB::select("
        SELECT
            M.id AS MonthNo,
            M.name AS MonthName,
            IFNULL(D.TotalAmount, 0) AS TotalAmount,
            IFNULL(D.TotalOrderedAmount, 0) AS TotalOrderedAmount,
            IFNULL(D.TotalDeliveredAmount, 0) AS TotalDeliveredAmount,
            IFNULL(D.TotalCancelledAmount, 0) AS TotalCancelledAmount
        FROM month_names M
        LEFT JOIN (
            SELECT
                DATE_FORMAT(created_at, '%b') AS MonthName,
                MONTH(created_at) AS MonthNo,
                SUM(total) AS TotalAmount,
                SUM(IF(status = 'ordered', total, 0)) AS TotalOrderedAmount,
                SUM(IF(status = 'delivered', total, 0)) AS TotalDeliveredAmount,
                SUM(IF(status = 'canceled', total, 0)) AS TotalCancelledAmount
            FROM orders
            WHERE YEAR(created_at) = YEAR(NOW())
            GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
        ) D ON D.MonthNo = M.id
    ");

    $AmountM= implode(',',collect($monthlyDatas)->pluck('TotalAmount')->toArray());
    $OrderedAmountM= implode(',',collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
    $DeliveredAmountM= implode(',',collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
    $CanceledAmountM= implode(',',collect($monthlyDatas)->pluck('TotalCancelledAmount')->toArray());

    $TotalAmount= collect($monthlyDatas)->sum('TotalAmount');
    $TotalOrderedAmount= collect($monthlyDatas)->sum('TotalOrderedAmount');
    $TotalDeliveredAmount= collect($monthlyDatas)->sum('TotalDeliveredAmount');
    $TotalCanceledAmount= collect($monthlyDatas)->sum('TotalCancelledAmount');

    return view('admin.index',compact('orders','dashboardDatas','AmountM','OrderedAmountM','DeliveredAmountM','CanceledAmountM','TotalAmount','TotalOrderedAmount','TotalDeliveredAmount','TotalCanceledAmount'));
}

public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}



    public function brands()
{
    $brands = Brand::orderBy('id','DESC')->paginate(10);
    return view('admin.brands', compact('brands'));


 }
public function brand_add()
{
return view('admin.brand_add');

}
public function brand_store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:brands,slug',
        'image' => 'mimes:jpeg,png,jpg,gif|max:2048',
    ]);
    $brand = new Brand();
    $brand->name = $request->name;
    $brand->slug =Str::slug( $request->slug);
    $image = $request->file('image');
    $file_extension = $request->file('image')->extension();
    $file_name=Carbon::now()->timestamp . '.' . $file_extension;
    $this->GenerateBrandThumbailsImage($image,$file_name);
    $brand->image = $file_name;
    $brand->save();
    return redirect()->route('admin.brands')->with('status', 'Brand added successfully!');


}
public function brand_edit($id)
{
    $brand = Brand::find($id);
    return view('admin.brand_edit', compact('brand'));
}
public function brand_update(Request $request, $id)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:brands,slug,' . $id,
        'image' => 'mimes:jpeg,png,jpg,gif|max:2048',
    ]);
    $brand = Brand::find($id);
    $brand->name = $request->name;
    $brand->slug =Str::slug( $request->slug);



    if($request->hasFile('image'))
{
     if(File::exists(public_path('/uploads/brands/' )."/". $brand->image)){
        File::delete(public_path('/uploads/brands/' )."/". $brand->image);
    }
    $image = $request->file('image');
    $file_extension = $request->file('image')->extension();
    $file_name=Carbon::now()->timestamp . '.' . $file_extension;
    $this->GenerateBrandThumbailsImage($image,$file_name);
    $brand->image = $file_name;
    }
    $brand->save();
    return redirect()->route('admin.brands')->with('status', 'Brand updated successfully!');
}

public function brand_delete($id)
{
    $brand = Brand::find($id);
    if(File::exists(public_path('/uploads/brands/' )."/". $brand->image)){
        File::delete(public_path('/uploads/brands/' )."/". $brand->image);
    }
    $brand->delete();
    return redirect()->route('admin.brands')->with('status', 'Brand deleted successfully!');
}

public function GenerateBrandThumbailsImage($image,$imageName)
{
    $destinationPath = public_path('/uploads/brands');
    $img = Image::make($image->path());
    //$img->cover(124,124,"top");

    $img->fit(124, 124, function ($constraint) {
        $constraint->upsize();
    }, 'top');


    $img->resize(124,124,function($constraint){
        $constraint->aspectRatio();
    });
    $img->save($destinationPath . '/' . $imageName);
}

public function slides()
    {
        $slides = Slide::orderBy('id','DESC')->paginate(12);
        return view('admin.slides', compact('slides'));
    }





public function categories()
{
    $categories = Category::orderBy('id','DESC')->paginate(10);
    return view('admin.categories', compact('categories'));
}
public function category_add()
{
    return view('admin.category_add');
}

public function category_store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:categories,slug',
        'image' => 'mimes:jpeg,png,jpg,gif|max:2048',
    ]);
    $category = new Category();
    $category->name = $request->name;
    $category->slug =Str::slug( $request->slug);
    $image = $request->file('image');
    $file_extension = $request->file('image')->extension();
    $file_name=Carbon::now()->timestamp . '.' . $file_extension;
    $this->GenerateCategoryThumbailsImage($image,$file_name);
    $category->image = $file_name;
    $category->save();
    return redirect()->route('admin.categories')->with('status', 'Category added successfully!');
}
public function GenerateCategoryThumbailsImage($image,$imageName)
{
    $destinationPath = public_path('/uploads/categories');
    $img = Image::make($image->path());
    //$img->cover(124,124,"top");
    $img->fit(124, 124, function ($constraint) {
        $constraint->upsize();
    }, 'top');


    $img->resize(124,124,function($constraint){
        $constraint->aspectRatio();
    });
    $img->save($destinationPath . '/' . $imageName);
}

public function category_edit($id)
{
    $category = Category::find($id);
    return view('admin.category_edit', compact('category'));

}
public function category_update(Request $request, $id)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:categories,slug,' . $id,
        'image' => 'mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $category = Category::find($id);
    $category->name = $request->name;
    $category->slug = Str::slug($request->slug);

    if($request->hasFile('image')) {
        if(File::exists(public_path('/uploads/categories/' . $category->image))){
            File::delete(public_path('/uploads/categories/' . $category->image));
        }

        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateCategoryThumbailsImage($image, $file_name);
        $category->image = $file_name;
    }

    $category->save();

    return redirect()->route('admin.categories')->with('status', 'Category updated successfully!');
}

public function category_delete($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('/uploads/categories/' . $category->image))) {
            File::delete(public_path('/uploads/categories/' . $category->image));
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category deleted successfully!');
    }

public function orders(){

   $orders = Order::orderBy('created_at','DESC')->paginate(10);
  return view('admin.orders', compact('orders'));
}

 public function details($order_id)
{
    $order = Order::findOrFail($order_id);  // Tek siparişi alıyoruz
    $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
    $transaction = Transaction::where('order_id', $order_id)->first();
     return view('admin.order_details', compact('order', 'orderItems', 'transaction'));
}



public function order_status_update(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'order_status' => 'required|in:ordered,delivered,canceled',
    ]);
    $order = Order::findOrFail($request->order_id);
    $order->status = $request->order_status;
    if ($request->order_status == 'delivered') {
        $order->delivered_date = Carbon::now();
    } elseif ($request->order_status == 'canceled') {
        $order->canceled_date = Carbon::now();
    }
    $order->save();

    if ($request->order_status == 'delivered') {
        Transaction::where('order_id', $order->id)->update(['status' => 'approved']);
    }
    return back()->with('status', 'Sipariş durumu güncellendi');
}

public function products()
{
    $products = Product::orderBy('created_at', 'DESC')->paginate(10);
    return view('admin.products', compact('products'));
}

public function product_add()
{
    $categories = Category::select('id', 'name')->orderBy('name')->get();
    $brands = Brand::select('id', 'name')->orderBy('name')->get();
    return view('admin.product-add', compact('categories', 'brands'));
}

public function product_store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:products,slug',
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required',
        'sale_price' => 'required',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required',
        'image' => 'required|mimes:png,jpg,jpeg|max:2048',
        'category_id' => 'required',
        'brand_id' => 'required',
    ]);

    $product = new Product();
    $product->name = $request->name;
    $product->slug = Str::slug($request->slug);
    $product->short_description = $request->short_description;
    $product->description = $request->description;
    $product->regular_price = $request->regular_price;
    $product->sale_price = $request->sale_price;
    $product->SKU = $request->SKU;
    $product->stock_status = $request->stock_status;
    $product->featured = $request->featured;
    $product->quantity = $request->quantity;
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;

    $current_timestamp = Carbon::now()->timestamp;

    if ($request->hasFile('image'))
{
    $image = $request->file('image');
    $imageName = $current_timestamp . '.' . $image->extension();
    $this->GenerateProductThumbnailImage($image, $imageName);
    $product->image = $imageName;
}

$gallery_arr = array();
$gallery_images = "";
$counter = 1;

if ($request->hasFile('images'))
{
    $allowedfileExtention = ['jpg', 'png', 'jpeg'];
    $files = $request->file('images');
    foreach($files as $file)
    {
        $gextention = $file->getClientOriginalExtension();
        $gcheck = in_array($gextention,$allowedfileExtention);
        if($gcheck)
        {
            $gfileName = $current_timestamp . '.' . $counter . '.' . $gextention;
            $this->GenerateProductThumbnailImage($file,$gfileName);
            array_push($gallery_arr,$gfileName);
            $counter = $counter + 1;
        }
    }
    $gallery_images = implode(',',$gallery_arr);
}
$product->images = $gallery_images;
$product->save();
return redirect()->route('admin.products')->with('status', 'Product has been added successfully!');
}


public function GenerateProductThumbnailImage($image, $imageName)
{
    $destinationPathThumbnail = public_path('uploads/products/thumbnails');
    $destinationPath = public_path('uploads/products');

    // Create folders if they don't exist
    if (!File::exists($destinationPath)) {
        File::makeDirectory($destinationPath, 0775, true);
    }

    if (!File::exists($destinationPathThumbnail)) {
        File::makeDirectory($destinationPathThumbnail, 0775, true);
    }

    try {
        $img = Image::make($image->getRealPath());

        // Main image
        $img->fit(540, 689, function ($constraint) {
            $constraint->upsize();
        })->save($destinationPath . '/' . $imageName);

        // Thumbnail image
        $img->fit(104, 104, function ($constraint) {
            $constraint->upsize();
        })->save($destinationPathThumbnail . '/' . $imageName);

        return true;

    } catch (\Exception $e) {
        Log::error('Görsel işleme hatası: ' . $e->getMessage());
        return false;
    }
}
public function product_edit($id)
{
    $product = Product::findOrFail($id);
    $categories = Category::select('id', 'name')->orderBy('name')->get();
    $brands = Brand::select('id', 'name')->orderBy('name')->get();
    return view('admin.product-edit', compact('product', 'categories', 'brands'));
}

public function product_update(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:products,slug,' . $request->id,
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required',
        'sale_price' => 'required',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required',
        'image' => 'mimes:png,jpg,jpeg|max:2048',
        'category_id' => 'required',
        'brand_id' => 'required',
    ]);

    $product = Product::find($request->id);
    $product->name = $request->name;
    $product->slug = Str::slug($request->slug);
    $product->short_description = $request->short_description;
    $product->description = $request->description;
    $product->regular_price = $request->regular_price;
    $product->sale_price = $request->sale_price;
    $product->SKU = $request->SKU;
    $product->stock_status = $request->stock_status;
    $product->featured = $request->featured;
    $product->quantity = $request->quantity;
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;

    $current_timestamp = Carbon::now()->timestamp;

    if ($request->hasFile('image')) {
        if (File::exists(public_path('uploads/products/') . '/' . $product->image)) {
            File::delete(public_path('uploads/products/') . '/' . $product->image);
        }

        if (File::exists(public_path('uploads/products/thumbnails/') . '/' . $product->image)) {
            File::delete(public_path('uploads/products/thumbnails/') . '/' . $product->image);
        }
        $image = $request->file('image');
        $imageName = $current_timestamp . '.' . $image->extension();
        $this->GenerateProductThumbnailImage($image, $imageName);
        $product->image = $imageName;
    }

    $gallery_arr = array();
    $gallery_images = "";
    $counter = 1;

    if ($request->hasFile('images')) {
        foreach (explode(',', $product->images) as $file) {
            if (File::exists(public_path('uploads/products/') . '/' . $file)) {
                File::delete(public_path('uploads/products/') . '/' . $file);
            }
            if (File::exists(public_path('uploads/products/thumbnails/') . '/' . $file)) {
                File::delete(public_path('uploads/products/thumbnails/') . '/' . $file);
            }
        }
        $allowedfileExtention = ['jpg', 'png', 'jpeg'];
        $files = $request->file('images');
        foreach ($files as $file) {
            $gextention = $file->getClientOriginalExtension();
            $gcheck = in_array($gextention, $allowedfileExtention);
            if ($gcheck) {
                $gfileName = $current_timestamp . '_' . $counter . '.' . $gextention;
                $this->GenerateProductThumbnailImage($file, $gfileName);
                array_push($gallery_arr, $gfileName);
                $counter++;
            }
        }
        $gallery_images = implode(',', $gallery_arr);
        $product->images = $gallery_images;
    }

       $product->save();
       return redirect()->route('admin.products')->with('status', 'Product has been updated successfully');

}
public function product_delete($id)
{
    $product = Product::findOrFail($id);

    // Ana görseli sil
    if ($product->image && File::exists(public_path('uploads/products/') . '/' . $product->image)) {
        File::delete(public_path('uploads/products/') . '/' . $product->image);
    }
    if ($product->image && File::exists(public_path('uploads/products/thumbnails/') . '/' . $product->image)) {
        File::delete(public_path('uploads/products/thumbnails/') . '/' . $product->image);
    }

    // Galeri görsellerini sil
    if ($product->images) {
        foreach (explode(',', $product->images) as $file) {
            if ($file && File::exists(public_path('uploads/products/') . '/' . trim($file))) {
                File::delete(public_path('uploads/products/') . '/' . trim($file));
            }
            if ($file && File::exists(public_path('uploads/products/thumbnails/') . '/' . trim($file))) {
                File::delete(public_path('uploads/products/thumbnails/') . '/' . trim($file));
            }
        }
    }

    $product->delete();
return redirect()->route('admin.products')->with('status', 'Product has been deleted successfully');
}


public function slide_add()
    {
        return view('admin.slide-add');
    }
        public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
        ]);
        $slide = new Slide();
        $slide->tagline=$request->tagline;
        $slide->title=$request->title;
        $slide->subtitle=$request->subtitle;
        $slide->link=$request->link;
        $slide->status=$request->status;
        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateslideThumbailsImage($image, $file_name);
        $slide->image = $file_name;
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'slide added successfully!');
    }
    public function GenerateslideThumbailsImage($image, $imageName)
    {
        $destinationPath = public_path('/uploads/slides');
        $img = Image::make($image->path());
        $img->fit(400, 690, function ($constraint) {
            $constraint->upsize();
        }, 'top');
        $img->resize(400, 690, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save($destinationPath . '/' . $imageName);

    }
    public function slide_edit($id)
     {
        $slide= Slide::find($id);
        return view('admin.slide-edit',compact('slide'));
     }
     public function slide_update(Request $request)
     {
            $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => '|mimes:png,jpg,jpeg|max:2048',
        ]);
        $slide= Slide::find($request->id);
        $slide->tagline=$request->tagline;
        $slide->title=$request->title;
        $slide->subtitle=$request->subtitle;
        $slide->link=$request->link;
        $slide->status=$request->status;
        if($request->hasfile('image'))
        {
            if(File::exists(public_path('uploads/slides').'/'.$slide->image))
            {
                File::delete(public_path('uploads/slides'.'/'.$slide->image));
            }
        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateslideThumbailsImage($image, $file_name);
        $slide->image = $file_name;
        }

        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'slide updated successfully!');
     }
    public function slide_delete($id)
{
    $slide = Slide::find($id);

    if ($slide) {
        $image_path = public_path('uploads/slides/' . $slide->image);
        if (File::exists($image_path)) {
            File::delete($image_path);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status', 'Slide deleted successfully!');
    }

    return redirect()->route('admin.slides')->with('error', 'Slide not found.');
}

public function coupons(){
    $coupons = Coupon::orderBy('expiry_date','DESC')->paginate(12);
    return view('admin.coupons',compact('coupons'));
}
public function coupon_add(){
    return view('admin.coupon-add');
}
public function coupon_store(Request $request){
    $request->validate([
        'code'=> 'required',
        'type' => 'required',
        'value' => 'required|numeric',
        'cart_value' => 'required|numeric',
        'expiry_date' => 'required|date',


    ]);
    $coupon = new Coupon();
    $coupon->code = $request->code;
    $coupon->type = $request->type;
    $coupon->value = $request->value;
    $coupon->cart_value = $request->cart_value;
    $coupon->expiry_date = $request->expiry_date;
    $coupon->save();
    return redirect()->route('admin.coupons')->with('status','coupon has added succesffuly!');

}
public function coupon_edit($id){
$coupon = Coupon::find($id);
return view('admin.coupon-edit',compact('coupon'));
}
public function coupon_update(Request $request){
    $request->validate([
        'code'=> 'required',
        'type' => 'required',
        'value' => 'required|numeric',
        'cart_value' => 'required|numeric',
        'expiry_date' => 'required|date',


    ]);
     $coupon = Coupon :: find($request -> id);
    $coupon->code = $request->code;
    $coupon->type = $request->type;
    $coupon->value = $request->value;
    $coupon->cart_value = $request->cart_value;
    $coupon->expiry_date = $request->expiry_date;
    $coupon->save();
     return redirect()->route('admin.coupons')->with('status','coupon has updated succesffuly!');

}
public function coupon_delete($id){
$coupon = Coupon::findOrFail($id);
$coupon->delete();
return redirect()->route('admin.coupons')->with('status','coupon has deleted succesffuly!');
}
public function contacts()
     {
        $contacts= Contact::orderBy('created_at','DESC')->paginate(10);
        return view('admin.contacts',compact('contacts'));
         }
          public function contact_delete($id)
{
    $contact = Contact::findOrFail($id);
    $contact->delete();
    return redirect()->route('admin.contacts')->with('status', 'Contact deleted successfully!');
}
  public function search(Request $request)
{
    $query = $request->input('query');
    $results = Product::where('name', 'LIKE', "%{$query}%")->take(8)->get();
    return response()->json($results);
}




}