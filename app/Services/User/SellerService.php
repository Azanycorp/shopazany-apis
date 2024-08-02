<?php

namespace App\Services\User;

use App\Enum\OrderStatus;
use App\Exports\ProductExport;
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
use Maatwebsite\Excel\Facades\Excel;

class SellerService
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
                'added_by' => $user->type
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

            $parts = explode('@', $user->email);
            $name = $parts[0];

            if(App::environment('production')){
                $folder = "/prod/product/{$name}";
            } elseif(App::environment(['staging', 'local'])) {
                $folder = "/stag/product/{$name}";
            }

            if ($request->hasFile('front_image')) {
                $path = $request->file('front_image')->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);
            } else {
                $url = $product->image;
            }

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
                'image' => $url
            ]);


            if ($request->hasFile('images')) {
                $product->productimages()->delete();

                foreach ($request->file('images') as $image) {
                    $path = $image->store($folder, 's3');
                    $url = Storage::disk('s3')->url($path);

                    $product->productimages()->create([
                        'image' => $url,
                    ]);
                }
            }

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

        $user->load('products.productimages');

        $data = SellerProductResource::collection($user->products);

        return $this->success($data, "All products");
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
        if (App::environment('production')){

            $data = "https://azany-uploads.s3.amazonaws.com/prod/product-template/product-template.xlsx";

        } elseif (App::environment(['local', 'staging'])) {

            $data = "https://azany-uploads.s3.amazonaws.com/stag/product-template/product-template.xlsx";
        }

        return $this->success($data, "Product template");
    }

    public function productImport($request)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $sellerId = $request->user_id;

        try {
            Excel::import(new ProductImport($sellerId, $request->file('file')));

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

    private function getStorageFolder(string $email): string
    {
        if (App::environment('production')) {
            return "/prod/document/{$email}";
        }

        return "/stag/document/{$email}";
    }

    private function storeFile($file, string $folder): string
    {
        $path = $file->store($folder, 's3');
        return Storage::disk('s3')->url($path);
    }

    private function exportProduct($userId)
    {
        $data = Excel::download(new ProductExport($userId), 'products.xlsx');

        return $this->success($data, "data");
    }
}

