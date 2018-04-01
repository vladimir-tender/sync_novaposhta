<?php

namespace App\Messages\Logger\NovaPoshta;


use App\Messages\Logger\ALogger;

class SyncLog extends ALogger
{
    
    private function message(string $message)
    {
        $this->logger->info($message);
    }
    
    public function syncFailed(): void
    {
        $this->message('FAILED Sync');
    }

    public function syncFailedViaDifference(string $type, int $cntRemove, int $cntDB): void
    {
        $this->message('LOGICAL EXCEPTION. Request for remove ' . $cntRemove .' of ' . $cntDB . ' ' . $type);
    }

    public function syncStart(): void
    {
        $this->message('START Sync');
    }

    public function syncEnd(): void
    {
        $this->message('END Sync');
    }

    public function syncInit(string $type, int $cntDB, int $cntApi): void
    {
        $this->message('Received '.$cntDB. ' ' . $type . ' from DB');
        $this->message('Received '.$cntApi. ' ' . $type . ' from NP API');
    }

    public function checkingDiff()
    {
        $this->message('DIFFERENCE checking');
    }

    public function syncException(\Exception $e): void
    {
        $this->message($e->getMessage());
    }

    public function syncCitiesStart(): void
    {
        $this->message('START Sync Cities');
    }

    public function syncCitiesEnd(): void
    {
        $this->message('END Sync Cities');
    }

    public function syncCitiesFailed(): void
    {
        $this->message('FAILED Sync Cities');
    }

    public function syncWarehousesStart(): void
    {
        $this->message('START Sync Warehouses');
    }

    public function syncWarehousesEnd(): void
    {
        $this->message('END Sync Warehouses');
    }

    public function syncWarehousesFailed(): void
    {
        $this->message('FAILED Sync Warehouses');
    }

    public function messageAddCity(string $ref): string
    {
        return '[add] City "' . $ref . '" created';
    }

    public function messageUpdateCity(string $ref, string $oldName, string $name): string
    {
        return '[update] City "' . $ref . '" updated. Name changed ' . $oldName . ' => ' .$name;
    }

    public function messageRemoveCity(string $ref): string
    {
        return '[remove] City "' . $ref . '" removed';
    }

    public function messageAddWarehouse(string $ref): string
    {
        return '[add] Warehouse "' . $ref . '" created';
    }

    public function messageUpdateWarehouseName(string $ref, string $oldName, string $name): string
    {
        return '[update] Warehouse "' . $ref . '" updated. Name changed ' . $oldName . ' => ' .$name;
    }

    public function messageUpdateWarehouseCity(string $ref, string $old_city_ref, string $city_ref): string
    {
        return '[update] Warehouse "' . $ref . '" updated. City ref changed ' . $old_city_ref . ' => ' .$city_ref;
    }

    public function messageRemoveWarehouse(string $ref): string
    {
        return '[remove] Warehouse "' . $ref . '" removed';
    }
}
