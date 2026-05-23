<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vendor;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{

    function generateRandomString($length = 6) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = 'VDR';
        $randomString .= $characters[rand(10, 35)]; 
        for ($i = 1; $i < $length; $i++) { 
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        // for ($i = 0; $i < 2; $i++) { 
        //     $randomString .= $characters[rand(10, 35)];
        // }
        return $randomString;
    }
    // Show all vendors
    public function index()
    {
        $vendors = Vendor::all();
        return view('vendors.index', ['vendors' => $vendors]);
    }

    // Show the form to create a new vendor
    public function create()
    {
        return view('vendors.create');
    }

    // Store a newly created vendor in the database
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'company_details' => 'required|string|max:255',
            'visa_type' => 'required|string|max:255',
            'visa_country_id' => 'exists:countries,id|nullable',
        ]);


        $uniqueCode = $this->generateRandomString();
        // Insert data into the database

        DB::table('vendors')->insert([
            'vendor_name' => $validatedData['vendor_name'],
            'company_details' => $validatedData['company_details'],
            'visa_type' => $validatedData['visa_type'],
            'unique_code' => $uniqueCode,
            'visa_country_id' => $validatedData['visa_country_id'],
        ]);

        // Redirect back to the previous page or any other page
        return redirect()->back()->with('success', 'Vendor created successfully!');
        
    }

    // Show the form to edit a vendor
    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', ['vendor' => $vendor]);
    }

    // Update the specified vendor in the database
    public function update(Request $request, Vendor $vendor)
    {
        \Log::debug($request->all());

        // Validate the request data
        $validatedData = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'company_details' => 'required|string',
            // 'attachment' => 'nullable|file',
            'amount_paid_new' => 'nullable|numeric|min:0',
            'amount_due' => 'nullable|numeric|min:0',
            'visa_type' => 'nullable|string|max:255',
            'unit_price' => 'nullable|numeric|min:0',
            'refund' => 'nullable|numeric|min:0',
            'visa_country_id' => 'exists:countries,id|nullable',
            'attachment' => 'nullable|file',
            'attachment2' => 'nullable|file',
            'attachmen3' => 'nullable|file',
            'attachment4' => 'nullable|file',

            
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

        $validatedData['amount_paid'] = $vendor->amount_paid + $validatedData['amount_paid_new'];

        // Update the vendor
        $vendor->update($validatedData);

        // Redirect to the vendors index page with a success message
        return redirect()->back()->with('success', 'Vendor updated successfully!');
    
    }

    // Delete the specified vendor from the database
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        // Redirect to the vendors index page with a success message
        return redirect()->back()->with('success', 'Vendor deleted successfully!');

    }
}
