<?php

namespace App\Filament\Resources\Transfers\Pages;

use App\Filament\Resources\Transfers\TransferResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\TransferService;
use Illuminate\Database\Eloquent\Model;

class CreateTransfer extends CreateRecord
{
    protected static string $resource = TransferResource::class;

    /**
     * @param array $data
     * @return Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        $transfer = app(TransferService::class)->createTransfer($data);

        return $transfer;
    }
}
