<?php

namespace App\Helpers\Data\Report;

class ReportDataLine
{
    protected array $lines;
    
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
        $this->lines = array();
    }

    /**
     * @var ReportLine[]
     * 
     * @return  self
     */
    public function addLine(array $lines)
    {
        array_push($this->lines, $lines);

        return $this;
    }

    public function getLines()
    {
        return $this->lines;
    }
}
