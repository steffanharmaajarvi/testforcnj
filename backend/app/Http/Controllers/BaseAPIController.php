<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;

class BaseAPIController extends Controller
{

    public function success(array $body = []): JsonResponse
    {

        return $this->response(200, $body);

    }

    public function failure(array $body = []): JsonResponse
    {

        return $this->response(400, $body);

    }

    private function response(int $status, array $body): JSONResponse
    {

        return response()
            ->json($body, $status);
    }

}
