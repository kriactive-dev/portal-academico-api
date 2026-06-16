<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

/** @extends \Illuminate\Http\Resources\Json\ResourceCollection<\App\Models\RequestEntry> */
class RequestCollection extends ResourceCollection
{
    public $collects = RequestResource::class;
}
