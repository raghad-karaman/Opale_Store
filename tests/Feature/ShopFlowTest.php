<?php
namespace Tests\Feature;
use App\Models\{User,Category,Brand,Product,Coupon,Order,Transaction};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopFlowTest extends TestCase
{
    use RefreshDatabase;

    private function seedData()
    {
        $cat=Category::forceCreate(['name'=>'Kolye','slug'=>'kolye','image'=>'x.jpg']);
        $br=Brand::forceCreate(['name'=>'Opale','slug'=>'opale','image'=>'x.jpg']);
        $p=new Product; $p->name='Test Kolye';$p->slug='test-kolye';$p->short_description='s';$p->description='d';
        $p->regular_price=100;$p->sale_price=80;$p->SKU='T1';$p->stock_status='instock';$p->featured=1;$p->quantity=10;
        $p->image='x.jpg';$p->category_id=$cat->id;$p->brand_id=$br->id;$p->save();
        Coupon::forceCreate(['code'=>'YUZDE10','type'=>'percent','value'=>10,'cart_value'=>50,'expiry_date'=>now()->addDay()]);
        return $p;
    }

    public function test_public_pages()
    {
        $p=$this->seedData();
        foreach (['/','/shop','/shop/test-kolye','/cart','/wishlist','/contact-us','/aboutus','/login','/register','/shop?brands=1&categories=1&min=1&max=500','/shop?brands=1%27%20OR%201=1--'] as $u) {
            $this->get($u)->assertStatus(200);
        }
    }

    public function test_admin_protected()
    {
        $this->get('/admin/contacts')->assertRedirect('/login');
        $this->delete('/admin/contact/1/delete')->assertRedirect('/login');
        $u=User::factory()->create(['mobile'=>'5551110000']);
        $this->actingAs($u)->get('/admin')->assertRedirect('/login');
    }

    public function test_full_order_flow_and_admin()
    {
        $p=$this->seedData();
        $u=User::factory()->create(['mobile'=>'5551112233']);
        // sahte fiyatla ekle
        $this->post('/cart/add',['id'=>$p->id,'name'=>'x','quantity'=>2,'price'=>1])->assertRedirect();
        $this->get('/cart')->assertStatus(200)->assertSee('160.00');
        $this->post('/cart/coupon_apply',['coupon_code'=>'YUZDE10'])->assertSessionHas('success');
        $this->actingAs($u);
        $this->get('/checkout')->assertStatus(200);
        $this->post('/place-an-order',[
            'mode'=>'cod','name'=>'Ali','phone'=>'5551112233','zip'=>'270000','state'=>'Gaziantep','city'=>'Sehitkamil','address'=>'Adres','locality'=>'Mah','landmark'=>'Park'
        ])->assertRedirect('/order-confirmation');
        $o=Order::first();
        $this->assertEquals(160, (float)$o->subtotal+(float)$o->discount);
        $this->assertEquals(16, (float)$o->discount);
        $this->assertEquals(144, (float)$o->subtotal);
        $this->assertEquals(174.24, (float)$o->total);
        $this->assertEquals(1, Transaction::count());
        $this->get('/order-confirmation')->assertStatus(200);
        $this->get('/account-orders')->assertStatus(200);
        $this->get('/account-order/details/'.$o->id)->assertStatus(200);

        // başkası iptal edemez
        $other=User::factory()->create(['mobile'=>'5559998877']);
        $this->actingAs($other)->put('/account-order/cancel-order',['order_id'=>$o->id])->assertStatus(404);
        $this->assertEquals('ordered',$o->fresh()->status);

        // admin
        $admin=User::factory()->create(['mobile'=>'5550001111','utype'=>'ADM']);
        $this->actingAs($admin);
        foreach (['/admin/orders','/admin/order/order_details/'.$o->id,'/admin/products','/admin/brands','/admin/categories','/admin/coupon','/admin/slides','/admin/contacts','/admin/product/add','/admin/search?query=Test'] as $u2) {
            $this->get($u2)->assertStatus(200);
        }
        $this->put('/admin/order/update-status',['order_id'=>$o->id,'order_status'=>'delivered'])->assertSessionHas('status');
        $this->assertEquals('delivered',$o->fresh()->status);
        $this->assertEquals('approved',Transaction::first()->status);
    }

    public function test_empty_cart_cannot_order()
    {
        $this->seedData();
        $u=User::factory()->create(['mobile'=>'5551112233']);
        $this->actingAs($u)->post('/place-an-order',['mode'=>'cod'])->assertRedirect('/cart');
        $this->assertEquals(0, Order::count());
    }

    public function test_contact_and_wishlist()
    {
        $p=$this->seedData();
        $this->post('/contact/store',['name'=>'Ali','email'=>'a@b.com','phone'=>'05551112233','comment'=>'Merhaba'])->assertSessionHas('success');
        $this->post('/wishlist/add',['id'=>$p->id,'price'=>1])->assertRedirect();
        $this->get('/wishlist')->assertStatus(200)->assertSee('80');
    }
}
