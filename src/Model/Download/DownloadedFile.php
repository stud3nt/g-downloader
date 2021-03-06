<?php

namespace App\Model\Download;

use App\Annotation\ModelVariable;
use App\Entity\Parser\File;
use App\Entity\Parser\NodeSettings;
use App\Enum\FileType;
use App\Enum\ParserType;
use App\Model\AbstractModel;
use App\Utils\FilesHelper;
use App\Utils\StringHelper;
use Gregwar\Image\Image;
use Intervention\Image\Exception\NotReadableException;
use Jenssegers\ImageHash\Hash;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Symfony\Component\Filesystem\Filesystem;

class DownloadedFile extends AbstractModel
{
    /** @ModelVariable() */
    public $resource;

    protected $width = 0;
    protected $height = 0;

    protected $tempFileDir;
    protected $operationalFileDir;

    protected $tempFilePath;
    protected $operationalFilePath;
    protected $targetFilePath;

    /** @var File */
    protected $fileEntity;

    /** @var NodeSettings */
    protected $settings;

    /** @var Filesystem */
    protected $fs;

    /** @var ImageHash */
    protected $imageHasher;

    protected $id3;

    public function __construct()
    {
        $this->fs = new Filesystem();
        $this->id3 = new \getID3();
        $this->imageHasher = new ImageHash(
            new DifferenceHash()
        );
    }

    public function __destruct()
    {
        if (file_exists($this->tempFilePath))
            unlink($this->tempFilePath);

        if (file_exists($this->operationalFilePath))
            unlink($this->operationalFilePath);
    }

    /**
     * @return DownloadedFile
     * @throws \Exception
     */
    public function prepareTempFiles(): self
    {
        if (!$this->tempFilePath)
            throw new \Exception('Temp file path must be specified before saving.');

        if (!$this->resource)
            throw new \Exception('File resource is empty.');

        if (!file_exists($this->tempFilePath)) { // error in file download
            $this->fileEntity->setCorrupted(true);
        } else {
            $tempPathinfo = pathinfo($this->tempFilePath);

            $this->tempFileDir = $tempPathinfo['dirname'];
            $this->operationalFileDir = $this->tempFileDir;
            $this->operationalFilePath = $this->operationalFileDir.DIRECTORY_SEPARATOR.$tempPathinfo['filename']
                .StringHelper::randomStr(12).'.'.$tempPathinfo['extension'];

            if (!$this->fs->exists($this->tempFileDir))
                $this->fs->mkdir($this->tempFileDir);

            if (!$this->fs->exists($this->operationalFileDir))
                $this->fs->mkdir($this->operationalFileDir);

            file_put_contents($this->tempFilePath, $this->resource);
        }

        return $this;
    }

    public function analyseTempFiles(): self
    {
        if ($this->getFileEntity()->getType() === FileType::Image) {
            try {
                $hash = $this->imageHasher->hash($this->tempFilePath);
                $this->getFileEntity()->setBinHash($hash->toBits())
                    ->setHexHash($hash->toHex());
            } catch (NotReadableException $ex) {
                $this->getFileEntity()->setCorrupted(true); // file corrupted or invalid format.
            }
        } elseif ($this->getFileEntity()->getType() === FileType::Video) {
            $data = $this->id3->analyze($this->tempFilePath);

            if (array_key_exists('playtime_seconds', $data)) {
                $length = $data['playtime_seconds'];
                $minLength = ($this->settings->getMinLength() > 0) ? $this->settings->getMinLength() : 14;

                if ($length < $minLength) {
                    $this->getFileEntity()->setCorrupted(true);
                    return $this;
                }

                $this->getFileEntity()->setLength($length);
            } else {
                $this->getFileEntity()->setCorrupted(true);
                return $this;
            }
        }

        $this->getFileEntity()->setDimensionRatio(
            round(($this->getFileEntity()->getWidth() / $this->getFileEntity()->getHeight()), 2)
        );

        return $this;
    }

