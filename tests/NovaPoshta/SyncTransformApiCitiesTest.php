<?php

namespace App\Tests\NovaPoshta;

use App\Messages\Console\NovaPoshta\SyncConsole;
use App\Messages\Logger\NovaPoshta\SyncLog;
use App\Services\Api\NovaPoshta;
use App\Services\Sync;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class SyncTransformApiCitiesTest extends TestCase
{
    public function getMock($className)
    {
        $mock = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        return $mock;
    }

    public function setUp()
    {
        //disable dependencies
        $managerMock = $this->getMock(EntityManager::class);
        $novaPoshtaMock = $this->getMock(NovaPoshta::class);
        $syncLogMock = $this->getMock(SyncLog::class);
        $syncConsoleMock = $this->getMock(SyncConsole::class);

        return new Sync($managerMock, $novaPoshtaMock, $syncLogMock, $syncConsoleMock);
    }

    public function dataPositiveTransformApiCities()
    {
        return [
            [
                [
                    'data' => [
                        [
                            'DescriptionRu' => 'Авдеевка',
                            'Ref' => 'a9522a7e-eaf5-11e7-ba66-005056b2fc3d'
                        ],
                        [
                            'DescriptionRu' => 'Авангард',
                            'Ref' => '8e1718f5-1972-11e5-add9-005056887b8d'
                        ]
                    ]
                ],
                [
                    'a9522a7e-eaf5-11e7-ba66-005056b2fc3d' => 'Авдеевка',
                    '8e1718f5-1972-11e5-add9-005056887b8d' => 'Авангард',
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataPositiveTransformApiCities
     */
    public function testPositiveTransformApiCities($in, $expected)
    {
        $sync = $this->setUp();
        $actualArray = $sync->transformApiCities($in);
        $this->assertEquals($expected, $actualArray);
    }

    public function dataNegativeTransformApiCities()
    {
        return [
            [
                [
                    'data' => [
                        [
                            'DescriptionRu' => 'Авдеевка',
                            'Ref' => 'a9522a7e-eaf5-11e7-ba66-005056b2fc3d'
                        ],
                        [
                            'DescriptionRu' => 'Авангард',
                            'Ref' => '8e1718f5-1972-11e5-add9-005056887b8d'
                        ]
                    ]
                ],
                [
                    'a9522a7e-eaf5-11e7-ba66-005056b2fc3d' => 'Авдеевка',
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataNegativeTransformApiCities
     */
    public function testNegativeTransformApiCities($in, $expected)
    {
        $sync = $this->setUp();
        $actualArray = $sync->transformApiCities($in);
        $this->assertNotEquals($expected, $actualArray);
    }

    public function dataBadApiAnswerData()
    {
        return [
            ['cities' => [],],
            ['data' => ''],
            ['data' => [
                    ['DescriptionUa' => 'Авдеевка', 'Ref' => 'a9522a7e-eaf5-11e7-ba66-005056b2fc3d'],
                    ['DescriptionRu' => 'Авдеевка', 'Id' => 'a9522a7e-eaf5-11e7-ba66-005056b2fc3d']
                ],
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider dataBadApiAnswerData
     */
    public function testBadApiAnswerData($in)
    {
        $sync = $this->setUp();
        $sync->transformApiCities($in);
    }


}
