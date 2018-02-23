<?php
namespace FeiMx\Pac\Contracts;

interface Factory {
    public function driver($driver = null);
}