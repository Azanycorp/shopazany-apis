<?php

namespace App\Services\User;

use App\Models\Product;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use App\Models\UserBusinessInformation;
use Illuminate\Support\Facades\Storage;

class SellerService
{
    use HttpResponse;

    public function businessInfo($request)
    {
        $user = User::getUserID($request->user_id);

        if(!$user){
            return $this->error(null, "User not found", 404);
        }

        if($user->userbusinessinfo->isNotEmpty()){
            return $this->error(null, "Business information has been submitted", 400);
        }

        try {
            $folder = $this->getStorageFolder($user->email);
    
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
        $user = User::getUserID($request->user_id);

        if(!$user){
            return $this->error(null, "User not found", 404);
        }

        try {

            $slug = Str::slug($request->name);

            if (Product::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . uniqid();
            }
            
            if($request->discount_price > 0){
                $price = (int)$request->product_price - (int)$request->discount_price;
            }

            $folder = null;

            if(App::environment('production')){
                $folder = "/prod/product/{$user->email}";
            } elseif(App::environment(['staging', 'local'])) {
                $folder = "/stag/product/{$user->email}";
            }

            if ($request->file('image')) {
                $path = $request->file('image')->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            $user->products()->create([
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

            return $this->success(null, "Added successfully");
        } catch (\Throwable $th) {
            throw $th;
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
}

