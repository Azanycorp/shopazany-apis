<?php

namespace App\Services\B2B;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\B2BProduct;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\B2BProductResource;
use App\Http\Resources\B2BSellerShippingAddressResource;
use App\Http\Resources\SellerProfileResource;
use App\Models\B2BSellerShippingAddress;
use App\Repositories\B2BProductRepository;
use App\Repositories\B2BSellerShippingRepository;

class SellerService extends Controller
{
    use HttpResponse;

    protected $b2bProductRepository;
    protected $b2bSellerShippingRepository;

    public function __construct(
        B2BProductRepository $b2bProductRepository,
        B2BSellerShippingRepository $b2bSellerShippingRepository
    )
    {
        $this->b2bProductRepository = $b2bProductRepository;
        $this->b2bSellerShippingRepository = $b2bSellerShippingRepository;
    }

    public function businessInformation($request)
    {
        $user = User::with('businessInformation')->findOrFail($request->user_id);

        try {

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middlename' => $request->middlename,
                'country' => $request->country_id,
            ]);

            $folder = folderName('document/businessreg');

            $businessDoc = $request->hasFile('business_reg_document') ? uploadImage($request, 'business_reg_document', $folder) : null;

            $identifyTypeDoc = null;
            if($request->identification_type && $request->hasFile('identification_type_document')) {
                $fld = folderName('document/identifytype');
                $identifyTypeDoc = uploadImage($request, 'identification_type_document', $fld);
            }

            $user->businessInformation()->create([
                'business_location' => $request->business_location,
                'business_type' => $request->business_type,
                'business_name' => $request->business_name,
                'business_reg_number' => $request->business_reg_number,
                'business_phone' => $request->business_phone,
                'country_id' => $request->country_id,
                'city' => $request->city,
                'address' => $request->address,
                'zip' => $request->zip,
                'state' => $request->state,
                'apartment' => $request->apartment,
                'business_reg_document' => $businessDoc,
                'identification_type' => $request->identification_type,
                'identification_type_document' => $identifyTypeDoc,
                'agree' => $request->agree,
            ]);

            return $this->success(null, 'Created successfully');

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function profile()
    {
        $auth = userAuth();

        $user = User::findOrFail($auth->id);

        $data = new SellerProfileResource($user);

        return $this->success($data, 'Seller profile');
    }

