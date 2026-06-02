<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $iso
 * @property string $iso3
 * @property string $dial
 * @property string|null $currency
 * @property string|null $currency_name
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryCurrency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryCurrency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryCurrency query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryCurrency whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryCurrency whereCurrencyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryCurrency whereDial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryCurrency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryCurrency whereIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryCurrency whereIso3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryCurrency whereName($value)
 *
 * @mixin \Eloquent
 */
class CountryCurrency extends Model
{
    //
}
