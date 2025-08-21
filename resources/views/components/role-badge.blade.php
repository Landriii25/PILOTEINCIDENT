@php
    use Illuminate\Support\Str;

    /**
     * Props possibles :
     * - role : string|null (ex: "admin")
     * - user : App\Models\User|null (lira getRoleNames()->first())
     *
     * Exemples d'appel :
     *   <x-role-badge :user="auth()->user()" />
     *   <x-role-badge role="admin" />
     *   <x-role-badge :role="$user->getRoleNames()->first()" />
     */

    // 1) Déterminer le nom du rôle
    $name = null;

    if (isset($role) && $role !== '') {
        // role passé explicitement
        $name = (string) $role;
    } elseif (isset($user) && method_exists($user, 'getRoleNames')) {
        // lire le 1er rôle Spatie du user
        $name = optional($user->getRoleNames())->first();
    }

    $key = $name ? Str::lower($name) : '';

    // 2) Palette (couleur + libellé à afficher)
    $palette = [
        'admin'        => ['label' => 'Administrateur', 'bg' => 'danger',   'text' => 'white'],
        'technicien'   => ['label' => 'Technicien',     'bg' => 'info',     'text' => 'white'],
        'superviseur'  => ['label' => 'Superviseur',    'bg' => 'warning',  'text' => 'dark'],
        'utilisateur'  => ['label' => 'Utilisateur',    'bg' => 'secondary','text' => 'white'],
        // défaut si inconnu / non défini
        '*'            => ['label' => '—',              'bg' => 'light',    'text' => 'dark'],
    ];

    $cfg = $palette[$key] ?? $palette['*'];
@endphp

<span {{ $attributes->merge([
        'class' => "badge bg-{$cfg['bg']} text-{$cfg['text']} align-middle",
        'title' => $name ? Str::title($name) : 'Aucun rôle',
    ]) }}>
    {{ $cfg['label'] }}
</span>
