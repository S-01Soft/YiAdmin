<?php

namespace yi\pagination;

class LengthAwarePaginator extends \Illuminate\Pagination\LengthAwarePaginator
{
    public function toArray()
    {
        $data = parent::toArray();
        $data['has_more'] = $data['last_page'] > $data['current_page'];
        return $data;
    }
}