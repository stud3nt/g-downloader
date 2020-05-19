<?php

namespace App\Model;

use App\Annotation\ModelVariable;
use App\Enum\PaginationMode;

class Pagination extends AbstractModel
{
    /**
     * @var boolean
     * @ModelVariable(type="boolean")
     */
    public $active = false;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $currentPage = 1;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $pagesPackageSize = 2;

    /**
     * @var string
     * @ModelVariable(type="string")
     */
    public $currentLetter = 'A';

    /**
     * @var string
     * @ModelVariable(type="string")
     */
    public $mode = PaginationMode::Numbers;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $totalPages = 1;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $pageShift = 0;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $packageStep = 100;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $minPackage = 1;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $maxPackage = 10;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $currentPackage = 1;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $packageSize = 100;

    /**
     * @var PaginationSelector[]
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\PaginationSelector"}, type="array")
     */
    public $selectors;

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
    public function setNumericPagination(int $currentPage = 1, int $totalPages = 1, int $pageShift = 0) : Pagination
    {
        $this->reset();

        $this->active = true;
        $this->mode = PaginationMode::Numbers;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->pageShift = $pageShift;

        return $this;
    }

    public function setLetterPagination(string $letter = 'A') : Pagination
    {
        $this->reset();

        $this->active = true;
        $this->mode = PaginationMode::Letters;
        $this->currentLetter = $letter;

        return $this;
    }

    public function setLoadMorePagination() : Pagination
    {
        $this->reset();

        $this->active = true;
        $this->mode = PaginationMode::LoadMore;

        return $this;
    }

    public function getActiveSelector(): ?PaginationSelector
    {
        if ($this->selectors) {
            foreach ($this->selectors as $selector) {
                if ($selector->isActive()) {
                    return $selector;
                }
            }

            return $this->selectors[0];
        }

        return null;
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
     * @return PaginationSelector[]
     */
    public function getSelectors(): ?array
    {
        return $this->selectors;
    }

    public function getSelectorsCount(): int
    {
        return count($this->selectors);
    }

    /**
     * @param PaginationSelector[] $selectors
     * @return $this
     */
    public function setSelectors(array $selectors): self
    {
        $this->selectors = $selectors;

        return $this;
    }

    /**
     * @return int
     */
    public function getPagesPackageSize(): int
    {
        return $this->pagesPackageSize;
    }

    /**
     * @param int $pagesPackageSize
     * @return Pagination
     */
    public function setPagesPackageSize(int $pagesPackageSize = 2): Pagination
    {
        $this->pagesPackageSize = $pagesPackageSize;

        return $this;
    }
}