    /**
     * @param File[] $potentialDuplicates
     * @return $this
     */
    public function analysePotentialDuplicates(array $potentialDuplicates = []): self
    {
        $bestHashDistance = 1000;
        $currentImageHash = Hash::fromHex($this->fileEntity->getHexHash());

        if ($potentialDuplicates) {
            foreach ($potentialDuplicates as $potentialDuplicate) {
                if ($this->getFileEntity()->getType() === FileType::Image) {
                    $compareHash = Hash::fromHex($potentialDuplicate->getHexHash());
                    $distance = $currentImageHash->distance($compareHash);

                    if ($distance < 14 && $distance < $bestHashDistance) { // distance less than 14 equals very similar or identical images
                        $bestHashDistance = $distance;
                        $this->getFileEntity()->setDuplicateOf($potentialDuplicate);

                        if ($distance < 4)
                            break;
                    }
                } elseif ($this->getFileEntity()->getType() === FileType::Video) {
                    if ($potentialDuplicate->getLength() === $this->getFileEntity()->getLength()) {
                        if ($potentialDuplicate->getSize() === $this->getFileEntity()->getSize() ||
                            $potentialDuplicate->getName() === $this->getFileEntity()->getName() ||
                            $potentialDuplicate->getThumbnail() == $this->getFileEntity()->getThumbnail()
                        ) {
                            $this->getFileEntity()->setDuplicateOf($potentialDuplicate);
                            break;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return DownloadedFile
     * @throws \Exception
     */
    public function optimizeImage(): self
    {
        if (!$this->tempFilePath)
            throw new \Exception('Target file path must be specified before saving.');
        elseif (filesize($this->tempFilePath) < (10 * 1024)) { // file smaller than 10kB - corrupted :(
            $this->fileEntity->setCorrupted(true);
            return $this;
        } elseif ($this->fileEntity->getDuplicateOf()) { // duplicate - don't waste CPU time;
            return $this;
        }

        $this->convertToJpg();

        switch ($this->fileEntity->getParser()) {
            case ParserType::HentaiFoundry:
                $expectedWidth = $this->settings->getMaxWidth() > 0 ? $this->settings->getMaxWidth() : 1920;
                $expectedHeight = $this->settings->getMaxHeight() > 0 ? $this->settings->getMaxHeight() : 1200;
                break;

            default:
                $expectedWidth = $this->settings->getMaxWidth() > 0 ? $this->settings->getMaxWidth() : 2000;
                $expectedHeight = $this->settings->getMaxHeight() > 0 ? $this->settings->getMaxHeight() : 1600;
                break;
        }

        $expectedCompressionRatio = $this->detectExpectedCompressionRatio();
        $maxSize = ($this->settings->getMaxSize() > 0)
            ? FilesHelper::sizeToBytes($this->settings->getMaxSize().' '.$this->settings->getSizeUnit())
            : (620 * 1024); // max image size: 620KB or from settings;

        $this->changeImageDimensions($expectedWidth, $expectedHeight);
        $this->adjustCompression($expectedCompressionRatio, $maxSize);

        return $this;
    }

    /**
     * @return float
     * @throws \Exception
     */
    public function detectExpectedCompressionRatio(): float
    {
        Image::open($this->tempFilePath)
            ->grayscale()
            ->save($this->operationalFilePath, 'jpg', 80);

        $tempFilesize = filesize($this->tempFilePath);
        $operationalFilesize = filesize($this->operationalFilePath);

        if (($operationalFilesize * 1.05) < $tempFilesize)
            $ratio = 2.8; // color image (big size loss after grayscaling);
        else
            $ratio = 5.2; // grayscale;

        $pixels = ($this->width * $this->height);

        if ($pixels > 500000) {
            $pixelsRatio = 1 + (($pixels - 500000) / 3000000);
            $ratio = round(($ratio / $pixelsRatio), 2);
        }

        return $ratio;
    }

    /**
     * @throws \Exception
     */
    public function convertToJpg(): void
    {
        if ($this->fileEntity->getExtension() !== 'jpg') {
            $newTempFilePath = $this->tempFileDir.DIRECTORY_SEPARATOR.$this->fileEntity->getName().'.jpg';

            Image::open($this->tempFilePath)->save($newTempFilePath, 'jpg', 80);
            unlink($this->tempFilePath);

            $this->tempFilePath = $newTempFilePath;
        }
    }

    /**
     * @param int $expectedWidth
     * @param int $expectedHeight
     * @throws \Exception
     */
    public function changeImageDimensions(int $expectedWidth, int $expectedHeight): void
    {
        $imageWidth = $this->fileEntity->getWidth();
        $imageHeight = $this->fileEntity->getHeight();

        $this->width = $imageWidth;
        $this->height = $imageHeight;

        $imagePixels = ($this->width * $this->height);
        $imageRatio = round(($imageWidth / $imageHeight), 2);

        $expectedRatio = round(($expectedWidth / $expectedHeight), 2);
        $maxExpectedPixels = (($expectedWidth * $expectedHeight) * 1.1);

        if ($imageRatio > $expectedRatio && $imagePixels > $maxExpectedPixels) { // obrazek jest szerszy, niż docelowe granice
            $this->height = round(($imageHeight * 1.25));

            if ($this->height > ($expectedHeight * 1.10) || ($this->height < ($expectedHeight * 0.94)))
                $this->height = $expectedHeight;

            $this->width = round($imageWidth * ($this->height / $imageHeight));
        } elseif ($imageRatio < $expectedRatio && $imagePixels > $maxExpectedPixels) { // obrazek jest węższy, niż docelowe granice
            $this->width = round(($imageWidth * 1.4));

            if ($this->width > $expectedWidth)
                $this->width = $expectedWidth;
            elseif ($this->width < ($expectedHeight * 0.75))
                $this->width = ($this->width * 1.2);

            $this->height = round($imageHeight * ($this->width / $imageWidth));
        }

        Image::open($this->tempFilePath)
            ->scaleResize($this->width, $this->height)
            ->save($this->tempFilePath, 'jpg', 90);
    }

    /**
     * @param float $minCompressionRatio
     * @param int $maxFileSize
     * @throws \Exception
     */
    public function adjustCompression(float $minCompressionRatio, int $maxFileSize): void
    {
        $fileSize = filesize($this->tempFilePath);
        $compressionRatio = (($this->width * $this->height) / $fileSize);

        if ($compressionRatio < $minCompressionRatio || $fileSize > $maxFileSize) {
            for ($i = 1; $i <= 8; $i++) {
                $testWidth = round($this->width / (1 + (0.1 * $i)));
                $testHeight = round($this->height / (1 + (0.1 * $i)));

                Image::open($this->tempFilePath)
                    ->scaleResize($testWidth, $testHeight)
                    ->save($this->operationalFilePath, 'jpg', (90 - (2*$i)));

                $controlFilesize = filesize($this->operationalFilePath);
                $controlCompressionRatio = (($testWidth * $testHeight) / $controlFilesize);

                if (($controlCompressionRatio > $minCompressionRatio && $controlFilesize < $maxFileSize) || ($controlFilesize < ($maxFileSize * 0.8)) || $i === 8) {
                    copy($this->operationalFilePath, $this->tempFilePath);
                    unlink($this->operationalFilePath);
                    break;
                }
            }
        }
    }

    /**
     * @return DownloadedFile
     * @throws \Exception
     */
    public function saveTargetFile(): bool
    {
        if (!$this->targetFilePath)
            throw new \Exception('Target file path must be specified before saving.');
        else if (!file_exists($this->tempFilePath))
            throw new \Exception('Temp file not exists');

        // if file is corrupted OR is duplicate of existing file:
        return ($this->fileEntity->isCorrupted() || $this->fileEntity->getDuplicateOf())
            ? true
            : copy($this->tempFilePath, $this->targetFilePath);
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $resource
     * @return self
     */
    public function setResource($resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTempFilePath()
    {
        return $this->tempFilePath;
    }

    /**
     * @param mixed $tempFilePath
     * @return self
     */
    public function setTempFilePath($tempFilePath): self
    {
        $this->tempFilePath = $tempFilePath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTargetFilePath()
    {
        return $this->targetFilePath;
    }

    /**
     * @param mixed $targetFilePath
     * @return self
     */
    public function setTargetFilePath($targetFilePath): self
    {
        $this->targetFilePath = $targetFilePath;

        return $this;
    }


    /**
     * @return File
     */
    public function getFileEntity(): File
    {
        return $this->fileEntity;
    }

    /**
     * @param File $fileEntity
     * @return self
     */
    public function setFileEntity(File $fileEntity): self
    {
        $this->fileEntity = $fileEntity;

        $this->tempFilePath = $fileEntity->getTempFilePath();
        $this->targetFilePath = $fileEntity->getTargetFilePath();
        $this->settings = $fileEntity->getFinalNodeSettings();

        if (!$this->settings)
            $this->settings = new NodeSettings();

        file_put_contents($this->tempFilePath, '');

        return $this;
    }
}