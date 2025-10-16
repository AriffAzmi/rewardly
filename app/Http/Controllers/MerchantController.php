<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Voucher;
use App\Models\User;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $merchants = Merchant::with('user')->paginate(10);
        return view('pages.merchants.index', compact('merchants'));
    }

    /**
     * Display a listing of vouchers.
     */
    public function showVouchers()
    {
        $vouchers = auth()->user()->merchants()->with('vouchers')->get();
        return view('pages.merchants.vouchers.index', compact('vouchers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('pages.merchants.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'company_name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);
        $merchant = new Merchant();
        $merchant->company_name = $request->input('company_name');
        $merchant->user_id = $request->input('user_id');
        $merchant->save();

        return redirect()->route('merchants.index')->with('success', 'Merchant created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $merchant = Merchant::findOrFail($id);
        return view('pages.merchants.show', compact('merchant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $merchant = Merchant::findOrFail($id);
        return view('pages.merchants.edit', compact('merchant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request
        $request->validate([
            'company_name' => 'required|string|max:255',
        ]);

        $merchant = Merchant::findOrFail($id);
        $merchant->update($request->all());
        return redirect()->route('merchants.index')->with('success', 'Merchant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
