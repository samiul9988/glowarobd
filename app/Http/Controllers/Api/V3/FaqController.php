<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\V3\FaqsCollection;

class FaqController extends Controller
{

    public function getFaq($id)
    {
        $faq = Faq::find($id);

        if ($faq) {
            return new FaqsCollection(collect([$faq]));
        }
        return response()->json([
            'success' => false,
            'message' => 'FAQ not found',
            'status' => 404,
        ], 404);
    }


    // function fetchFaqs(Request $request)
    // {
    //     $where_cound = 'id != "" ';
    //     $sort = 'desc';
    //     $faqs = DB::table('faqs')
    //         ->select(DB::raw('*'))
    //         ->whereRaw($where_cound)
    //         ->orderBy('id', $sort)->get();

    //     $totalcount = $faqs->count();
    //     $perPage = $request->get('per_page', 10);
    //     $currentPage = $request->get('page', 1);
    //     $pagedData = $faqs->slice(($currentPage - 1) * $perPage, $perPage)->all();
    //     $faqss = new \Illuminate\Pagination\LengthAwarePaginator($pagedData, count($faqs), $perPage);
    //     $totalPages = ceil($totalcount / $perPage);

    //     return response()->json([
    //         'success' => true,
    //         'data' =>  $faqss,
    //         'total' => $totalcount,
    //         'per_page' => intval($perPage),
    //         'current_page' => intval($currentPage),
    //         'last_page' => $totalPages
    //     ]);
    // }

    
    function fetchFaqs(Request $request)
    {
        // Default per page limit
        $limit = $request->get('limit', 15);
        $currentPage = $request->get('page', 1);
        $sort = 'desc';

        // Initial query with category relationship
        $faqs = DB::table('faqs')
            // ->join('categories','faqs.category_id','=','categories.id')
            ->select('faqs.*')
            ->orderBy('faqs.id', $sort);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->input('search');
            $faqs->where(function ($query) use ($search) {
                $query->where('faqs.question', 'LIKE', '%' . $search . '%')
                ->orWhere('faqs.answer', 'LIKE', '%' . $search . '%');
                // ->orWhere('categories.name', 'LIKE', '%' . $search . '%'); 
            });
        }

        // Get paginated results
        $totalcount = $faqs->count();  
        $pagedData = $faqs->skip(($currentPage - 1) * $limit)->take($limit)->get(); 

        // Create pagination object
        $faqss = new \Illuminate\Pagination\LengthAwarePaginator($pagedData, $totalcount, $limit, $currentPage);

        // Return the response with paginated and filtered data
        return response()->json([
            'success' => true,
            'data' => $faqss->items(),  
            'total' => $faqss->total(), 
            'per_page' => $faqss->perPage(), 
            'current_page' => $faqss->currentPage(), 
            'last_page' => $faqss->lastPage()
        ]);
    }

}
