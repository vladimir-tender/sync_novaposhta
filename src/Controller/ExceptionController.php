<?php

declare(strict_types = 1);

namespace App\Controller;

use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionController extends Controller
{

    public function showAction(\Exception $e = null): View
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
        }

        if (is_null($e)) {
            $e = new \Exception('Not Found', Response::HTTP_NOT_FOUND);
        }

        $errorResponse = [
            'error' => [
                'message' => $e->getMessage(),
                'stack'   => $e->getTrace(),
            ],
        ];

        return View::create($errorResponse, $statusCode);
    }
}