    public function editAccount($request)
    {
        $user = User::findOrFail($request->user_id);

        $image = $request->hasFile('logo') ? uploadUserImage($request, 'logo', $user) : $user->image;

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middlename' => $request->middlename,
            'email' => $request->email,
            'phone' => $request->phone,
            'image' => $image
        ]);

        return $this->success(null, "Updated successfully");
    }

    public function changePassword($request)
    {
        $user = $request->user();

        if (Hash::check($request->old_password, $user->password)) {
            $user->update([
                'password' => bcrypt($request->new_password),
            ]);

             return $this->success(null, 'Password Successfully Updated');

        }else {
            return $this->error(null, 422, 'Old Password did not match');
        }
    }

    public function editCompany($request)
    {
        $user = User::with('businessInformation')->findOrFail($request->user_id);

        $user->businessInformation()->update([
            'business_name' => $request->business_name,
            'business_reg_number' => $request->business_reg_number,
            'business_phone' => $request->business_phone,
            'country_id' => $request->country_id,
            'city' => $request->city,
            'address' => $request->address,
            'state' => $request->state,
        ]);

        return $this->success(null, "Updated successfully");
    }

    public function addProduct($request)
    {
        $user = User::find($request->user_id);

        if (! $user) {
            return $this->error(null, "User not found", 404);
        }

        try {

            $parts = explode('@', $user->email);
            $name = $parts[0];

            $res = folderNames('b2bproduct', $name, 'front_image');

            $slug = Str::slug($request->name);

            if (B2BProduct::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . uniqid();
            }

            if ($request->hasFile('front_image')) {
                $path = $request->file('front_image')->store($res->frontImage, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            $data = (array)[
                'user_id' => $user->id,
                'name' => $request->name,
                'slug' => $slug,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'keywords' => $request->keywords,
                'description' => $request->description,
                'front_image' => $url,
                'minimum_order_quantity' => $request->minimum_order_quantity,
                'unit' => $request->unit,
                'fob_price' => $request->fob_price,
                'country_id' => $user->country ?? 160,
            ];

            $product = $this->b2bProductRepository->create($data);

            if($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store($res->folder, 's3');
                    $url = Storage::disk('s3')->url($path);

                    $product->b2bProductImages()->create([
                        'image' => $url,
                    ]);
                }
            }

            return $this->success(null, 'Product added successfully', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getAllProduct($request)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::select('id')->findOrFail($request->user_id)->id;
        $search = request()->input('search');

        $products = $this->b2bProductRepository->all($user, $search);
        $data = B2BProductResource::collection($products);

        return $this->success($data, 'All products');
    }

    public function getProductById($user_id, $product_id)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $prod = $this->b2bProductRepository->find($product_id);
        $data = new B2BProductResource($prod);

        return $this->success($data, 'Product detail');
    }

    public function updateProduct($request)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::findOrFail($request->user_id);
        $prod = B2BProduct::findOrFail($request->product_id);

        $parts = explode('@', $user->email);
        $name = $parts[0];

        $res = folderNames('b2bproduct', $name, 'front_image');

        if ($request->name) {
            $slug = Str::slug($request->name);

            if (B2BProduct::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . uniqid();
            }
        } else {
            $slug = $prod->slug;
        }

        if ($request->hasFile('front_image')) {
            $path = $request->file('front_image')->store($res->frontImage, 's3');
            $url = Storage::disk('s3')->url($path);
        } else {
            $url = $prod->front_image;
        }

        $data = (array)[
            'user_id' => $user->id,
            'name' => $request->name ?? $prod->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'keywords' => $request->keywords,
            'description' => $request->description,
            'front_image' => $url,
            'minimum_order_quantity' => $request->minimum_order_quantity,
            'unit' => $request->unit,
            'fob_price' => $request->fob_price,
            'country_id' => $user->country ?? 160,
        ];

        $product = $this->b2bProductRepository->update($request->product_id, $data);

        if($request->hasFile('images')) {
            $product->b2bProductImages()->delete();
            foreach ($request->file('images') as $image) {
                $path = $image->store($res->folder, 's3');
                $url = Storage::disk('s3')->url($path);

                $product->b2bProductImages()->create([
                    'image' => $url,
                ]);
            }
        }

        return $this->success(null, 'Product updated successfully');
    }

    public function deleteProduct($user_id, $product_id)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $this->b2bProductRepository->delete($product_id);

        return $this->success(null, 'Deleted successfully');
    }

    public function getAnalytics($user_id)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with(['b2bProducts.category'])
            ->withCount(['b2bProducts', 'b2bProducts as category_count' => function ($query) {
                $query->distinct('category_id');
            }])
            ->findOrFail($user_id);

        $data = [
            'product_count' => $user->b2b_products_count,
            'category_count' => $user->category_count
        ];

        return $this->success($data, 'Analytics');
    }

    public function addShipping($request)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('b2bSellerShippingAddresses')->find($request->user_id);

        if (! $user) {
            return $this->error(null, "User not found", 404);
        }

        $data = (array)[
            'user_id' => $request->user_id,
            'address_name' => $request->address_name,
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'street' => $request->street,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'state_id' => $request->state_id,
            'country_id' => $request->country_id,
        ];

        $this->b2bSellerShippingRepository->create($data);

        return $this->success(null, 'Added successfully');
    }

    public function getAllShipping($user_id)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $address = $this->b2bSellerShippingRepository->all($user_id);
        $data = B2BSellerShippingAddressResource::collection($address);

        return $this->success($data, 'All address');
    }

    public function getShippingById($user_id, $shipping_id)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $shipping = $this->b2bSellerShippingRepository->find($shipping_id);
        $data = new B2BSellerShippingAddressResource($shipping);

        return $this->success($data, 'Address detail');
    }

    public function updateShipping($request, $shipping_id)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $data = (array)[
            'address_name' => $request->address_name,
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'street' => $request->street,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'state_id' => $request->state_id,
            'country_id' => $request->country_id,
        ];

        $this->b2bSellerShippingRepository->update($shipping_id, $data);

        return $this->success(null, 'Updated successfully');
    }

    public function deleteShipping($user_id, $shipping_id)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $this->b2bSellerShippingRepository->delete($shipping_id);

        return $this->success(null, 'Deleted successfully');
    }

    public function setDefault($user_id, $shipping_id)
    {
        $currentUserId = auth()->id();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $shipping = B2BSellerShippingAddress::where('user_id', $user_id)
            ->where('id', $shipping_id)
            ->firstOrFail();

        if($shipping->is_default) {
            return $this->error(null, 'Already set at default', 400);
        }

        B2BSellerShippingAddress::where('user_id', $user_id)->update(['is_default' => 0]);

        $shipping->update([
            'is_default' => 1
        ]);

        return $this->success(null, 'Set successfully');
    }

    public function getComplaints($user_id)
    {
        $user = User::with(['b2bProducts.b2bRequestRefunds'])->findOrFail($user_id);

        $refunds = $user->b2bProducts->flatMap(function ($product) {
            return $product->b2bRequestRefunds;
        });

        if ($complaintNumber = request()->query('complaint_number')) {
            $refunds = $refunds->where('complaint_number', $complaintNumber);
        }

        if ($type = request()->query('type')) {
            $refunds = $refunds->where('type', $type);
        }

        if ($status = request()->query('status')) {
            $refunds = $refunds->where('status', $status);
        }

        if ($fromDate = request()->query('from') && $toDate = request()->query('to')) {
            $refunds = $refunds->whereBetween('created_at', [$fromDate, $toDate]);
        }

        if ($orderNo = request()->query('order_number')) {
            $refunds = $refunds->where('order_number', $orderNo);
        }

        return $refunds;
    }

    public function getTemplate()
    {
        $data = getB2BProductTemplate();

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
                return $this->b2bExportProduct($userId);
                break;

            case 'order':
                return "None yet";
                break;

            default:
                return "Type not found";
                break;
        }
    }

    public function getEarningReport($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $data = (object) [
            'total_sales_alltime' => 0,
            'sales_this_month' => 0,
            'total_payout' => 0,
            'payout_this_month' => 0,
            'total_category' => 0,
            'total_brand' => 0,
        ];
    }
}










