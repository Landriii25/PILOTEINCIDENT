<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit(Request $request)
    {
        return view('profile.edit',['user'=>$request->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'email'=>['required','email'],
        ]);

        $request->user()->update($data);

        return back()->with('success','Profil mis à jour.');
    }

    public function destroy(Request $request)
    {
        $request->user()->delete();
        return redirect('/')->with('success','Compte supprimé.');
    }
}
