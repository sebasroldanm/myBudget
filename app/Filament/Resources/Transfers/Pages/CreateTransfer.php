<?php

namespace App\Filament\Resources\Transfers\Pages;

use App\Filament\Resources\Transfers\TransferResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\TransferService;
use Filament\Notifications\Notification;
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
        try {
            return app(TransferService::class)->createTransfer($data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->title('Error en la transferencia')
                ->body($e->getMessage())
                ->danger()
                ->send();
            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error en la transferencia')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
