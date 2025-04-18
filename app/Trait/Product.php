<?php

namespace App\Trait;

use Illuminate\Support\Facades\Storage;

trait Product
{
    public function uploadFrontImage($request, $folderPath)
    {
        if ($request->hasFile('front_image')) {
            $path = $request->file('front_image')->store($folderPath->frontImage, 's3');
            return Storage::disk('s3')->url($path);
        }

        return null;
    }

    public function uploadAdditionalImages($request, $folderPath, $product)
    {
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store($folderPath->folder, 's3');
                $url = Storage::disk('s3')->url($path);
                $product->productimages()->create(['image' => $url]);
            }
        }
    }

    public function createProductRecord($request, $user, $slug, $url)
    {
        $price = $this->calculateFinalPrice(
            $request->product_price,
            $request->discount_type,
            $request->discount_value
        );

        return $user->products()->create([
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
            'image' => $url,
            'added_by' => $user->type,
            'country_id' => $user->country ?? 160,
            'default_currency' => $user->default_currency,
        ]);
    }

    public function createProductVariations($request, $product)
    {
        $variations = collect($request->variation)->map(fn ($item) => json_decode($item, true));
        $variationImages = $request->file('variation_image', []);

        foreach ($variations as $index => $variation) {
            $imageUrl = null;

            if (isset($variationImages[$index])) {
                $path = $variationImages[$index]->store('product/variations', 's3');
                $imageUrl = Storage::disk('s3')->url($path);
            }

            $product->productVariations()->create([
                'variation' => $variation['variation'],
                'sku' => $variation['sku'],
                'price' => $variation['price'],
                'stock' => $variation['stock'],
                'image' => $imageUrl
            ]);
        }
    }

    public function calculateFinalPrice($basePrice, $discountType = null, $discountValue = 0)
    {
        if (!$discountType || $discountValue <= 0) {
            return $basePrice;
        }

        if ($discountType === 'flat') {
            return max(0, $basePrice - $discountValue);
        }

        if ($discountType === 'percentage') {
            return max(0, $basePrice - ($basePrice * ($discountValue / 100)));
        }

        return $basePrice;
    }

}
