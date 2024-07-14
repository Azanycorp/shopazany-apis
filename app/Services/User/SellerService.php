<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\UserBusinessInformation;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\App;
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

