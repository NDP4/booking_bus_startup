<?php

namespace App\Filament\Resources\CrewAssignmentResource\Pages;

use App\Filament\Resources\CrewAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrewAssignments extends ListRecords
{
    protected static string $resource = CrewAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
