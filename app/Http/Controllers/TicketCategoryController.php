<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TicketCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketCategoryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $date = $request->input('date');
        $parent_id = $request->input('parent');
        $search = $request->input('search');
        $categories = TicketCategory::latest()->with('parent', 'childs.tickets')->withCount('tickets')
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($date, function ($query) use ($date) {
                $breakDate = explode(' to ', $date);

                if (count($breakDate) === 2) {
                    $start_date = Carbon::parse($breakDate[0])->startOfDay()->format('Y-m-d H:i:s');
                    $end_date = Carbon::parse($breakDate[1])->endOfDay()->format('Y-m-d H:i:s');

                    $query->whereBetween('created_at', [$start_date, $end_date]);
                }
            })
            ->when($parent_id, function ($query) use ($parent_id) {
                return $query->where('parent_id', $parent_id);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->paginate(25);
        return view('backend.support.tickets_categories.index', compact('categories', 'status', 'date', 'parent_id', 'search'));
    }

    public function create()
    {
        return view('backend.support.tickets_categories.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'parent' => 'nullable|exists:ticket_categories,id',
            ]);

            TicketCategory::create([
                'name' => ucwords($request->name),
                'slug' => Str::slug($request->slug ?: $request->name),
                'parent_id' => $request->parent ?? null,
                'description' => $request->description ?? null,
                'status' => $request->status ?? 1,
            ]);

            flash(('Ticket category created successfully.'))->success();
            return redirect()->route('ticket_categories.index');
        } catch (ValidationException $e) {
            flash(($e->validator->errors()->first()))->error();
            return redirect()->back()->withInput();
        } catch (\Exception $e) {
            flash(('Server Error'))->error();
            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        $category = TicketCategory::findOrFail($id);
        return view('backend.support.tickets_categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'parent' => 'nullable|exists:ticket_categories,id',
            ]);

            $category = TicketCategory::findOrFail($id);

            $category->update([
                'name' => ucwords($request->name),
                'slug' => Str::slug($request->slug ?: $request->name),
                'parent_id' => $request->parent ?? null,
                'description' => $request->description ?? null,
                'status' => $request->status ?? 1,
            ]);

            flash(('Ticket category updated successfully.'))->success();
            return redirect()->route('ticket_categories.index');
        } catch (ValidationException $e) {
            flash(($e->validator->errors()->first()))->error();
            return redirect()->back()->withInput();
        } catch (\Exception $e) {
            flash(('Server Error'))->error();
            return redirect()->back()->withInput();
        }
    }

    public function bulkStatusUpdate(Request $request)
    {
        dd($request->all());
        $ids = $request->input('id', []);
        $status = $request->input('status');

        try {
            DB::beginTransaction();
            TicketCategory::whereIn('id', $ids)->update(['status' => $status]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => ('Ticket categories status updated successfully.')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => ('Request Failed! Please try again later.')
            ], 500);
        }
    }

    public function getSubCategories(Request $request)
    {
        $parentId = $request->input('category_id');
        $subCategories = \App\Models\TicketCategory::active()->whereNotNull('parent_id')->where('parent_id', $parentId)->select('name', 'id', 'slug')->get();
        return response()->json([
            'success' => true,
            'data' => $subCategories->toArray(),
            'message' => ('Subcategories fetched successfully.')
        ]);
    }
}
