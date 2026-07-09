<?php

namespace App\Modules\Notifications\Services;

use App\Models\LaunchProposal;

class FormalEmailComposer
{
    public function composeLaunchApproval(LaunchProposal $launch): array
    {
        $launch->loadMissing(['academicEvent.sessions', 'specialty', 'audience', 'modality']);
        $event = $launch->academicEvent;

        return [
            'subject' => 'PROGRAMACION FINAL DE LANZAMIENTOS - '.$launch->audience->name.' - '.now()->format('m/Y').' - V.01',
            'body' => trim("
Estimados equipos,

Se formaliza la programacion del lanzamiento {$launch->commercial_name}.

Especialidad: {$launch->specialty->name}
Publico objetivo: {$launch->audience->name}
Modalidad: {$launch->modality->name}
Inicio: {$event?->start_date?->format('d/m/Y')}
Estado: {$launch->status}

Por favor continuar con coordinacion academica, piezas, copy, landing y pauta segun corresponda.
            "),
        ];
    }
}
