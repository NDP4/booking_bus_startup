<?php

namespace App\Filament\Resources\CrewAssignmentResource\Pages;

use App\Filament\Resources\CrewAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCrewAssignment extends EditRecord
{
    protected static string $resource = CrewAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
