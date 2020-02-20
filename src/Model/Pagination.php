<?php

namespace App\Model;

use App\Annotation\ModelVariable;
use App\Enum\PaginationMode;

class Pagination extends AbstractModel
{
    /**
     * @var boolean
     * @ModelVariable()
     */
    public $active = false;

    /**
     * @var integer
     * @ModelVariable()
     */
    public $currentPage = 1;

    /**
     * @var string
     * @ModelVariable()
     */
    public $currentLetter = 'A';

    /**
     * @var string
     * @ModelVariable()
     */
    public $mode = PaginationMode::Numbers;

    /**
     * @var integer
     * @ModelVariable()
     */
    public $totalPages = 1;

    /**
     * @var integer
     * @ModelVariable()
     */
    public $pageShift = 0;

    /**
     * @var integer
     * @ModelVariable()
     */
    public $packageStep = 100;

    /**
     * @var integer
     * @ModelVariable()
     */
    public $minPackage = 1;

    /**
     * @var integer
     * @ModelVariable()
     */
    public $maxPackage = 10;

    /**
     * @var integer
     * @ModelVariable()
     */
    public $currentPackage = 1;

    /**
     * @var integer
     * @ModelVariable()
     */
    public $packageSize = 100;

    /**
     * @var \stdClass
     * @ModelVariable(type="stdClass")
     */
    public $additionalSelectors;

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

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     * @return $this;
     */
    public function setActive($active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param mixed $currentPage
     * @return $this;
     */
    public function setCurrentPage($currentPage): self
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrentLetter()
    {
        return $this->currentLetter;
    }

    /**
     * @param mixed $currentLetter
     * @return $this;
     */
    public function setCurrentLetter($currentLetter): self
    {
        $this->currentLetter = $currentLetter;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param mixed $mode
     * @return $this;
     */
    public function setMode($mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * @param mixed $totalPages
     * @return $this;
     */
    public function setTotalPages($totalPages): self
    {
        $this->totalPages = $totalPages;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageShift()
    {
        return $this->pageShift;
    }

    /**
     * @param mixed $pageShift
     * @return $this;
     */
    public function setPageShift($pageShift): self
    {
        $this->pageShift = $pageShift;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPackageStep()
    {
        return $this->packageStep;
    }

    /**
     * @param mixed $packageStep
     * @return $this;
     */
    public function setPackageStep($packageStep): self
    {
        $this->packageStep = $packageStep;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMinPackage()
    {
        return $this->minPackage;
    }

    /**
     * @param mixed $minPackage
     * @return $this;
     */
    public function setMinPackage($minPackage): self
    {
        $this->minPackage = $minPackage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxPackage()
    {
        return $this->maxPackage;
    }

    /**
     * @param mixed $maxPackage
     * @return $this;
     */
    public function setMaxPackage($maxPackage): self
    {
        $this->maxPackage = $maxPackage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrentPackage()
    {
        return $this->currentPackage;
    }

    /**
     * @param mixed $currentPackage
     * @return $this;
     */
    public function setCurrentPackage($currentPackage): self
    {
        $this->currentPackage = $currentPackage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPackageSize()
    {
        return $this->packageSize;
    }

    /**
     * @param mixed $packageSize
     * @return $this;
     */
    public function setPackageSize($packageSize): self
    {
        $this->packageSize = $packageSize;

        return $this;
    }

    /**
     * @return \stdClass
     */
    public function getAdditionalSelectors(): \stdClass
    {
        return $this->additionalSelectors;
    }

    /**
     * @param \stdClass $additionalSelectors
     * @return $this;
     */
    public function setAdditionalSelectors(\stdClass $additionalSelectors): self
    {
        $this->additionalSelectors = $additionalSelectors;

        return $this;
    }
}