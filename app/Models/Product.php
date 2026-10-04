<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Brand;
use Intervention\Image\Facades\Image;



class Product extends Model
{
    use HasFactory;

public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}

public function brand()
{
    return $this->belongsTo(Brand::class, 'brand_id');
}
}