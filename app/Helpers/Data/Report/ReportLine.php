<?php

namespace App\Helpers\Data\Report;

use App\Enums\Report\ReportTextStyleEnum;
use App\Enums\Report\ReportAlignEnum;

class ReportLine
{
    private string $text;
    private ReportAlignEnum $align;
    private ReportTextStyleEnum $textStyle;
    
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
        $this->align = ReportAlignEnum::Left;
        $this->textStyle = ReportTextStyleEnum::Normal;
    }

    /**
     * Get the value of text
     */ 
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set the value of text
     *
     * @return  self
     */ 
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get the value of align
     */ 
    public function getAlign(): ReportAlignEnum
    {
        return $this->align;
    }

    /**
     * Set the value of align
     *
     * @return  self
     */ 
    public function setAlign(ReportAlignEnum $align)
    {
        $this->align = $align;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->getText(),
            'align' => $this->getAlign(),
            'textStyle' => $this->getTextStyle(),
        ];
    }

    /**
     * Get the value of textStyle
     */ 
    public function getTextStyle(): ReportTextStyleEnum
    {
        return $this->textStyle;
    }

    /**
     * Set the value of textStyle
     *
     * @return  self
     */ 
    public function setTextStyle(ReportTextStyleEnum $textStyle)
    {
        $this->textStyle = $textStyle;

        return $this;
    }
}
