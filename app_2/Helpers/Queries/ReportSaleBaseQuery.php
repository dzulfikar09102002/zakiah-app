<?php

namespace App\Helpers\Queries;

class ReportSaleBaseQuery extends ReportBaseQuery
{
    protected string $discounted;
    protected array $statuses;

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (array_key_exists('discounted', $this->params)) {
            $this->discounted = $this->params['discounted'];
        } else {
            $this->discounted = 'all';
        }

        if (array_key_exists('statuses', $this->params)) {
            $this->statuses = $this->params['statuses'];
        } else {
            $this->statuses = array('ok');
        }
    }

    protected function buildWhereLocation($query) {
        $query->whereIn('location_id', $this->location_ids);
        
        if ($this->selectAllLocation && count($this->excludeLocations) > 0) {
            $query->whereNotIn('location_id', $this->excludeLocations);
        } else if ($this->selectAllLocation == false) {
            $query->whereIn('location_id', $this->locations);
        }
    }

    protected function buildWhereSalesTime($query)
    {
        $query
            ->where('created_at', '>=', $this->startAt)
            ->where('created_at', '<=', $this->endAt);
    }

    protected function buildWhereStatus($query)
    {
        $query
            ->whereIn('status', $this->statuses);
    }
}
