<?php

namespace FeiMx\Pac;

class PacUser
{
    public $rfc;

    public $type = 'P';

    public $credit = 0;

    public $counter = 0;

    public $status = 'A';

    public $added;

    public function map(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->{$attribute} = $value;
        }

        return $this;
    }
}
