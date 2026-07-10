<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = Template::active()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(15);
        return view('backend.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('backend.templates.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'content' => 'required|string',
            'status' => 'boolean',
        ]);

        Template::create($validatedData);

        flash(('Template created successfully'))->success();
        return to_route('templates.index');
    }

    public function edit(int $id)
    {
        $template = Template::findOrFail($id);
        return view('backend.templates.edit', compact('template'));
    }

    public function update(Request $request, int $id)
    {
        // dd($request->all(), $id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'content' => 'required|string',
            'status' => 'boolean',
        ]);

        $template = Template::findOrFail($id);
        $template->update($validatedData);

        flash(('Template updated successfully'))->success();
        return to_route('templates.index');
    }

    public function destroy(int $id)
    {
        // Delete the template
    }
}
