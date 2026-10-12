<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::latest()->paginate(15);
        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:kiloan,satuan',
            'price' => 'required|numeric|min:0',
        ]);

        Service::create($data);

        return redirect()->route('services.index')->with('success', 'Layanan ditambahkan.');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:kiloan,satuan',
            'price' => 'required|numeric|min:0',
        ]);

        $service->update($data);

        return redirect()->route('services.index')->with('success', 'Layanan diperbarui.');
    }

    public function toggleActive(Service $service)
    {
        $service->update(['is_active' => ! $service->is_active]);
        return back()->with('success', 'Status layanan diperbarui.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Layanan dihapus.');
    }
}