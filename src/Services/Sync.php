<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Api\NovaPoshta;
use App\Entity\City;
use App\Entity\Warehouse;
use App\Messages\Console\NovaPoshta\SyncConsole;
use App\Messages\Logger\NovaPoshta\SyncLog;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

class Sync
{
    /** @var EntityManagerInterface $em */
    private $em;

    /** @var NovaPoshta $api */
    private $api;

    /** @var SyncLog $log */
    private $log;

    /** @var float $percent */
    private $percent = 0.1;

    /** @var $citiesApi array */
    private $citiesApi;

    /** @var $warehousesApi array */
    private $warehousesApi;

    /** @var $citiesDB array */
    private $citiesDB;

    /** @var $warehousesDB array */
    private $warehousesDB;

    /** @var $console SyncConsole */
    private $console;

    public function __construct(EntityManagerInterface $em, NovaPoshta $api, SyncLog $logger, SyncConsole $console)
    {
        $this->em = $em;
        $this->api = $api;
        $this->log = $logger;
        $this->console = $console;
    }

    public function run(OutputInterface $output): void
    {
        $console = $this->console->init($output);
        $log = $this->log;

        $console->syncLoadData();
        $this->getCities();
        $this->getWarehouses();

        $console->syncStart();
        $log->syncStart();

        $console->syncCitiesStart();
        $log->syncCitiesStart();
        try {
            $this->synchronizeCities();

            $console->synCitiesComplete();
            $log->syncCitiesEnd();
        } catch (\InvalidArgumentException $e) {
            $console->syncFailed();
            $log->syncFailed();
            return;
        } catch (\Exception $e) {
            $log->syncCitiesFailed();
            $log->syncException($e);
            return;
        }

        $console->syncWarehousesStart();
        $log->syncWarehousesStart();
        try {
            $this->synchronizeWarehouses();

            $console->syncWarehousesComplete();
            $log->syncWarehousesEnd();
        } catch (\InvalidArgumentException $e) {
            $console->syncFailed();
            $log->syncFailed();
            return;
        } catch (\Exception $e) {
            $log->syncWarehousesFailed();
            $log->syncException($e);
            return;
        }

        $console->syncComplete();
        $log->syncEnd();
    }

    /**
     * @return array
     */
    private function getCities(): array
    {
        $cities = $this->api->getCities();
        $this->citiesApi = $cities;

        return $cities;
    }

    /**
     * @param string $ref
     * @return array
     */
    private function getWarehouses(string $ref = null): array
    {
        $warehouses = $this->api->getWarehouses($ref);
        $this->warehousesApi = $warehouses;

        return $warehouses;
    }

    private function checkDifferenceCities(int $cntRemove): bool
    {
        $this->console->syncCheckDifferenceCities();
        $cntApi = array_key_exists('info', $this->citiesApi) ? $this->citiesApi['info']['totalCount'] : 0;
        $cntDB = $this->em->getRepository(City::class)->countCities();

        $this->log->syncInit('cities', $cntDB, $cntApi);

        return $this->differenceLogic($cntRemove, $cntDB, 'cities');
    }

    private function checkDifferenceWarehouses(int $cntRemove): bool
    {
        $this->console->syncCheckDifferenceWarehouses();
        $cntApi = array_key_exists('info', $this->warehousesApi) ? $this->warehousesApi['info']['totalCount'] : 0;
        $cntDB = $this->em->getRepository(Warehouse::class)->countWarehouses();

        $this->log->syncInit('warehouses', $cntDB, $cntApi);

        return $this->differenceLogic($cntRemove, $cntDB, 'warehouses');
    }

    private function differenceLogic(int $cntRemove, int $cntDB, string $type): bool
    {
        if ($cntRemove > $this->percent * $cntDB) {
            $this->log->syncFailedViaDifference($type, $cntRemove, $cntDB);

            throw new InvalidArgumentException(
                'Sync fails via count ' . $type . ' for remove. Request for remove ' . $cntRemove .' rows of ' .$cntDB
            );
        }

        return true;
    }

