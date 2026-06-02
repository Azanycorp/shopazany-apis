<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read Category|null $category
 * @property-read Collection<int, SizeChartDetail> $sizeChartDetails
 * @property-read int|null $size_chart_details_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeChart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeChart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeChart query()
 *
 * @mixin \Eloquent
 */
#[Table(name: 'size_charts')]
class SizeChart extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sizeChartDetails()
    {
        return $this->hasMany(SizeChartDetail::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
