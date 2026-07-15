<?php

namespace App\Modules\Notifications\Services;

use App\Models\AcademicEvent;
use App\Models\LaunchProposal;

class FormalEmailComposer
{
    public function composeLaunchApproval(LaunchProposal $launch, ?AcademicEvent $event = null): array
    {
        $event ??= $launch->academicEvent;
        $audienceName = $launch->getAttribute('audience_name') ?? $launch->audience?->name;
        $specialtyName = $launch->getAttribute('specialty_name') ?? $launch->specialty?->name;
        $modalityName = $launch->getAttribute('modality_name') ?? $launch->modality?->name;

        return [
            'subject' => 'PROGRAMACION FINAL DE LANZAMIENTOS - '.$audienceName.' - '.now()->format('m/Y').' - V.01',
            'body' => trim("
Estimados equipos,

Se formaliza la programacion del lanzamiento {$launch->commercial_name}.

Especialidad: {$specialtyName}
Publico objetivo: {$audienceName}
Modalidad: {$modalityName}
Inicio: {$event?->start_date?->format('d/m/Y')}
Estado: {$launch->status}

Por favor continuar con coordinacion academica, piezas, copy, landing y pauta segun corresponda.
            "),
        ];
    }
}
