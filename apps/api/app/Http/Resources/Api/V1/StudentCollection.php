<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

/** @extends \Illuminate\Http\Resources\Json\ResourceCollection<\App\Models\Student> */
class StudentCollection extends ResourceCollection
{
    public $collects = StudentResource::class;
}
