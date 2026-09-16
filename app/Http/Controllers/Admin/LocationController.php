<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the locations.
     */
    public function index()
    {
        $locations = Location::withCount(['tickets'])->latest()->paginate(10);
        return view('admin.locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new location.
     */
    public function create()
    {
        return view('admin.locations.create');
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
        ]);

        Location::create($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', "Location '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified location.
     */
    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
        ]);

        $location->update($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', "Location '{$validated['name']}' updated successfully.");
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(Location $location)
    {
        if ($location->tickets()->exists()) {
            return redirect()->route('admin.locations.index')
                ->with('error', "Cannot delete Location '{$location->name}' because it has active tickets associated with it.");
        }

        $locationName = $location->name;
        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', "Location '{$locationName}' deleted successfully.");
    }
}
