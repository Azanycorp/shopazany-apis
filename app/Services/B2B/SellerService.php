<?php

namespace App\Services\B2B;

use App\Http\Resources\SellerProfileResource;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Hash;

class SellerService
{
    use HttpResponse;

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
}










