<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FaqController extends Controller
{

    public function index()
    {
        $search = '';
        if (request()->has('search')) {
            $search = request()->input('search');
            $faqs = Faq::orderBy('created_at', 'desc')
            ->where('question', 'LIKE', '%' . $search . '%')
            ->orWhere('answer', 'LIKE', '%' . $search . '%')
            ->paginate();

        } else {
            $faqs = Faq::orderBy('created_at', 'desc')->paginate();
        }

        return view('backend.faqs.index', compact('faqs', 'search'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        try {
            $request->validate([
                'question' => 'required',
                'answer' => 'required'
            ]);

            // Create record
            Faq::create([
                'question' => $request->input('question'),
                'answer' => $request->input('answer'),
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully Created'
            ]);

            // laravel by default error message
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ]);

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong!' . $exception->getMessage(),
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate request
            $request->validate([
                'question' => 'required',
                'answer' => 'required',
            ]);

            // Find the FAQ by ID, if not found throw a 404 error
            $faq = Faq::findOrFail($id);

            // Update the record
            $faq->update([
                'question' => $request->input('question'),
                'answer' => $request->input('answer'),
            ]);

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully Updated',
            ]);
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors(),
            ]);
        } catch (Exception $exception) {
            // Log and return any other exception
            Log::error($exception->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong! ' . $exception->getMessage(),
            ]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->input('id'); // Get the ID from the request
            Faq::destroy($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Faq has been deleted successfully'
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong! ' . $exception->getMessage(),
            ]);
        }
    }


}
