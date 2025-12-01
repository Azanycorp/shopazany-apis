<?php

namespace App\Pipelines\BusinessInformation;

use App\Trait\HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateBusinessInformation
{
    use HttpResponse;

    public function handle($request)
    {
        $user = $request->user;

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middlename' => $request->middlename,
            'country' => $request->country_id,
        ]);

        $folder = folderName('document/businessreg');

        $businessDoc = $request->hasFile('business_reg_document') ?
            uploadImage($request, 'business_reg_document', $folder) :
            ['url' => null];

        $identifyTypeDoc = ['url' => null];

        if ($request->identification_type && $request->hasFile('identification_type_document')) {
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
            'business_reg_document' => $businessDoc['url'],
            'identification_type' => $request->identification_type,
            'identification_type_document' => $identifyTypeDoc['url'],
            'agree' => $request->agree,
        ]);

        return $this->success(null, 'Created successfully', Response::HTTP_CREATED);
    }
}
