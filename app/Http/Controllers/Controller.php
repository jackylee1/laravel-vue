<?php

namespace App\Http\Controllers;

use App\Http\Response\ErrorCode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success($data = null, array $headers = [])
    {
        return response()->json($data)->withHeaders($headers);
    }

    public function error(int $code, string $message = '')
    {
        $data = [
            'error'   => true,
            'code'    => $code,
            'message' => $message ?: ErrorCode::getText($code)
        ];

        return response()->json($data);
    }
}
