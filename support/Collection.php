<?php

namespace support;

class Collection extends \Illuminate\Support\Collection 
{
    public function order(string $field, string $order = 'asc')
    {
        return $this->sort(function ($a, $b) use ($field, $order) {
            $fieldA = $a[$field] ?? null;
            $fieldB = $b[$field] ?? null;
            return 'desc' == strtolower($order) ? intval($fieldB > $fieldA) : intval($fieldA > $fieldB);
        });
    }
} 