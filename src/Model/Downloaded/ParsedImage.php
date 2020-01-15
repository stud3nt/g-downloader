<?php

namespace App\Model\Downloaded;

use App\Annotation\ModelVariable;
use App\Entity\Parser\File;
use App\Enum\ParserType;
use App\Model\AbstractModel;
use App\Utils\StringHelper;
use Gregwar\Image\Image;
use Symfony\Component\Filesystem\Filesystem;

class ParsedImage extends AbstractModel
{
    /**
     * @ModelVariable()
     */
    public $resource;

    protected $width = 0;
    protected $height = 0;

    protected $tempFileDir;
    protected $operationalFileDir;
    protected $targetFileDir;

    protected $tempFilePath;
    protected $operationalFilePath;
    protected $targetFilePath;

    /** @var File */
    protected $fileEntity;

    /** @var Filesystem */
    protected $fs;

    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    public function __destruct()
    {
        unlink($this->tempFilePath);
    }

    /**
     * @return ParsedImage
     * @throws \Exception
     */
    public function prepareTempFiles(): self
    {
        if (!$this->tempFilePath)
            throw new \Exception('Temp file path must be specified before saving.');

        if (!$this->resource)
            throw new \Exception('File resource is empty.');

        $tempPathinfo = pathinfo($this->tempFilePath);

        $this->tempFileDir = $tempPathinfo['dirname'];
        $this->operationalFileDir = $this->tempFileDir;
        $this->operationalFilePath = $this->operationalFileDir.DIRECTORY_SEPARATOR.$tempPathinfo['basename']
            .StringHelper::randomStr(12).'.'.$tempPathinfo['extension'];

        if (!$this->fs->exists($this->tempFileDir))
            $this->fs->mkdir($this->tempFileDir);

        if (!$this->fs->exists($this->operationalFileDir))
            $this->fs->mkdir($this->operationalFileDir);

        file_put_contents($this->tempFilePath, $this->resource);

        return $this;
    }

    /**
     * @return ParsedImage
     * @throws \Exception
     */
    public function optimize(): self
    {
        if (!$this->tempFilePath)
            throw new \Exception('Target file path must be specified before saving.');
        else if (!file_exists($this->tempFilePath))
            throw new \Exception('File resource is empty.');

        $this->convertToJpg();

        switch ($this->fileEntity->getParser()) {
            case ParserType::HentaiFoundry:
                $this->changeImageDimensions(1920, 1200);
                break;

            case ParserType::Boards4chan:
            case ParserType::Reddit:
            case ParserType::Imagefap:
                $this->changeImageDimensions(2140, 1400);
                break;
        }

        $this->adjustCompression(5.2, (620*1024)); // max image size: 620KB

        return $this;
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

    public function changeImageDimensions(int $expectedWidth, int $expectedHeight): void
    {
        $imageWidth = $this->fileEntity->getWidth();
        $imageHeight = $this->fileEntity->getHeight();

        $imageRatio = round(($imageWidth / $imageHeight), 2);
        $expectedRatio = round(($expectedWidth / $expectedHeight), 2);

        if ($imageRatio > $expectedRatio) { // obrazek jest szerszy, niż docelowe granice
            $this->height = round(($imageHeight * 1.25));

            if ($this->height > ($expectedHeight * 1.10) || ($this->height < ($expectedHeight * 0.94))) {
                $this->height = $expectedHeight;
            }

            $this->width = round($imageWidth * ($this->height / $imageHeight));
        } else { // obrazek jest węższy, niż docelowe granice
            $this->width = round(($imageWidth * 1.4));

            if ($this->width > $expectedWidth) {
                $this->width = $expectedWidth;
            } elseif ($this->width < ($expectedHeight * 0.75)) {
                $this->width = ($this->width * 1.2);
            }

            $this->height = round($imageHeight * ($this->width / $imageWidth));
        }

        Image::open($this->tempFilePath)
            ->scaleResize($this->width, $this->height)
            ->saveJpeg($this->operationalFilePath, 80);
    }

    /**
     * @param float $minCompressionRatio
     * @param int $maxFileSize
     * @throws \Exception
     */
    public function adjustCompression(float $minCompressionRatio, int $maxFileSize): void
    {
        $filesize = filesize($this->tempFilePath);
        $compressionRatio = (($this->width * $this->height) / $filesize);

        if ($compressionRatio < $minCompressionRatio || $filesize > $maxFileSize) {
            for ($i = 1; $i <= 8; $i++) {
                $testWidth = round($this->width / (1 + (0.3 * $i)));
                $testHeight = round($this->height / (1 + (0.3 * $i)));

                Image::open($this->tempFilePath)
                    ->scaleResize($testWidth, $testHeight)
                    ->save($this->operationalFilePath, 'jpg', (80 - (1*$i)));

                $controlFilesize = filesize($this->operationalFilePath);
                $controlCompressionRatio = (($testWidth * $testHeight) / $controlFilesize);

                if ($controlCompressionRatio > $minCompressionRatio) {
                    copy($this->operationalFilePath, $this->tempFilePath);
                    unlink($this->operationalFilePath);
                    break;
                }
            }
        }
    }

    /**
     * @return ParsedImage
     * @throws \Exception
     */
    public function saveTargetFile(): bool
    {
        if (!$this->targetFilePath)
            throw new \Exception('Target file path must be specified before saving.');
        else if (!file_exists($this->tempFilePath))
            throw new \Exception('Temporary file not exists.');

        return copy($this->tempFilePath, $this->targetFilePath);
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

        return $this;
    }
}