<?php

namespace yi\pagination;

class Paginator extends \Illuminate\Pagination\Paginator
{
    public function toArray()
    {
        $data = parent::toArray();
        $data['has_more'] = $this->hasMore;
        return $data;
    }
}