<?php

namespace FeiMx\Pac;

class PacStamp
{
    public $xml;

    public $uuid;

    public $date;

    public $statusCode;

    public $satSeal;

    public $satCertificateNumber;

    public function map(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->{$attribute} = $value;
        }

        return $this;
    }
}