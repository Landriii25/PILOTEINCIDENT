<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller; // Ensure correct Controller import
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Affiche la page profil.
     */
    public function edit()
    {
        return view('profile.edit'); // ou la vue que tu utilises (adminlte/breeze)
    }

    /**
     * Met à jour le profil (nom, titre, email, mot de passe optionnel).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'title'    => ['nullable', 'string', 'max:255'],
            'email'    => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Mise à jour des infos
        $user->name  = $validated['name'];
        $user->title = $validated['title'] ?? null;
        $user->email = $validated['email'];

        // Si le mot de passe est fourni, on le met à jour
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Si l'email change, on peut invalider la vérification (si tu utilises MustVerifyEmail)
        if ($user->isDirty('email') && in_array(\Illuminate\Contracts\Auth\MustVerifyEmail::class, class_implements($user))) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * (Optionnel) Supprimer le compte utilisateur.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        auth()->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Compte supprimé.');
    }
}
