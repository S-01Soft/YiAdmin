<?php

namespace yi;

use JasonGrimes\Paginator as BasePaginator;

class Paginator extends BasePaginator 
{
    public function toHtml()
    {
        if (!$this->simple) return parent::toHtml();

        $hasPrePage = $this->currentPage > 1;
        $currentPage = $this->currentPage;
        $hasNextPage = $this->hasMore;
        if (!$hasPrePage && !$hasNextPage) return '';
        $html = '<ul class="pagination">';
        if ($hasPrePage) {
            $html .= '<li><a href="' . htmlspecialchars($this->getPageUrl($currentPage - 1)) . '">&laquo; '. $this->previousText .'</a></li>';
        }
        
        if ($hasNextPage) {
            $html .= '<li><a href="' . htmlspecialchars($this->getPageUrl($currentPage + 1)) . '">'. $this->nextText .' &raquo;</a></li>';
        }
        return $html;
    }

    public function setSimple($simple)
    {
        $this->simple = $simple;
        return $this;
    }

    public function setHasMorePages($hasMore)
    {
        $this->hasMore = $hasMore;
        return $this;
    }
}