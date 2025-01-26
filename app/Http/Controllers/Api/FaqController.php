<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Trait\HttpResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use HttpResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Faq::get(['id', 'question', 'answer']);

        return $this->success($data, "FAQs");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Faq::create([
            'question' => $request->question,
            'answer' => $request->answer
        ]);
        return $this->success(null, "Added successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show(Faq $faq)
    {
        $data = Faq::findOrFail($faq->id);

        $data = [
            'id' => $data->id,
            'question' => $data->question,
            'answer' => $data->answer,
        ];

        return $this->success($data, "FAQ Detail");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Faq $faq)
    {
        $data = Faq::findOrFail($faq->id);

        $data->update([
            'question' => $request->question,
            'answer' => $request->answer
        ]);

        return $this->success(null, "Updated successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq)
    {
        $data = Faq::findOrFail($faq->id);

        $data->delete();

        return $this->success(null, "Deleted successfully");
    }
}
