<?php

namespace App\Services;

/**
 * Defines the JSONResponse format for the entire Service
 * Class JsonAPIResponse
 * @package App\Services
 */
class JsonAPIResponse {
    /**
     * set the basic response code types
     * @var int
     */
    public static $SUCCESS                  = 200;
    public static $CREATED                  = 201;
    public static $ACCEPTED                 = 202;
    public static $NO_CONTENT               = 204;
    public static $BAD_REQUEST              = 400;
    public static $UNAUTHORIZED             = 401;
    public static $FORBIDDEN                = 403;
    public static $NOT_FOUND                = 404;
    public static $METHOD_NOT_ALLOW         = 405;
    public static $UNPROCESSABLE_ENTITY     = 422;
    public static $INTERNAL_SERVER_ERROR    = 500;
    public static $NOT_IMPLEMENTED          = 501;
    public static $ACCOUNT_NOT_VERIFIED     = 209;

    /**
     * Returns a successful response without a status code
     * @param string $message
     * @param null $data
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function sendSuccessResponse(string $message = "Success", $data = null, int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                'status' => true,
                'message' => $message,
                'data' => $data
            ],
            $status
        );
    }

    /**
     * This returns an error message back to the client without a status code
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function sendErrorResponse(string $message = 'error', $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'message' => $message
            ],
            $status
        );
    }
}