    private function synchronizeCities(): bool
    {
        $this->setCitiesDB();
        $citiesApi = $this->transformApiCities($this->citiesApi);
        $citiesDB = $this->transformDBCities();

        $complexArray = $this->resolveCitiesDifference($citiesApi, $citiesDB);
        $add = $complexArray['add'];
        $update = $complexArray['update'];
        $remove = $complexArray['remove'];
        $this->checkDifferenceCities(count($remove));

        $this->synchronizeCitiesDB($add, $update, $remove);

        $this->em->flush();
        $this->log->unLoadLogStack();

        return true;
    }

    private function synchronizeWarehouses(): bool
    {
        $this->setCitiesDB();
        $this->setWarehousesDB();
        $warehousesApi = $this->transformApiWarehouses();
        $warehousesDB = $this->transformDBWarehouses();

        $complexArray = $this->resolveWarehousesDifference($warehousesApi, $warehousesDB);
        $add = $complexArray['add'];
        $update = $complexArray['update'];
        $remove = $complexArray['remove'];
        $this->checkDifferenceWarehouses(count($remove));

        $this->synchronizeWarehousesDB($add, $update, $remove);

        $this->em->flush();
        $this->log->unLoadLogStack();

        return true;
    }

    public function transformApiCities($apiAnswer): array
    {
        $transformed = [];

        if(!is_array($apiAnswer) or !array_key_exists('data', $apiAnswer) or !is_array($apiAnswer['data'])) {
            throw new \InvalidArgumentException('Incorrect api answer data key!');
        }

        foreach ($apiAnswer['data'] as $city) {
            if(!array_key_exists('Ref', $city) or !array_key_exists('DescriptionRu', $city)) {
                throw new \InvalidArgumentException('Incorrect api answer data key!');
            }
            $transformed[$city['Ref']] = $city['DescriptionRu'];
        }
        return $transformed;
    }


    private function transformDBCities(): array
    {
        $citiesDB = $this->em->getRepository(City::class)->findAll();
        $transformed = [];

        /** @var City $city */
        foreach ($citiesDB as $city) {
            $transformed[$city->getId()] = $city->getName();
        }

        return $transformed;
    }

    private function transformApiWarehouses(): array
    {
        $transformed = [];
        if (!is_array($this->warehousesApi['data'])) {
            throw new \InvalidArgumentException('No warehouses data from api');
        }

        foreach ($this->warehousesApi['data'] as $warehouse) {
            $transformed[$warehouse['Ref']] = [
                'city_ref' => $warehouse['CityRef'],
                'name' => $warehouse['DescriptionRu'],
            ];
        }
        return $transformed;
    }

    /**
     * @return array
     */
    private function transformDBWarehouses(): array
    {
        $warehousesDB = $this->em->getRepository(Warehouse::class)->findAll();
        $transformed = [];

        /** @var Warehouse $warehouse */
        foreach ($warehousesDB as $warehouse) {
            $transformed[$warehouse->getId()] = [
                'city_ref' => $warehouse->getCity()->getId(),
                'name' => $warehouse->getName(),
            ];
        }

        return $transformed;
    }

    private function setCitiesDB(): void
    {
        $citiesDB = $this->em->getRepository(City::class)->findAll();
        $citiesStatic = [];

        /** @var City $city */
        foreach ($citiesDB as $city) {
            $citiesStatic[$city->getId()] = $city;
        }
        $this->citiesDB = $citiesStatic;
    }

    private function setWarehousesDB(): void
    {
        $warehousesDB = $this->em->getRepository(Warehouse::class)->findAll();
        $warehousesStatic = [];

        /** @var Warehouse $warehouse */
        foreach ($warehousesDB as $warehouse) {
            $warehousesStatic[$warehouse->getId()] = $warehouse;
        }
        $this->warehousesDB = $warehousesStatic;
    }

