<?php

namespace App\Helpers\Response;

use Illuminate\Http\Response;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;

trait ResponseHelpers
{
    /**
     * Success status of the response.
     *
     * @var bool
     */
    protected $success = false;

    /**
     * Respond with http ok.
     * Status code = 200
     *
     * @param mixed|null $data
     * @param bool $success
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondOk($data = null, $success = true)
    {
        $this->success = $success;

        return $this->respond($data);
    }

    /**
     * Respond with created.
     * Status code = 201
     *
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondCreated($data = null)
    {
        $this->success = true;

        return $this->respond($data, Response::HTTP_CREATED);
    }

    /**
     * Respond with no content.
     * Status code = 204
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondNoContent()
    {
        $this->success = true;

        return $this->respond(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Respond with bad request.
     * Status code = 400
     *
     * @param string|null $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondBadRequest($message = 'Bad Request')
    {
        return $this->respond(null, Response::HTTP_BAD_REQUEST, $message);
    }

    /**
     * Respond with unauthorized.
     * Status code = 401
     *
     * @param string|null $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondUnauthorized($message = 'Unauthorized')
    {
        return $this->respond(null, Response::HTTP_UNAUTHORIZED, $message);
    }

    /**
     * Respond with forbidden.
     * Status code = 403
     *
     * @param string|null $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondForbidden($message = 'Forbidden')
    {
        return $this->respond(null, Response::HTTP_FORBIDDEN, $message);
    }

    /**
     * Respond with not found.
     * Status code = 404
     *
     * @param string|null $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondNotFound($message = 'Not Found')
    {
        return $this->respond(null, Response::HTTP_NOT_FOUND, $message);
    }

    /**
     * Respond with method not allowed.
     * Status code = 405
     *
     * @param string|null $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondMethodNotAllowed($message = 'Method Not Allowed')
    {
        return $this->respond(null, Response::HTTP_METHOD_NOT_ALLOWED, $message);
    }

    /**
     * Return a JSON response with the custom data format.
     *
     * @param mixed $data
     * @param int $status
     * @param string|null $message
     * @param array $headers
     * @param int $options
     * @return \Illuminate\Http\JsonResponse|JsonResource
     */
    protected function respond($data, $status = Response::HTTP_OK, $message = null, array $headers = [], $options = 0)
    {
        if ($data instanceof JsonResource) {
            $additionalData = $this->getAdditionalData($message);

            return $data->additional($additionalData);
        }

        return $this->jsonResponse(
            $this->formatResponseData($data, $message),
            $status,
            $headers,
            $options
        );
    }

    /**
     * Return a JSON response.
     *
     * @param string|array $data
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse($data = [], $status = Response::HTTP_OK, array $headers = [], $options = 0)
    {
        return response()->json($data, $status, $headers, $options);
    }

    /**
     * Merge the various data into the standard response format.
     *
     * @param mixed $data
     * @param string|null $message
     * @return array
     */
    private function formatResponseData($data, $message = null)
    {
        /*
         * If the data object is not an array and has the "data" key, then create
         * an array with the data wrapped with the "data" key.
         */
        if (! (is_array($data) && array_key_exists('data', $data))) {
            if ($data instanceof Arrayable) {
                $data = $data->toArray();
            }

            $data = compact('data');
        }

        /*
         * Prevent null data keys from displaying in the response.
         * Still allows empty array for no result responses.
         */
        if (data_get($data, 'data') === null) {
            unset($data['data']);
        }

        $additionalData = $this->getAdditionalData($message);

        return array_merge($additionalData, $data);
    }

    /**
     * Get the additional data.
     *
     * @param string|null $message
     * @return array
     */
    private function getAdditionalData($message = null)
    {
        $success = $this->success;

        return $message === null
            ? compact('success')
            : compact('success', 'message');
    }
}
