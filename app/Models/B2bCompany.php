<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id for b2b entities only
 * @property string|null $business_name
 * @property string|null $tax_id
 * @property string|null $business_reg_number
 * @property string|null $business_phone
 * @property string|null $company_size
 * @property string|null $website
 * @property array<array-key, mixed> $service_type
 * @property string|null $average_spend
 * @property string|null $country_id
 * @property string|null $city
 * @property string|null $address
 * @property string|null $state
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $logo
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereAverageSpend($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereBusinessPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereBusinessRegNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereCompanySize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bCompany whereWebsite($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'business_name',
    'tax_id',
    'business_reg_number',
    'business_phone',
    'company_size',
    'website',
    'service_type',
    'average_spend',
    'country_id',
    'city',
    'address',
    'state',
    'logo',
])]
class B2bCompany extends Model
{
    protected function casts(): array
    {
        return [
            'service_type' => 'array',
        ];
    }
}