    /**
     * @param array $citiesApi
     * @param array $citiesDB
     * @return array
     */
    public function resolveCitiesDifference(array $citiesApi, array $citiesDB): array
    {
        $add = $update = $remove = [];
        foreach ($citiesApi as $key => $name) {
            if (array_key_exists($key, $citiesDB)) {
                if ($citiesDB[$key] != $name) {
                    $update[$key] = $name;
                }
            } else {
                $add[$key] = $name;
            }
        }

        foreach ($citiesDB as $key => $name) {
            if (!array_key_exists($key, $citiesApi)) {
                $remove[$key] = $name;
            }
        }
        return [
            'add' => $add,
            'update' => $update,
            'remove' => $remove,
        ];
    }

    /**
     * @param array $warehousesApi
     * @param array $warehousesDB
     * @return array
     */
    private function resolveWarehousesDifference(array $warehousesApi, array $warehousesDB): array
    {
        $add = $update = $remove = [];
        foreach ($warehousesApi as $key => $data) {
            if (array_key_exists($key, $warehousesDB)) {
                if ($warehousesDB[$key]['name'] != $data['name']) {
                    $update[$key]['name'] = $data['name'];
                }
                if ($warehousesDB[$key]['city_ref'] != $data['city_ref']) {
                    $update[$key]['city_ref'] = $data['city_ref'];
                }
            } else {
                $add[$key] = [
                    'city_ref' => $data['city_ref'],
                    'name' => $data['name'],
                ];
            }
        }

        foreach ($warehousesDB as $key => $data) {
            if (!array_key_exists($key, $warehousesApi)) {
                $remove[$key] = $data['name'];
            }
        }

        return [
            'add' => $add,
            'update' => $update,
            'remove' => $remove,
        ];
    }

    /**
     * @param array $add
     * @param array $update
     * @param array $remove
     * @return bool
     */
    private function synchronizeCitiesDB(array $add, array $update, array $remove): bool
    {
        $em = $this->em;
        $log = $this->log;

        foreach ($add as $ref => $name) {
            $em->persist(new City($ref, $name));
            $this->log->addLogToStack('info', $log->messageAddCity($ref));
        }

        foreach ($update as $ref => $name) {
            /** @var City $city */
            $city = $this->citiesDB[$ref];
            $this->log->addLogToStack('info', $log->messageUpdateCity($ref, $city->getName(), $name));
            $city->setName($name);
            $em->persist($city);

        }

        foreach ($remove as $ref => $name) {
            /** @var City $city */
            $city = $this->citiesDB[$ref];
            $em->remove($city);
            $this->log->addLogToStack('info', $log->messageRemoveCity($ref));
        }

        return true;
    }

    /**
     * @param array $add
     * @param array $update
     * @param array $remove
     * @return bool
     */
    private function synchronizeWarehousesDB(array $add, array $update, array $remove): bool
    {
        $em = $this->em;
        $log = $this->log;

        foreach ($add as $ref => $data) {
            /** @var City $city */
            $city = $this->citiesDB[$data['city_ref']];
            $em->persist(new Warehouse($ref, $data['name'], $city));
            $this->log->addLogToStack('info', $log->messageAddWarehouse($ref));
        }

        foreach ($update as $ref => $data) {
            /** @var Warehouse $warehouse */
            $warehouse = $this->warehousesDB[$ref];

            if (array_key_exists('name', $data)) {
                $this->log->addLogToStack(
                    'info',
                    $log->messageUpdateWarehouseName($ref, $warehouse->getName(), $data['name'])
                );
                $warehouse->setName($data['name']);

            }

            if (array_key_exists('city_ref', $data)) {
                /** @var City $city */
                $city = $this->citiesDB[$data['city_ref']];
                $this->log->addLogToStack(
                    'info',
                    $log->messageUpdateWarehouseCity($ref, $warehouse->getCity()->getId(), $data['city_ref'])
                );
                $warehouse->setCity($city);
            }

            $em->persist($warehouse);
        }

        foreach ($remove as $ref => $name) {
            /** @var Warehouse $warehouse */
            $warehouse = $this->warehousesDB[$ref];
            $em->remove($warehouse);
            $this->log->addLogToStack('info', $log->messageRemoveWarehouse($ref));
        }

        return true;
    }
}
