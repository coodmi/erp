<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class CountryController extends Controller
{
    
    // Show all countries
    public function index()
    {
        $countries = Country::all();
        return view('countries.index', ['countries' => $countries]);
    }
    
    public function setid($id)
    {
        Session::put('country_id',$id);
        return response()->json('success',200);
    }
    
    public function setstatus($status)
    {
        Session::put('c_status',$status);
        return response()->json('success',200);
    }


    // Show the form to create a new country
    public function create()
    {
        return view('countries.create');
    }

    // Store a newly created country in the database
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'country_name' => 'nullable|string|max:255',
        ]);

        DB::table('countries')->insert([
            'country_name' => $validatedData['country_name'],
           
        ]);

        // Redirect back to the previous page or any other page
        return redirect()->back()->with('success', 'Country created successfully!');
    }

    // Show the form to edit a specific country
    public function edit(Country $country)
    {
        return view('countries.edit', ['country' => $country]);
    }

    // Update a specific country in the database

    public function update(Request $request, Country $country)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'country_name' => 'required|string|max:255',
        ]);

        // Update the specific country in the database
        $country->country_name = $validatedData['country_name'];
        $country->save();

        // Redirect back to the previous page or any other page
        return redirect()->back()->with('success', 'Country updated successfully!');
    }

    // Delete a specific country from the database

    public function destroy(Country $country)
    {
        // Delete the specific country from the database
        $country->delete();

        // Redirect back to the previous page or any other page
        return redirect()->back()->with('success', 'Country deleted successfully!');
    }


}
