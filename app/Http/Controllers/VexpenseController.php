<?php

namespace App\Http\Controllers;


use App\Models\Vexpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class VexpenseController extends Controller
{
    public function index()
    {
        $vexpense = Vexpense::all();
        return view('vexpense.index', ['vexpense' => $vexpense]);
    }

    public function create()
    {
        return view('vexpenses.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'expense_name' => 'required|string|max:255',
            'expense_type' => 'string|max:255',
            'expense_amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'remarks' => 'nullable|string',
            'vexcat_id' => 'required|exists:vexcat,id',
        ]);

        DB::table('vexpense')->insert([
            'expense_name' => $validatedData['expense_name'],
            'expense_type' => $validatedData['expense_type'],
            'expense_amount' => $validatedData['expense_amount'],
            'expense_date' => $validatedData['expense_date'],
            'remarks' => $validatedData['remarks'],
            'vexcat_id' => $validatedData['vexcat_id'],
        ]);


        return redirect()->back()->with('success', 'Expense added successfully!');

    }

    public function edit(Vexpense $vexpense)
    {
        return view('vexpenses.edit', ['vexpense' => $vexpense]);
    }

    public function update(Request $request, Vexpense $vexpense)
    {
        $validatedData = $request->validate([
            'expense_name' => 'required|string|max:255',
            'expense_type' => 'required|string|max:255',
            'expense_amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $vexpense->update($validatedData);

        return redirect()->back()->with('success', 'Expense updated successfully!');
    }
    
      public function destroy(Vexpense $vexpense)
    {
        $vexpense->delete();
        return redirect()->back()->with('success', 'Expense deleted successfully!');
    }
    
}
