<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Trait\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    use HttpResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
        ]);
        $path = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().rand(10, 1000).'.'.$file->extension();
            $file->move(public_path('brands'), $filename, 'public');
            $path = config('services.baseurl').'brands/'.$filename;
        }
        Brand::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'image' => $path,
        ]);

        return $this->success(null, 'Created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): void
    {
        //
    }
}
