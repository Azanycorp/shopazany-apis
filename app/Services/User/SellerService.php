<?php

namespace App\Services\User;

use App\Enum\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\SellerProductResource;
use App\Imports\ProductImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SellerService extends Controller
{
    use HttpResponse;

    public function businessInfo($request)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        if($user->userbusinessinfo->isNotEmpty()){
            return $this->error(null, "Business information has been submitted", 400);
        }

        try {
            $parts = explode('@', $user->email);
            $name = $parts[0];

            $folder = $this->getStorageFolder($name);

            $url = null;
            if ($request->hasFile('file')) {
                $url = $this->storeFile($request->file('file'), $folder);
            }

            $user->update($request->only(['first_name', 'last_name']));

            $user->userbusinessinfo()->create([
                'business_location' => $request->business_location,
                'business_type' => $request->business_type,
                'identity_type' => $request->identity_type,
                'file' => $url,
                'confirm' => $request->confirm
            ]);

            return $this->success(null, "Information added successfully");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function createProduct($request)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        try {

            $slug = Str::slug($request->name);

            if (Product::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . uniqid();
            }

            $price = $request->product_price;

            if($request->discount_price > 0){
                $price = (int)$request->product_price - (int)$request->discount_price;
            }

            $folder = null;
            $frontImage = null;

            $parts = explode('@', $user->email);
            $name = $parts[0];

            if(App::environment('production')){
                $folder = "/prod/product/{$name}";
                $frontImage = "/prod/product/{$name}/front_image";
            } elseif(App::environment(['staging', 'local'])) {
                $folder = "/stag/product/{$name}";
                $frontImage = "/stag/product/{$name}/front_image";
            }

            if ($request->hasFile('front_image')) {
                $path = $request->file('front_image')->store($frontImage, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            $product = $user->products()->create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'brand_id' => $request->brand_id,
                'color_id' => $request->color_id,
                'unit_id' => $request->unit_id,
                'size_id' => $request->size_id,
                'product_sku' => $request->product_sku,
                'product_price' => $request->product_price,
                'discount_price' => $request->discount_price,
                'price' => $price,
                'current_stock_quantity' => $request->current_stock_quantity,
                'minimum_order_quantity' => $request->minimum_order_quantity,
                'image' => $url,
                'added_by' => $user->type,
                'country_id' => $user->country ?? 160,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store($folder, 's3');
                    $url = Storage::disk('s3')->url($path);

                    $product->productimages()->create([
                        'image' => $url,
                    ]);
                }
            }

            return $this->success(null, "Added successfully");
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateProduct($request, $id, $userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $product = Product::find($id);

        if(!$product){
            return $this->error(null, "Product not found", 404);
        }

        try {

            $slug = Str::slug($request->name);

            if (Product::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . uniqid();
            }

            $price = $request->product_price;

            if($request->discount_price > 0){
                $price = (int)$request->product_price - (int)$request->discount_price;
            }

            $folder = null;
            $frontImage = null;

            $parts = explode('@', $user->email);
            $name = $parts[0];

            if(App::environment('production')){
                $folder = "/prod/product/{$name}";
                $frontImage = "/prod/product/{$name}/front_image";
            } elseif(App::environment(['staging', 'local'])) {
                $folder = "/stag/product/{$name}";
                $frontImage = "/stag/product/{$name}/front_image";
            }

            $image = uploadSingleProductImage($request, 'front_image', $frontImage, $product);

            $product->update([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'brand_id' => $request->brand_id,
                'color_id' => $request->color_id,
                'unit_id' => $request->unit_id,
                'size_id' => $request->size_id,
                'product_sku' => $request->product_sku,
                'product_price' => $request->product_price,
                'discount_price' => $request->discount_price,
                'price' => $price,
                'current_stock_quantity' => $request->current_stock_quantity,
                'minimum_order_quantity' => $request->minimum_order_quantity,
                'image' => $image,
                'country_id' => $user->country ?? 160,
            ]);

            uploadMultipleProductImage($request, 'images', $folder, $product);

            return $this->success(null, "Updated successfully");
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getProduct($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $query = $user->products();

        if (request()->filled('category')) {
            $query->where('category_id', request('category'));
        }

        if (request()->filled('brand')) {
            $query->where('brand_id', request('brand'));
        }

        if (request()->filled('color')) {
            $query->where('color_id', request('color'));
        }

        if (request()->filled('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        $query->with('productimages');

        $products = $query->paginate(25);

        $data = SellerProductResource::collection($products);

        return [
            'status' => 'true',
            'message' => 'All products',
            'data' => $data,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'prev_page_url' => $products->previousPageUrl(),
                'next_page_url' => $products->nextPageUrl(),
            ],
        ];
    }

    public function getSingleProduct($productId, $userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $product = Product::find($productId);

        if(!$product){
            return $this->error(null, "Product not found", 404);
        }

        $data = new SellerProductResource($product);

        return $this->success($data, "Product retrieved successfully");
    }

    public function deleteProduct($id, $userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $product = Product::find($id);

        if(!$product){
            return $this->error(null, "Product not found", 404);
        }

        $product->update([
            'status' => "deleted"
        ]);

        return $this->success(null, "Deleted successfully");
    }

    public function getAllOrders($id)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $orders = Order::where('seller_id', $id)->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "All Orders");

    }

    public function getConfirmedOrders($id)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $orders = Order::where('seller_id', $id)
        ->where('status', OrderStatus::CONFIRMED)
        ->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "Confirmed Orders");

    }

    public function getCancelledOrders($id)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $orders = Order::where('seller_id', $id)
        ->where('status', OrderStatus::CANCELLED)
        ->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "Cancelled Orders");

    }

    public function getDeliveredOrders($id)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $orders = Order::where('seller_id', $id)
        ->where('status', OrderStatus::DELIVERED)
        ->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "Delivered Orders");

    }

    public function getPendingOrders($id)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $orders = Order::where('seller_id', $id)
        ->where('status', OrderStatus::PENDING)
        ->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "Pending Orders");

    }

    public function getProcessingOrders($id)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $orders = Order::where('seller_id', $id)
        ->where('status', OrderStatus::PROCESSING)
        ->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "Processing Orders");

    }

    public function getShippedOrders($id)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $orders = Order::where('seller_id', $id)
        ->where('status', OrderStatus::SHIPPED)
        ->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "Shipped Orders");

    }

    public function getTemplate()
    {
        $data = getImportTemplate();

        return $this->success($data, "Product template");
    }

    public function productImport($request)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $seller = auth()->user();

        try {
            Excel::import(new ProductImport($seller), $request->file('file'));

            return $this->success(null, "Imported successfully");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function export($userId, $type)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        switch ($type) {
            case 'product':
                return $this->exportProduct($userId);
                break;

            case 'order':
                return "None yet";
                break;

            default:
                return "Type not found";
                break;
        }
    }

    public function updateProfile($request, $userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $image = uploadUserImage($request, 'image', $user);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middlename' => $request->middlename,
            'address' => $request->address,
            'phone' => $request->phone_number,
            'country' => $request->country_id,
            'state_id' => $request->state_id,
            'date_of_birth' => $request->date_of_birth,
            'image' => $image,
        ]);

        return $this->success([
            'user_id' => $user->id
        ], "Updated successfully");

    }

    public function dashboardAnalytics($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $totalProducts = Product::where('user_id', $userId)->count();

        $totalOrders = Order::where('seller_id', $userId)->count();

        $orderCounts = Order::where('seller_id', $userId)
            ->selectRaw('
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as confirmed_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as processing_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as shipped_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_sales
            ', [
                OrderStatus::PENDING,
                OrderStatus::CONFIRMED,
                OrderStatus::PROCESSING,
                OrderStatus::SHIPPED,
                OrderStatus::DELIVERED,
                OrderStatus::CANCELLED,
                OrderStatus::DELIVERED
            ])
            ->first();

        return $this->success([
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'completed_sales' => $orderCounts->completed_sales ?? 0,
            'pending_count' => $orderCounts->pending_count ?? 0,
            'confirmed_count' => $orderCounts->confirmed_count ?? 0,
            'processing_count' => $orderCounts->processing_count ?? 0,
            'shipped_count' => $orderCounts->shipped_count ?? 0,
            'delivered_count' => $orderCounts->delivered_count ?? 0,
            'cancelled_count' => $orderCounts->cancelled_count ?? 0,
        ], "Analytics");
    }

    public function getOrderSummary($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $orders = Order::where('seller_id', $userId)
        ->orderBy('created_at', 'desc')
        ->take(8)
        ->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "Order Summary");
    }

    public function topSelling($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $topSellingProducts = DB::table('orders')
        ->select('product_id', DB::raw('SUM(product_quantity) as total_quantity'))
        ->groupBy('product_id')
        ->orderBy('total_quantity', 'desc')
        ->limit(8)
        ->get();

        $productIds = $topSellingProducts->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get();

        $topSellingProductsWithDetails = $topSellingProducts->map(function ($item) use ($products) {
            $product = $products->firstWhere('id', $item->product_id);
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'front_image' => $product->front_image,
                'sold' => $item->total_quantity
            ];
        });

        return $this->success($topSellingProductsWithDetails, "Top Selling Products");
    }

}

