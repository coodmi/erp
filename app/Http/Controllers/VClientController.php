<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;
use App\Models\VClient;

class VClientController extends Controller
{

    function generateRandomString($length = 6) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = 'CLT'; // Start with "CLT"
        $randomString .= $characters[rand(10, 35)]; 
        for ($i = 1; $i < $length; $i++) { 
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        // for ($i = 0; $i < 2; $i++) { 
        //     $randomString .= $characters[rand(10, 35)];
        // }
        return $randomString;
    }
    // Show all VClients
    public function index()
    {
        $vclients = VClient::all();
        return view('vclients.index', ['vClients' => $vclients]);
    }

    // Show the form to create a new VClient
    public function create()
    {
        return view('vclients.create');
    }

    // Store a newly created VClient in the database
    public function store(Request $request)
    {
        $uniqueCode = $this->generateRandomString();

        // Normalise nullable foreign keys and boolean before validation
        $request->merge([
            'agent_id'       => $request->filled('agent_id')       ? $request->agent_id       : null,
            'vendor_id'      => $request->filled('vendor_id')      ? $request->vendor_id      : null,
            'visa_country_id'=> $request->filled('visa_country_id')? $request->visa_country_id: null,
            'amount_paid'    => $request->filled('amount_paid')    ? $request->amount_paid    : 0,
            'amount_due'     => $request->filled('amount_due')     ? $request->amount_due     : 0,
            'isTicket'       => in_array($request->input('isTicket'), [1, '1', true, 'true'], true) ? 1 : 0,
            'status'         => $request->filled('status')         ? $request->status         : 'submitted',
        ]);

        // Single validation block — works for both agent-linked and standalone clients
        $validatedData = $request->validate([
            'client_name'    => 'required|string|max:255',
            'address'        => 'nullable|string|max:255',
            'passport_no'    => 'nullable|string|max:255',
            'visa_type'      => 'nullable|string|max:255',
            'amount_paid'    => 'nullable|numeric|min:0',
            'amount_due'     => 'nullable|numeric|min:0',
            'isTicket'       => 'nullable|integer',
            'status'         => 'nullable|string|max:255',
            'attachment'     => 'nullable|file',
            'agent_id'       => 'nullable|exists:agents,id',
            'vendor_id'      => 'nullable|exists:vendors,id',
            'visa_country_id'=> 'nullable|exists:countries,id',
        ]);

        if ($request->hasFile('attachment')) {
            $file     = $request->file('attachment');
            $filePath = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');
            $validatedData['attachment'] = $filePath;
        } else {
            unset($validatedData['attachment']);
        }

        // Duplicate passport check (only when passport_no is provided)
        $passport_no = $validatedData['passport_no'] ?? null;
        if ($passport_no && VClient::where('passport_no', $passport_no)->exists()) {
            $redirectUrl = $request->input('_redirect_back');
            if ($redirectUrl && str_contains($redirectUrl, '/vclients')) {
                return redirect($redirectUrl)->with('error', 'A client with this passport number already exists!');
            }
            return redirect()->back()->with('error', 'A client with this passport number already exists!');
        }

        DB::table('clients')->insert([
            'client_name'    => $validatedData['client_name'],
            'address'        => $validatedData['address']        ?? null,
            'passport_no'    => $validatedData['passport_no']    ?? null,
            'visa_type'      => $validatedData['visa_type']      ?? null,
            'visa_country_id'=> $validatedData['visa_country_id']?? null,
            'unique_code'    => $uniqueCode,
            'agent_id'       => $validatedData['agent_id']       ?? null,
            'vendor_id'      => $validatedData['vendor_id']      ?? null,
            'amount_paid'    => $validatedData['amount_paid']    ?? 0,
            'amount_due'     => $validatedData['amount_due']     ?? 0,
            'isTicket'       => $validatedData['isTicket']       ?? 0,
            'status'         => $validatedData['status']         ?? 'submitted',
            'attachment'     => $validatedData['attachment']     ?? null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Redirect back to the correct filtered view (preserve visa_type / all param)
        $redirectUrl = $request->input('_redirect_back');
        if ($redirectUrl && str_contains($redirectUrl, '/vclients')) {
            return redirect($redirectUrl)->with('success', 'Client created successfully!');
        }

        // Fallback: redirect to the visa_type filtered view if visa_type was provided
        $visaType = $validatedData['visa_type'] ?? null;
        if ($visaType) {
            return redirect('/vclients?visa_type=' . urlencode($visaType))->with('success', 'Client created successfully!');
        }

        return redirect('/vclients?all=1')->with('success', 'Client created successfully!');
    }

    // Show the form to edit a VClient
    public function edit(VClient $vclient)
    {
        return view('vclients.edit', ['vclient' => $vclient]);
    }

    // Update the specified VClient in the database
    public function update(Request $request, VClient $vclient)
    { 
        // Validate the request data
        $validatedData = [];
       
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filePath = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');
            $validatedData['attachment'] = $filePath;
            $filePath1=$filePath;
            // Generate the URL for the uploaded file
            $fileUrl1 = Storage::disk('public')->url($filePath);
        
            // Use $fileUrl as needed
        }else {
            // If no file is uploaded, remove the attachment key from the validated data
            unset($validatedData['attachment2']);
        }
        if ($request->hasFile('attachment2')) {
            $file = $request->file('attachment2');
            $filePath = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');
            $validatedData['attachment2'] = $filePath;
            $filePath2=$filePath;
            // Generate the URL for the uploaded file
            $fileUrl = Storage::disk('public')->url($filePath);
        
            // Use $fileUrl as needed
        }else {
            // If no file is uploaded, remove the attachment key from the validated data
            unset($validatedData['attachment2']);
        }
        if ($request->hasFile('attachmen3')) {
            $file = $request->file('attachmen3');
            $filePath = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');
            $validatedData['attachmen3'] = $filePath;
            $filePath3=$filePath;
            // Generate the URL for the uploaded file
            $fileUrl = Storage::disk('public')->url($filePath);
        
            // Use $fileUrl as needed
        }else {
            // If no file is uploaded, remove the attachment key from the validated data
            unset($validatedData['attachmen3']);
        }
        if ($request->hasFile('attachment4')) {
            $file = $request->file('attachment4');
            $filePath = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');
            $validatedData['attachment4'] = $filePath;
            $filePath4=$filePath;
            // Generate the URL for the uploaded file
            $fileUrl = Storage::disk('public')->url($filePath);
        
            // Use $fileUrl as needed
        }else {
            // If no file is uploaded, remove the attachment4 key from the validated data
            unset($validatedData['attachment4']);
        }
         
        
        $c=Vclient::find($vclient->id);
        $c->client_name=$request->client_name;
        $c->address=$request->address;
        $c->passport_no=$request->passport_no;
        $c->visa_type=$request->visa_type; 
        $c->amount_due=$request->amount_due;
        $c->isTicket=$request->isTicket; 
        $c->status=$request->status;
        if(isset($fileUrl1)){
             $c->attachment=$fileUrl1;
        }
        
        if(isset($filePath2)){
             $c->attachment2=$filePath2;
        }
        
        if(isset($filePath3)){
             $c->attachmen3=$filePath3;
        }
        
        if(isset($filePath4)){
             $c->attachment4=$filePath4;
        }
        
         
        $c->agent_id=$request->agent_id;
        $c->vendor_id=$request->vendor_id;
        $c->visa_country_id=$request->visa_country_id;
        $c->unit_price=$request->unit_price;
        $c->refund=$request->refund; 
        $c->amount_paid=$vclient->amount_paid+$request->amount_paid_new; 
        // Update the VClient
        $c->update();

        // Redirect to the VClients index page with a success message
        
        return redirect()->back()->with('success', 'Client updated successfully!');

        // return redirect()->route('vclients.index')->with('success', 'VClient updated successfully.');
    }

    // Delete the specified VClient from the database
    public function destroy(VClient $vclient)
    {
        $vclient->delete();

        // Redirect to the VClients index page with a success message
        return redirect()->back()->with('success', 'Client deleted successfully!');

        // return redirect()->route('vclients.index')->with('success', 'VClient deleted successfully.');
    }
}
