<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DataJsonResponse extends JsonResource
{
    /**
     * The custom resource instance.
     *
     * @var mixed
     */
    public $customResource;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  mixed  $customResource
     * @return void
     */
    public function __construct($resource, $customResource)
    {
        parent::__construct($resource);
        $this->customResource = $customResource;
    }


    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $x = $this->customResource;
        if ($request->get('is_light', false)) {
            return [
                'data' => $this->collection->transform(function ($item) use ($x) {
                    return new $x($item);
                }),
                'status' => true,
                'status_code' => config('constants.validation_codes.ok'),
                'message' => config('constants.messages.success.listed')
            ];
        }
        return [
            'current_page' => (string) $this->currentPage(),
            'total' => (string) $this->total(),
            'data' => $this->collection->transform(function ($item) use ($x) {
                return new $x($item);
            }),
            'first_page_url' => url($request->path()) . '?page=1',
            'from' => (string) $this->firstItem(),
            'last_page' => (string) $this->lastPage(),
            'last_page_url' => url($request->path()) . '?page=' . $this->lastPage(),
            'next_page_url' => (string) $this->nextPageUrl(),
            'path' => url($request->path()),
            'per_page' => (string) $this->perPage(),
            'prev_page_url' => (string) $this->previousPageUrl(),
            'to' => (string) $this->lastItem(),
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.listed')
        ];
    }
}
