<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\City;
use App\Entity\Warehouse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * City controller.
 *
 * @Route("/")
 */
class CityController extends Controller
{

    /**
     * List Cities
     *
     * @FOSRest\Get("/cities")
     */
    public function listCitiesAction(): View
    {
        $repo = $this->getDoctrine()->getRepository(City::class);
        $cities = $repo->findBy([], ['name' => 'ASC']);

        $dataResponse = [
            'cities' => array_map(function (City $city) {
                return [
                    'id' => $city->getId(),
                    'name' => $city->getName(),
                ];
            }, $cities),
        ];

        return View::create($dataResponse, Response::HTTP_OK, []);
    }

    /**
     * Get City
     *
     * @param string $id
     * @return View
     *
     * @FOSRest\Get("/cities/{id}")
     */
    public function getCityAction(string $id): View
    {
        $repo = $this->getDoctrine()->getRepository(City::class);
        $city = $repo->find($id);

        if (!$city) {
            $errorResponse = [
                'error' => [
                    'code' => 'APP-CITY_NF',
                    'message' => 'City not found',
                ],
            ];

            return View::create($errorResponse, Response::HTTP_NOT_FOUND);
        }

        return View::create($city, Response::HTTP_OK);
    }

    /**
     * @Route("/test")
     */
    public function testAction()
    {
        $this->unloadData();
        $this->loadFakeData();
        die();
        $api = $this->get('api.novaposhta');
        $data = $api->getCities();
        return $this->json($data);
    }

    private function loadFakeData()
    {
        $city = new City('23123w12','Odessa');
        $city2 = new City('23321w12312','Kyiv');
        $city3 = new City('a4502be8-577e-11de-b9fc-0021918b679a', 'London');

        $warehouse = new Warehouse('342w34124', 'Otdelenie', $city3);
        $warehouse2 = new Warehouse('342w3234124', 'Otdelenie 2', $city3);
        $warehouse3 = new Warehouse('6f567f34-6fe6-11e4-acce-0050568002cf', 'Отделение', $city3);

        $em = $this->getDoctrine()->getManager();
        //$em->persist($city);
        //$em->persist($city2);
        $em->persist($city3);
        //$em->persist($warehouse);
        //$em->persist($warehouse2);
        $em->persist($warehouse3);
        $em->flush();
    }

    private function unloadData()
    {
        $c = $this->getDoctrine()->getRepository(City::class)->findAll();
        $w = $this->getDoctrine()->getRepository(Warehouse::class)->findAll();
        $em = $this->getDoctrine()->getManager();
        foreach ($w as $i) {
            $em->remove($i);
        }
        foreach ($c as $i) {
            $em->remove($i);
        }
        $em->flush();
    }

}
