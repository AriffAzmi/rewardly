<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller 
{
    // User management methods
    public function index()
    {
        // List all users
        $users = User::paginate(10);
        return view('pages.users.index', compact('users'));
    }

    public function show($id)
    {
        // Show user details
        $user = User::findOrFail($id);
        return view('pages.users.show', compact('user'));
    }

    public function create()
    {
        // Show user creation form
        return view('pages.users.create');
    }

    public function store(Request $request)
    {
        // Handle user creation
        $user = User::create($request->all());
        return redirect()->route('users.show', $user);
    }

    public function edit($id)
    {
        // Show user edit form
        $user = User::findOrFail($id);
        return view('pages.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        // Handle user update
        $user = User::findOrFail($id);
        $user->update($request->all());
        return redirect()->route('users.show', $user);
    }

    public function destroy($id)
    {
        // Handle user deletion
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index');
    }
}