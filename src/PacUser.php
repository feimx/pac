<?php

namespace FeiMx\Pac;

class PacUser
{
    public $rfc;

    public $type = 'O';

    public $credit;

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
