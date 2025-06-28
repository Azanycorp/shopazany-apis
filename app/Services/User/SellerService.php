<?php

namespace App\Services\User;

use App\Enum\MailingEnum;
use App\Enum\OrderStatus;
use App\Enum\ProductStatus;
use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use App\Http\Resources\OrderResource;
use App\Http\Resources\SellerProductResource;
use App\Imports\ProductImport;
use App\Mail\OrderStatusUpdated;
use App\Trait\General;
use App\Trait\Product as TraitProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SellerService extends Controller
{
    use HttpResponse, General, TraitProduct;

    public function businessInfo($request)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('userbusinessinfo')->find($request->user_id);

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

            $url = [];
            if ($request->hasFile('file')) {
                $url = $this->storeFile($request->file('file'), $folder);
            }

            $user->update($request->only(['first_name', 'last_name']));

            $user->userbusinessinfo()->create([
                'business_location' => $request->business_location,
                'business_type' => $request->business_type,
                'identity_type' => $request->identity_type,
                'file' => $url['url'],
                'confirm' => $request->confirm
            ]);

            return $this->success(null, "Information added successfully");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function createProduct($request)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id || $currentUser->type != UserType::SELLER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('products')->find($request->user_id);
        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $slug = Str::slug($request->name);
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . uniqid();
        }

        $parts = explode('@', $user->email);
        $name = $parts[0];
        $folderPath = folderNames('product', $name, 'front_image');

        DB::beginTransaction();
        try {
            $url = $this->uploadFrontImage($request, $folderPath);
            $product = $this->createProductRecord($request, $user, $slug, $url);
            $this->uploadAdditionalImages($request, $folderPath, $product);
            $this->createProductVariations($request, $product, $name);

            DB::commit();
            return $this->success(null, "Added successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, "Failed to create product: " . $e->getMessage(), 500);
        }
    }

    public function updateProduct($request, $id, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $product = Product::find($id);

        if(!$product){
            return $this->error(null, "Product not found", 404);
        }
        $slug = Str::slug($request->name);
        if (Product::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . uniqid();
        }

        $price = $this->calculateFinalPrice(
            $request->product_price,
            $request->discount_type,
            $request->discount_value
        );

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
            'price' => $price,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'current_stock_quantity' => $request->current_stock_quantity,
            'minimum_order_quantity' => $request->minimum_order_quantity,
            'image' => $image['url'],
            'public_id' => $image['public_id'],
            'country_id' => $user->country ?? 160,
        ]);

        uploadMultipleProductImage($request, 'images', $folder, $product);
        $this->updateProductVariations($request, $product, $name);

        return $this->success(null, "Updated successfully");
    }

    public function getProduct($userId)
    {
        $user = User::with(['products'])->find($userId);

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

        $query->with([
            'category',
            'subCategory',
            'shopCountry',
            'productimages',
            'brand',
            'color',
            'unit',
            'size',
            'orders',
            'productReviews',
            'productVariations',
        ]);

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
        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $product = Product::with([
                'category',
                'subCategory',
                'shopCountry',
                'productimages',
                'brand',
                'color',
                'unit',
                'size',
                'orders',
                'productReviews',
                'productVariations',
            ])
            ->find($productId);

        if(!$product){
            return $this->error(null, "Product not found", 404);
        }

        $data = new SellerProductResource($product);

        return $this->success($data, "Product retrieved successfully");
    }

    public function deleteProduct($id, $userId)
    {
        $product = Product::find($id);

        if(!$product){
            return $this->error(null, "Product not found", 404);
        }

        $product->update([
            'status' => ProductStatus::DELETED
        ]);

        return $this->success(null, "Deleted successfully");
    }

    public function getAllOrders($id)
    {
        $status = request()->query('status');

        $validStatuses = [
            OrderStatus::PENDING,
            OrderStatus::CONFIRMED,
            OrderStatus::PROCESSING,
            OrderStatus::SHIPPED,
            OrderStatus::DELIVERED,
            OrderStatus::CANCELLED,
        ];

        $orders = Order::whereHas('products', function ($query) use ($id) {
                $query->where('user_id', $id);
            })
            ->with(['user', 'products.shopCountry'])
            ->when($status, function ($query) use ($status, $validStatuses) {
                if (!in_array($status, $validStatuses)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid status',
                        'data' => null,
                    ], 400)->throwResponse();
                }
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(25);

        $data = OrderResource::collection($orders);

        return [
            'status' => 'true',
            'message' => 'All Orders',
            'data' => $data,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'prev_page_url' => $orders->previousPageUrl(),
                'next_page_url' => $orders->nextPageUrl(),
            ],
        ];
    }

    public function getOrderDetail($userId, $id)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $order = Order::whereHas('products', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with([
                'user.userShippingAddress',
                'products.shopCountry',
                'products.productVariations.product',
            ])
            ->where('id', $id)
            ->first();

        if (!$order) {
            return $this->error(null, "Order not found", 404);
        }

        $data = new OrderDetailResource($order);

        return $this->success($data, "Order retrieved successfully");
    }

    public function updateOrderStatus($userId, $orderId, $request)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $order = Order::with(['user', 'products'])->find($orderId);

        if (!$order) {
            return $this->error(null, "Order not found", 404);
        }

        $validStatuses = [
            OrderStatus::PENDING,
            OrderStatus::CONFIRMED,
            OrderStatus::PROCESSING,
            OrderStatus::SHIPPED,
            OrderStatus::DELIVERED,
            OrderStatus::CANCELLED,
        ];

        if (!in_array($request->status, $validStatuses)) {
            return $this->error(null, "Invalid status", 400);
        }

        $sellerProducts = $order->products()
            ->whereHas('user', function ($query) use ($currentUserId) {
                $query->where('user_id', $currentUserId);
            })->get();

        if ($sellerProducts->isEmpty()) {
            return $this->error(null, "No products found for this seller in the order.", 404);
        }

        foreach ($sellerProducts as $product) {
            DB::table('order_items')
                ->where('order_id', $orderId)
                ->where('product_id', $product->id)
                ->update(['status' => $request->status]);
        }

        $remainingStatuses = DB::table('order_items')
            ->where('order_id', $orderId)
            ->pluck('status')
            ->unique();

        $newOrderStatus = $this->determineOrderStatus($remainingStatuses);
        $order->update(['status' => $newOrderStatus]);

        $msg = getOrderStatusMessage($request->status);
        logOrderActivity($orderId, $msg, $request->status);

        $type = MailingEnum::ORDER_STATUS_UPDATED;
        $subject = "Order status update";
        $mail_class = OrderStatusUpdated::class;
        $data = [
            'order' => $order,
            'status' => $request->status,
            'user' => $order->user
        ];
        mailSend($type, $order->user, $subject, $mail_class, $data);

        return $this->success(null, "Order updated successfully");
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

        $seller = userAuth();

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

            case 'order':
                return "None yet";

            default:
                return "Type not found";
        }
    }

    public function dashboardAnalytics($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $totalProducts = Product::where('user_id', $userId)->count();
        $totalOrders = Order::whereHas('products', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count();

        $orderCounts = Order::whereHas('products', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
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

        $topRateds = Product::topRated($userId)->limit(5)->get();
        $mostFavorites = Product::mostFavorite($userId)->limit(5)->get();

        $data = [
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'completed_sales' => $orderCounts->completed_sales ?? 0,
            'pending_count' => $orderCounts->pending_count ?? 0,
            'confirmed_count' => $orderCounts->confirmed_count ?? 0,
            'processing_count' => $orderCounts->processing_count ?? 0,
            'shipped_count' => $orderCounts->shipped_count ?? 0,
            'delivered_count' => $orderCounts->delivered_count ?? 0,
            'cancelled_count' => $orderCounts->cancelled_count ?? 0,
            'top_rated' => $topRateds,
            'most_favorite' => $mostFavorites,
        ];

        return $this->success($data, "Analytics");
    }

    public function getOrderSummary($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $orders = Order::whereHas('products', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['user', 'products.shopCountry'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "Order Summary");
    }

    public function topSelling($userId)
    {
        $topSellingProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.user_id', $userId)
            ->select('order_items.product_id', DB::raw('SUM(order_items.product_quantity) as total_quantity'))
            ->groupBy('order_items.product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(8)
            ->get();

        $productIds = $topSellingProducts->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get();

        $data = $topSellingProducts->map(function ($item) use ($products): array {
            $product = $products->firstWhere('id', $item->product_id);
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'front_image' => $product->image,
                'sold' => $item->total_quantity
            ];
        });

        return $this->success($data, "Top Selling Products");
    }

    public function createAttribute($request)
    {
        $user = User::with('productAttributes')
            ->findOrFail($request->user_id);

        foreach ($request['attributes'] as $attribute) {
            $user->productAttributes()->create([
                'name' => $attribute['name'],
                'value' => $attribute['values'],
                'use_for_variation' => $attribute['use_for_variation'],
            ]);
        }

        return $this->success(null, "Attribute created successfully", 201);
    }

    public function getAttribute($userId)
    {
        $user = User::with(['productAttributes' => function ($query) {
                $query->select('id', 'user_id', 'name', 'value', 'use_for_variation');
            }])
            ->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $data = $user->productAttributes;

        return $this->success($data, "All Attributes");
    }

    public function getSingleAttribute($attributeId, $userId)
    {
        $user = User::with(['productAttributes' => function ($query) {
                $query->select('id', 'user_id', 'name', 'value', 'use_for_variation');
            }])
            ->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $attribute = $user->productAttributes()
            ->select('id', 'user_id', 'name', 'value', 'use_for_variation')
            ->find($attributeId);

        if (!$attribute) {
            return $this->error(null, "Attribute not found", 404);
        }

        return $this->success($attribute, "Attribute retrieved successfully");
    }

    public function updateAttribute($request, $attributeId, $userId)
    {
        $user = User::with(['productAttributes' => function ($query) {
                $query->select('id', 'user_id', 'name', 'value', 'use_for_variation');
            }])
            ->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $attribute = $user->productAttributes()
            ->select('id', 'user_id', 'name', 'value', 'use_for_variation')
            ->find($attributeId);

        if (!$attribute) {
            return $this->error(null, "Attribute not found", 404);
        }

        $attribute->update([
            'name' => $request->name,
            'value' => $request->values,
            'use_for_variation' => $request->use_for_variation,
        ]);

        return $this->success(null, "Attribute updated successfully");
    }

    public function deleteAttribute($attributeId, $userId)
    {
        $user = User::with('productAttributes')
            ->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $attribute = $user->productAttributes()->find($attributeId);

        if (!$attribute) {
            return $this->error(null, "Attribute not found", 404);
        }

        $attribute->delete();

        return $this->success(null, "Attribute deleted successfully");
    }

}

