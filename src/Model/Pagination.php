<?php

namespace App\Model;

use App\Annotation\ModelVariable;
use App\Enum\PaginationMode;

class Pagination extends AbstractModel
{
    /**
     * @ModelVariable()
     */
    public $active = false;

    /**
     * @ModelVariable()
     */
    public $currentPage = 1;

    /**
     * @ModelVariable()
     */
    public $currentLetter = 'A';

    /**
     * @ModelVariable()
     */
    public $mode = PaginationMode::Numbers;

    /**
     * @ModelVariable()
     */
    public $totalPages = 1;

    /**
     * @ModelVariable()
     */
    public $pageShift = 0;

    public function reset() : Pagination
    {
        $this->active = false;
        $this->currentPage = 1;
        $this->currentLetter = 'A';
        $this->mode = PaginationMode::Numbers;
        $this->totalPages = 1;
        $this->pageShift = 0;

        return $this;
    }

    public function disable() : Pagination
    {
        $this->reset();

        return $this;
    }

    /**
     * @param int $currentPage
     * @param int $totalPages
     * @param int $pageShift
     * @return $this
     */
    public function numericPagination(int $currentPage = 1, int $totalPages = 1, int $pageShift = 0) : Pagination
    {
        $this->reset();

        $this->active = true;
        $this->mode = PaginationMode::Numbers;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->pageShift = $pageShift;

        return $this;
    }

    public function letterPagination(string $letter = 'A') : Pagination
    {
        $this->reset();

        $this->active = true;
        $this->mode = PaginationMode::Letters;
        $this->currentLetter = $letter;

        return $this;
    }

    public function loadMorePagination() : Pagination
    {
        $this->reset();

        $this->active = true;
        $this->mode = PaginationMode::LoadMore;

        return $this;
    }
}