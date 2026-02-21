<?php

namespace App\Helpers\Data\Report;

class ReportData
{
    /**
     * @var ReportLine[]
     */
    private array $headers, $bodies;
    private array $filter;

    public function __construct()
    {
        //
        $this->headers = array();
        $this->bodies = array();
    }

    public function toArray()
    {
        //
        return [
            'headers' => $this->getHeaders(),
            'bodies' => $this->getBodies(),
            'filters' => $this->getFilter(),
        ];
    }

    /**
     * Get the value of headers
     *
     * @return  ReportLine[]
     */ 
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set the value of headers
     *
     * @param  ReportLine[]  $headers
     *
     * @return  self
     */ 
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get the value of bodies
     *
     * @return  ReportLine[]
     */ 
    public function getBodies()
    {
        return $this->bodies;
    }

    /**
     * Set the value of bodies
     *
     * @param  ReportLine[]  $bodies
     *
     * @return  self
     */ 
    public function setBodies(array $bodies)
    {
        $this->bodies = $bodies;

        return $this;
    }

    /**
     * Get the value of filter
     */ 
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set the value of filter
     *
     * @return  self
     */ 
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }
}
