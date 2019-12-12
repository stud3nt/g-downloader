<?php

namespace App\Traits;

use App\Service\FileCache;
use App\Utils\CacheHelper;

trait PageLoaderTrait
{
    protected $pageLoaderProgressData = [];

    /** @var FileCache */
    protected $cache;

    public function startProgress(string $progressName, int $totalValue, int $startPercent = 10, int $endPercent = 100): void
    {
        $this->pageLoaderProgressData[$progressName] = [
            'startPercent' => $startPercent,
            'endPercent' => $endPercent,
            'totalValue' => $totalValue,
            'currentValue' => 0,
            'stepSize' => 1
        ];

        $this->setPageLoaderProgress($startPercent);
    }

    public function endProgress(string $progressName) : void
    {
        $this->setPageLoaderProgress(
            $this->pageLoaderProgressData[$progressName]['endPercent']
        );

        unset($this->pageLoaderProgressData[$progressName]);
    }

    public function progressStep(string $progressName) : void
    {
        $progressData = $this->pageLoaderProgressData[$progressName];
        $newValue = $progressData['currentValue'] + $progressData['stepSize'];

        $rangeSize = $progressData['endPercent'] - $progressData['startPercent']; // calculate percent range size
        $calculatedProgressValue = round((($newValue / $progressData['totalValue']) * $rangeSize), 2);
        $calculatedProgressValue += $progressData['startPercent'];

        $this->pageLoaderProgressData[$progressName]['currentValue'] = $newValue;

        $this->setPageLoaderProgress($calculatedProgressValue);
    }

    /**
     * Increase progress by defined step, but no more than limit;
     *
     * @param int $stepSize
     * @param int $limit
     */
    public function makeBlindStep(int $stepSize, int $limit = 50) : void
    {
        $currentStep = CacheHelper::getPageLoaderData()['progress'] ?? 0;
        $projectedStep = ($currentStep + $stepSize);

        $this->setPageLoaderProgress(
            ($projectedStep > $limit) ? $limit : $projectedStep
        );
    }

    public function setPageLoaderProgress(int $progress = 0) : void
    {
        $this->cache->savePageLoaderProgress($progress);
    }

    public function setPageLoaderDescription(string $description = '') : void
    {
        $this->cache->savePageLoaderDescription($description);
    }
}