<?php

namespace App\Http\Controllers;

use App\Models\Vexcat;

use Illuminate\Http\Request;

class VexcatController extends Controller
{

    // Show all vexcat
    public function index()
    {
        $vexcat = Vexcat::all();
        return view('vexcat.index', ['vexcat' => $vexcat]);
    }

    // Show the form to create a new vexcat
    public function create()
    {
        return view('vexcat.create');
    }

    // Store a newly created vexcat in the database
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'category_name' => 'required|string|max:255',
            'category_description' => 'nullable|string|max:255',
        ]);

        // Insert data into the database
        $vexcat = Vexcat::create($validatedData);

        return redirect()->back()->with('success', 'Category created successfully!');

    }

    // Show a single vexcat
    public function show($id)
    {
        $vexcat = Vexcat::findOrFail($id);
        return view('vexcat.show', ['vexcat' => $vexcat]);
    }

    // Show the form to edit a vexcat
    public function edit($id)
    {
        $vexcat = Vexcat::findOrFail($id);
        return view('vexcat.edit', ['vexcat' => $vexcat]);
    }

    // Update a vexcat in the database
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'category_name' => 'required|string|max:255',
            'category_description' => 'required|string|max:255',
        ]);

        // Update the vexcat in the database
        Vexcat::whereId($id)->update($validatedData);

        return redirect()->back()->with('success', 'Category edited successfully!');
    }

    // Remove a vexcat from the database
    public function destroy($id)
    {
        Vexcat::whereId($id)->delete();
        return redirect()->back()->with('success', 'Category deleted successfully!');

    }
}
