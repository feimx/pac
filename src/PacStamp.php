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

    public function toArray()
    {
        return [
            'xml' => $this->xml,
            'uuid' => $this->uuid,
            'date' => $this->date,
            'status_code' => $this->statusCode,
            'sat_seal' => $this->satSeal,
            'sat_certificate_number' => $this->satCertificateNumber,
        ];
    }
}
