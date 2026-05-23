<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ledger;


class LedgerController extends Controller
{
    //

    public function index()
    {
        return view('ledger.index');
    }

    public function create()
    {
        return view('ledger.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required',
            'date' => 'required',
            'paid_for' => 'required',
            'unit_pirce' => 'required',
            'number_of_unit' => 'required',
            'payment_mode' => 'required',
            'amount' => 'required',
            'advance' => 'required',
            'due' => 'required',
            'refund' => 'required',
        ]);

        Ledger::create($request->all());

        return redirect()->back()
            ->with('success', 'Ledger created successfully.');
    }

    public function show(Ledger $ledger)
    {
        return view('ledger.show', compact('ledger'));
    }

    public function edit(Ledger $ledger)
    {
        return view('ledger.edit', compact('ledger'));
    }
    
    public function editinfo($id)
    {
        $ledger=Ledger::where('id',$id)->first();
        return response()->json($ledger, 200);
    }


    public function update(Request $request, Ledger $ledger)
    {
        $request->validate([
            'agent_id' => 'required',
            'date' => 'required',
            'paid_for' => 'required',
            'unit_pirce' => 'required',
            'number_of_unit' => 'required',
            'payment_mode' => 'required',
            'amount' => 'required',
            'advance' => 'required',
            'due' => 'required',
            'deposit' => 'required',
            'refund' => 'required',
        ]);

        $ledger->update($request->all());

        return redirect()->back()->with('success', 'Ledger updated successfully');
    }
    
    public function updateinfo(Request $request)
    {
        $ledger=Ledger::where('id',$request->ledger_id)->first();
        $request->validate([
            'agent_id' => 'required',
            'date' => 'required',
            'paid_for' => 'required',
            'unit_pirce' => 'required',
            'number_of_unit' => 'required',
            'payment_mode' => 'required',
            'amount' => 'required',
            'advance' => 'required',
            'due' => 'required',
            'deposit' => 'required',
            'refund' => 'required',
        ]);

        $ledger->update($request->all());

        return redirect()->back()->with('success', 'Ledger updated successfully');
    }
    

    public function destroy(Ledger $ledger)
    {
        $ledger->delete();

        return redirect()->back()
            ->with('success', 'Ledger deleted successfully');
    }

    public function getLedger()
    {
        $ledger = Ledger::all();

        return response()->json($ledger);
    }

    public function getLedgerById($id)
    {
        $ledger = Ledger::find($id);

        return response()->json($ledger);
    }
}
