<?php

namespace App\Trait;

trait Product
{
    public function uploadFrontImage($request, $folderPath)
    {
        if ($request->hasFile('front_image')) {
            return uploadImage($request, 'front_image', $folderPath->frontImage);
        }

        return [
            'url' => null,
            'public_id' => null,
        ];
    }

    public function uploadAdditionalImages($request, $name, $product): void
    {
        $folder = folderNames('product', $name, null, 'images');
        uploadMultipleProductImage($request, 'images', $folder->folder, $product);
    }

    public function createProductRecord($request, $user, $slug, array $url, ?string $type = null)
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
            'image' => $url['url'],
            'public_id' => $url['public_id'],
            'added_by' => $user->type,
            'country_id' => $user->country ?? 160,
            'default_currency' => $user->default_currency,
            'condition' => $request->condition,
            'type' => $type,
        ]);
    }

    public function createProductVariations($request, $product, $name): void
    {
        $variations = (new \Illuminate\Support\Collection($request->variation))->map(fn ($item): mixed => json_decode($item, true));
        $variationImages = $request->file('variation_image', []);

        foreach ($variations as $index => $variation) {
            $imageUrl = ['url' => null];

            if (isset($variationImages[$index])) {
                $folder = folderNames('product', $name, null, 'variations');
                $imageUrl = uploadImageFile($variationImages[$index], $folder->folder);
            }

            $product->productVariations()->create([
                'variation' => $variation['variation'],
                'sku' => $variation['sku'],
                'price' => $variation['price'],
                'stock' => $variation['stock'],
                'image' => $imageUrl['url'],
            ]);
        }
    }

    public function calculateFinalPrice($basePrice, $discountType = null, $discountValue = 0)
    {
        if (! $discountType || $discountValue <= 0) {
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

    public function updateProductVariations($request, $product, $name): void
    {
        $processedVariationIds = [];

        $variations = (new \Illuminate\Support\Collection($request->variation))->map(fn ($item): mixed => json_decode($item, true));
        $variationImages = $request->file('variation_image', []);

        foreach ($variations as $index => $variation) {
            $variationId = $variation['id'] ?? null;
            $imageUrl = $imageUrl = ['url' => null];

            if (isset($variationImages[$index])) {
                $folder = folderNames('product', $name, null, 'variations');
                $imageUrl = uploadImageFile($variationImages[$index], $folder->folder);
            }

            if ($variationId) {
                $existingVariation = $product->productVariations()->find($variationId);
                if ($existingVariation) {
                    $existingVariation->update([
                        'variation' => $variation['variation'],
                        'sku' => $variation['sku'],
                        'price' => $variation['price'],
                        'stock' => $variation['stock'],
                        'image' => $imageUrl['url'] ?? $existingVariation->image,
                    ]);

                    $processedVariationIds[] = $existingVariation->id;
                }
            } else {
                $newVariation = $product->productVariations()->create([
                    'variation' => $variation['variation'],
                    'sku' => $variation['sku'],
                    'price' => $variation['price'],
                    'stock' => $variation['stock'],
                    'image' => $imageUrl['url'] ?? null,
                ]);

                $processedVariationIds[] = $newVariation->id;
            }
        }

        $product->productVariations()
            ->whereNotIn('id', $processedVariationIds)
            ->delete();
    }
}
