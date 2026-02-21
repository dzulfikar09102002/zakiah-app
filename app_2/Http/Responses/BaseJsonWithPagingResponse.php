<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Response;

class BaseJsonWithPagingResponse extends BaseJsonResponse
{
    protected array $paging;

    protected function build() {
        return array_merge(parent::build(), $this->getPaging());
    }

    /**
     * Get the value of paging
     */ 
    public function getPaging()
    {
        return $this->paging;
    }

    /**
     * Set the value of paging
     *
     * @return  self
     */ 
    public function setPaging($paging)
    {
        $this->paging = $paging;

        return $this;
    }
}
