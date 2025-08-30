<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:settings.manage']);
    }

    /**
     * Affiche la page de paramètres avec les valeurs actuelles.
     */
    public function index()
    {
        // Valeurs actuelles (lecture via config/env)
        $data = [
            'app_name'           => config('app.name'),
            'app_locale'         => config('app.locale'),
            // NB: dans ton config/app.php, timezone est figé à 'UTC'.
            // Si tu veux le rendre éditable, change la config pour lire env('APP_TIMEZONE', 'UTC')
            'app_timezone'       => env('APP_TIMEZONE', 'UTC'),
            'allow_registration' => (bool) env('ALLOW_REGISTRATION', false),
            'company_name'       => env('COMPANY_NAME', ''),
            'company_logo_url'   => env('COMPANY_LOGO_URL', ''),
        ];

        return view('settings.index', $data);
    }

    /**
     * Reçoit le POST du formulaire et met à jour le .env (persistant).
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name'           => ['required', 'string', 'max:255'],
            'app_locale'         => ['required', 'in:fr,en'], // adapte si tu as plus de langues
            'app_timezone'       => ['required', 'string', 'max:64'],
            'allow_registration' => ['nullable', 'boolean'],
            'company_name'       => ['nullable', 'string', 'max:255'],
            'company_logo_url'   => ['nullable', 'string', 'max:2048'],
        ]);

        // Normaliser les booléens issus des checkbox
        $validated['allow_registration'] = (bool) ($validated['allow_registration'] ?? false);

        // Paires ENV à écrire
        $envPairs = [
            'APP_NAME'           => $validated['app_name'],
            'APP_LOCALE'         => $validated['app_locale'],
            'APP_TIMEZONE'       => $validated['app_timezone'],   // utile si tu adaptes config/app.php
            'ALLOW_REGISTRATION' => $validated['allow_registration'] ? 'true' : 'false',
            'COMPANY_NAME'       => $validated['company_name'] ?? '',
            'COMPANY_LOGO_URL'   => $validated['company_logo_url'] ?? '',
        ];

        $this->writeEnv($envPairs);

        // Rafraîchir la config en mémoire (facultatif en dev)
        // config([...]) ne persiste pas, mais on peut "forcer" quelques clés si besoin :
        config([
            'app.name'   => $envPairs['APP_NAME'],
            'app.locale' => $envPairs['APP_LOCALE'],
        ]);

        return redirect()->route('settings.index')->with('success', 'Paramètres mis à jour.');
    }

    /**
     * Écrit/Met à jour les clés dans le fichier .env
     * (Simple remplace-ligne, robuste pour la plupart des cas).
     */
    private function writeEnv(array $pairs): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            // Si pas de .env, on en crée un basique
            File::put($envPath, '');
        }

        $env = File::get($envPath);

        foreach ($pairs as $key => $value) {
            // Échapper les valeurs avec espaces/characters spéciaux
            $value = $this->escapeEnvValue($value);

            $pattern = "/^{$key}=.*/m";

            if (preg_match($pattern, $env)) {
                // Remplacer la ligne existante
                $env = preg_replace($pattern, "{$key}={$value}", $env);
            } else {
                // Ajouter à la fin
                $env .= PHP_EOL."{$key}={$value}";
            }
        }

        File::put($envPath, $env);
    }

    private function escapeEnvValue($value): string
    {
        // Si déjà entouré de quotes, on garde
        $string = (string) $value;

        // Ajouter des quotes si espaces, #, =, etc.
        if (Str::contains($string, [' ', '#', '='])) {
            // Échapper les quotes existantes
            $string = str_replace('"', '\"', $string);
            return "\"{$string}\"";
        }

        return $string;
    }
}
