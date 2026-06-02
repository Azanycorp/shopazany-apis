<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeChartDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeChartDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeChartDetail query()
 *
 * @mixin \Eloquent
 */
#[Table(name: 'size_chart_details')]
class SizeChartDetail extends Model
{
    use HasFactory;

    protected $guarded = [];
}
