<?php

namespace App\Helpers\Queries;

use App\Helpers\Constants\PageConstants;
use App\Helpers\Data\Report\ReportData;
use App\Helpers\Data\Report\ReportDataLine;
use App\Helpers\Data\Report\ReportLine;

class ReportBaseQuery
{
    protected array $params;
    protected array $location_ids;
    protected string $startAt, $endAt;
    protected array $locations, $excludeLocations;
    protected bool $selectAllLocation;

    protected int $page, $limit, $offset;

    /**
     * Create a new class instance.
     */
    public function __construct(array $params)
    {
        //
        $this->params = $params;
        
        $this->startAt = $this->params['start_at'];
        $this->endAt = $this->params['end_at'];
        $this->selectAllLocation = $this->params['select_all_location'] == 'true';
        if (array_key_exists('locs', $this->params)) {
            $this->locations = $this->params['locs'];
        } else {
            $this->locations = [];
        }

        if (array_key_exists('exclude_locs', $this->params)) {
            $this->excludeLocations = $this->params['exclude_locs'];
        } else {
            $this->excludeLocations = [];
        }

        $this->page = $this->params['page'] ?? 1;
        if ($this->page <= 0) $this->page = 1;

        $this->limit = $this->params['limit'] ?? PageConstants::DefaultLimit;
        if ($this->limit <= 0) $this->limit = PageConstants::DefaultLimit;

        $this->offset = ($this->page - 1) * $this->limit;

        $entity = $params['entity'];
        $this->location_ids = $entity->locations()->pluck('id')->toArray();
    }

    public function filter(): array
    {
        // return (new ReportData())
        //     ->setHeaders($this->generateHeader())
        //     ->setBodies($this->generateBody())
        //     ->setFilter($this->generateFilter())
        //     ->toArray();
        return $this->generateBody();
    }

    public function generatePaging(): array
    {
        $total = $this->buildTotal();
        $last_page = ceil($total / $this->limit);

        return [
            "current_page" => $this->page,
            "limit" => $this->limit,
            "last_page" => $last_page,
            "total" => $total,
            "prev_page_url" => $this->page > 1 ? 'prev' : null,
            "next_page_url" => $last_page > $this->page ? 'next': null,
        ];
    }

    /**
     * @return ReportLine[]
     */
    protected function buildHeader(): array
    {
        //
        return [];
    }

    protected function generateHeader(): array
    {
        //
        return array_map(function(ReportLine $line): array { return $line->toArray(); }, $this->buildHeader());
    }

    protected function buildBody(): ReportDataLine
    {
        //
        return new ReportDataLine();
    }

    protected function generateBody(): array
    {
        //
        $bodies = array();
        foreach ($this->buildBody()->getLines() as $reportLines)
        {
            array_push($bodies, array_map(function(ReportLine $line): array { return $line->toArray(); }, $reportLines));
        }
        
        return $bodies;
    }

    protected function generateFilter(): array
    {
        //
        return [
            __('report.start_at') => $this->startAt,
        ];
    }

    protected function buildTotal(): int
    {
        return 0;
    }

    protected function queryLimitOffset($query) {
        return $query->limit($this->limit)->offset($this->offset);
    }
}
