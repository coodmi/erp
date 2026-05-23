<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Agent;
use Illuminate\Support\Facades\Storage;


class AgentController extends Controller
{

    function generateRandomString($length = 7) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = 'AGT'; // Start with "AGT"
        $randomString .= $characters[rand(10, 35)]; // Ensure the first character is a letter
        for ($i = 1; $i < $length - 3; $i++) { // Adjust the loop to accommodate the "AGT" prefix
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        for ($i = 0; $i < 2; $i++) { // Ensure the last two characters are letters
            $randomString .= $characters[rand(10, 35)];
        }
        return $randomString;
    }

    public function index()
    {
        $agents = Agent::all();
        return view('agents.index', ['agents' => $agents]);
    }
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'agent_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            // 'passport_number' => 'required|string|max:255',
            'visa_type' => 'required|string|max:255',
            'visa_country_id' => 'required|exists:countries,id',
        ]);

        // Generate unique code
    // $uniqueCode = 'AGT' . str_pad(Agent::count() + 1, 6, '0', STR_PAD_LEFT);
    $uniqueCode = $this->generateRandomString();

    // Insert data into the database
    DB::table('agents')->insert([
        'agent_name' => $validatedData['agent_name'],
        'address' => $validatedData['address'],
        'unique_code' => $uniqueCode,
        'visa_type' => $validatedData['visa_type'],
        'visa_country_id' => $validatedData['visa_country_id'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

        // Redirect back to the previous page or any other page
        return redirect()->back()->with('success', 'Agent created successfully!');
    }

    public function edit(Agent $agent)
    {
        return view('agents.edit', ['agent' => $agent]);
    }

    public function update(Request $request, Agent $agent)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'agent_name' => 'string|max:255',
            'address' => 'string|max:255',
            'passport_number' => 'string|max:255',
            'visa_type' => 'nullable|string|max:255',
            'amount_paid_new' => 'nullable|numeric|min:0',
            'amount_due' => 'nullable|numeric|min:0',
            'attachment' => 'nullable|file',
            'attachment2' => 'nullable|file',
            'attachmen3' => 'nullable|file',
            'attachment4' => 'nullable|file',
            'visa_country_id' => 'exists:countries,id|nullable',
            'unit_price' => 'nullable|numeric|min:0',
            'refund' => 'nullable|numeric|min:0',

        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filePath = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');
            $validatedData['attachment'] = $filePath;
        
            // Generate the URL for the uploaded file
            $fileUrl = Storage::disk('public')->url($filePath);
        
            // Use $fileUrl as needed
        }else {
            // If no file is uploaded, remove the attachment key from the validated data
            unset($validatedData['attachment2']);
        }
        if ($request->hasFile('attachment2')) {
            $file = $request->file('attachment2');
            $filePath = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');
            $validatedData['attachment2'] = $filePath;
        
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
        
            // Generate the URL for the uploaded file
            $fileUrl = Storage::disk('public')->url($filePath);
        
            // Use $fileUrl as needed
        }else {
            // If no file is uploaded, remove the attachment4 key from the validated data
            unset($validatedData['attachment4']);
        }

        $validatedData['amount_paid'] = $agent->amount_paid + $validatedData['amount_paid_new'];

        // Update the specified agent in the database
        $agent->update($validatedData);


        // Redirect back to the previous page or any other page
        return redirect()->back()->with('success', 'Agent updated successfully!');
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();

        // Redirect to the VClients index page with a success message
        return redirect()->back()->with('success', 'Agent deleted successfully!');

        // return redirect()->route('vclients.index')->with('success', 'VClient deleted successfully.');
    }
}
