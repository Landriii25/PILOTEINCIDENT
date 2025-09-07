<?php

namespace App\Exports;

use App\Models\Incident;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Bonus pour des colonnes auto-ajustées

class IncidentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // On récupère les incidents avec leurs relations pour la performance
        return Incident::with(['application', 'technicien', 'user'])->get();
    }

    /**
     * Définit la ligne d'en-tête.
     */
    public function headings(): array
    {
        return [
            'Code',
            'Titre',
            'Statut',
            'Priorité',
            'Application',
            'Technicien Assigné',
            'Créé par',
            'Date de Création',
            'Date de Prise en Charge',
            'Date de Résolution',
        ];
    }

    /**
     * Transforme chaque incident en une ligne du tableau.
     *
     * @param \App\Models\Incident $incident
     */
    public function map($incident): array
    {
        return [
            $incident->code,
            $incident->titre,
            $incident->statut,
            $incident->priorite,
            $incident->application?->nom ?? 'N/A',
            $incident->technicien?->name ?? 'Non assigné',
            $incident->user?->name ?? 'N/A',
            $incident->created_at->format('d/m/Y H:i'),
            $incident->acknowledged_at ? $incident->acknowledged_at->format('d/m/Y H:i') : '',
            $incident->resolved_at ? $incident->resolved_at->format('d/m/Y H:i') : '',
        ];
    }
}
