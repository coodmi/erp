<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerV;


class LedgerVController extends Controller
{
    // ---------------------------------------




    public function index()
    {
        return view('vledger.index');
    }

    public function create()
    {
        return view('vledger.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required',
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

        LedgerV::create($request->all());

        return redirect()->back()
            ->with('success', 'Ledger created successfully.');
    }

    public function show(LedgerV $ledger)
    {
        return view('vledger.show', compact('ledger'));
    }

    public function edit(LedgerV $ledger)
    {
        return view('vledger.edit', compact('ledger'));
    }

    public function update(Request $request, LedgerV $ledger)
    {
        $request->validate([
            'vendor_id' => 'required',
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

    public function destroy(LedgerV $ledger)
    {
        $ledger->delete();

        return redirect()->back()
            ->with('success', 'Ledger deleted successfully');
    }

    public function getLedger()
    {
        $ledger = LedgerV::all();

        return response()->json($ledger);
    }

    public function getLedgerById($id)
    {
        $ledger = LedgerV::find($id);

        return response()->json($ledger);
    }









    // ---------------------------------------
}
