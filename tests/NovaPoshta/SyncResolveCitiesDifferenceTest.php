<?php

namespace App\Tests\NovaPoshta;


use App\Messages\Console\NovaPoshta\SyncConsole;
use App\Messages\Logger\NovaPoshta\SyncLog;
use App\Services\Api\NovaPoshta;
use App\Services\Sync;
use App\Tests\NovaPoshta\DataProviders\Cities;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class SyncResolveCitiesDifferenceTest extends TestCase
{
    public function getMock($className)
    {
        $mock = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        return $mock;
    }

    public function setUp()
    {
        $managerMock = $this->getMock(EntityManager::class);
        $novaPoshtaMock = $this->getMock(NovaPoshta::class);
        $syncLogMock = $this->getMock(SyncLog::class);
        $syncConsoleMock = $this->getMock(SyncConsole::class);

        return new Sync($managerMock, $novaPoshtaMock, $syncLogMock, $syncConsoleMock);
    }

    public function dataResolveCitiesDifferenceBase()
    {
        return [
            [
                [ 'foo' => 'bar', 'foo1' => 'bar1', 'foo2' => 'bar2', 'foo3' => 'bar3' ],
                [ 'foo' => 'bar1', 'foo1' => 'bar1', 'foo2' => 'bar2', 'foo4' => 'bar4' ],
                [
                    'add' => ['foo3' => 'bar3'],
                    'update' => ['foo' => 'bar'],
                    'remove' => ['foo4' => 'bar4']
                ]
            ],
            [
                [ 'foo' => 'bar1', 'foo1' => 'bar1', 'foo2' => 'bar2', 'foo3' => 'bar3' ],
                [ 'foo' => 'bar'],
                [
                    'add' => ['foo1' => 'bar1', 'foo2' => 'bar2', 'foo3' => 'bar3'],
                    'update' => ['foo' => 'bar1'],
                    'remove' => []
                ]
            ],
            [
                [ 'foo' => 'bar', 'foo3' => 'bar3' ],
                [ 'foo' => 'bar', 'foo1' => 'bar1', 'foo2' => 'bar2'],
                [
                    'add' => ['foo3' => 'bar3'],
                    'update' => [],
                    'remove' => ['foo1' => 'bar1', 'foo2' => 'bar2']
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataResolveCitiesDifferenceBase
     */
    public function testResolveCitiesDifferenceBase($api, $db, $expected)
    {
        $sync = $this->setUp();
        $answerArray = $sync->resolveCitiesDifference($api, $db);

        $this->assertEquals($expected, $answerArray);
    }

    public function dataResolveCitiesDifferenceReal()
    {
        return [
            [
                Cities::getCitiesApi(),
                Cities::getCitiesDBcase1(),
                Cities::getExpectedCase1()
            ],
            [
                Cities::getCitiesApi(),
                Cities::getCitiesDBcase2(),
                Cities::getExpectedCase2()
            ]
        ];
    }

    /**
     * @dataProvider dataResolveCitiesDifferenceReal
     */
    public function testResolveCitiesDifferenceReal($api, $db, $expected)
    {
        $sync = $this->setUp();
        $transformedApi = $sync->transformApiCities($api);
        $transformedDB = $db;

        $answerArray = $sync->resolveCitiesDifference($transformedApi, $transformedDB);

        $this->assertEquals($expected, $answerArray);
    }

}
