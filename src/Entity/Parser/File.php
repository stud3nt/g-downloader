<?php

namespace App\Entity\Parser;

use App\Annotation\EntityVariable;
use App\Entity\Base\AbstractEntity;
use App\Enum\NodeLevel;
use App\Entity\Traits\{CreatedAtTrait, IdentifierTrait, ParserTrait, NameTrait, UpdatedAtTrait, UrlTrait };
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\Mapping as ORM;

/**
 * Files
 *
 * @ORM\Table(name="parsed_files", indexes={
 *     @ORM\Index(name="IDX__parsed_files__identifier", columns={"identifier"}),
 *     @ORM\Index(name="IDX__parsed_files__node_id", columns={"node_id"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class File extends AbstractEntity
{
    use CreatedAtTrait,
        UpdatedAtTrait,
        IdentifierTrait,
        NameTrait,
        UrlTrait,
        ParserTrait;

    /**
     * @ORM\Column(name="file_url", type="string", length=2048, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $fileUrl = null;

    /**
     * @ORM\Column(name="description", type="string", length=4096, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="bin_hash", type="string", length=64, nullable=true)
     * @EntityVariable(convertable=false, writable=false, readable=false)
     */
    protected $binHash;

    /**
     * @ORM\Column(name="hex_hash", type="string", length=255, nullable=true)
     * @EntityVariable(convertable=false, writable=false, readable=false)
     */
    protected $hexHash;

    /**
     * @ORM\Column(name="extension", type="string", length=8, nullable=false)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $extension;

    /**
     * @ORM\Column(name="thumbnail", type="string", length=1024, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $thumbnail;

    /**
     * @EntityVariable(convertable=true, writable=false, readable=true)
     */
    protected $localThumbnail;

    /**
     * @ORM\Column(name="mime_type", type="string", length=16, nullable=false)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $mimeType;

    /**
     * @var \DateTime
     * @ORM\Column(name="uploaded_at", type="datetime", options={"default"="CURRENT_TIMESTAMP"}, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true, converter="DateTime")
     */
    protected $uploadedAt;

    /**
     * @ORM\Column(name="type", columnDefinition="ENUM('image', 'video')", nullable=false)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $type;

    /**
     * @ORM\Column(name="icon", type="string", length=16, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $icon = null;

    /**
     * @ORM\Column(name="download_info", type="array", nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
    */
    protected $downloadInfo = [];

    /**
     * @ORM\Column(name="width", type="integer", nullable=true, length=4, options={"unsigned"=true, "default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $width = 0;

    /**
     * @ORM\Column(name="height", type="integer", nullable=true, length=4, options={"unsigned"=true, "default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $height = 0;

    /**
     * @ORM\Column(name="length", type="integer", nullable=true, length=8, options={"unsigned"=true, "default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $length = 0;

    /**
     * @ORM\Column(name="size", type="integer", nullable=false, length=11, options={"unsigned"=true, "default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $size = 0;

    /**
     * @ORM\Column(name="dimension_ratio", type="decimal", precision=5, scale=2, options={"default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $dimensionRatio = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="downloaded_at", type="datetime", nullable=true)
     * @EntityVariable(convertable=true, converter="DateTime", writable=true, readable=true)
     */
    protected $downloadedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Parser\File")
     * @ORM\JoinColumn(name="duplicate_of_id", referencedColumnName="id")
     * @EntityVariable(convertable=false, writable=false, readable=false)
     */
    protected $duplicateOf;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Parser\Node", inversedBy="files")
     * @ORM\JoinColumn(name="node_id", referencedColumnName="id", nullable=true)
     */
    protected $parentNode;

    /**
     * @EntityVariable(convertable=true, writable=false, readable=true)
     */
    protected $imageUrl;

    /**
     * @EntityVariable(convertable=true, writable=false, readable=true)
     */
    protected $mainStatus;

    /**
     * @ORM\Column(name="is_corrupted", type="boolean")
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $corrupted = false;

    /**
     * @EntityVariable(convertable=true, writable=false, readable=true)
     */
    protected $textSize = '0 bytes';

    protected $curlRequest;
    /** @var string */
    protected $tempFilePath = null;
    /** @var string */
    protected $cleanTempFilePath = null;
    /** @var string */
    protected $targetFilePath;

    /** @var NodeSettings|null */
    protected $nodeSettings = null;

    public function __construct()
    {
        if (!$this->id) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }

    public function getRedisDownloadKey()
    {
        return 'file_download_'.$this->getIdentifier();
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(?\DateTimeInterface $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        if ($this->width > 0 && $this->height > 0)
            $this->dimensionRatio = round(($this->width / $this->height), 2);

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        if ($this->width > 0 && $this->height > 0)
            $this->dimensionRatio = round(($this->width / $this->height), 2);

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getDownloadedAt(): ?\DateTimeInterface
    {
        return $this->downloadedAt;
    }

    public function setDownloadedAt(?\DateTimeInterface $downloadedAt): self
    {
        $this->downloadedAt = $downloadedAt;

        return $this;
    }

    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    public function setFileUrl(?string $fileUrl): self
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTextSize()
    {
        return $this->textSize;
    }

    /**
     * @param mixed $textSize
     * @return self
     */
    public function setTextSize($textSize): self
    {
        $this->textSize = $textSize;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocalThumbnail()
    {
        return $this->localThumbnail;
    }

    /**
     * @param mixed $localThumbnail
     * @return self
     */
    public function setLocalThumbnail($localThumbnail): self
    {
        $this->localThumbnail = $localThumbnail;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param mixed $imageUrl
     * @return self
     */
    public function setImageUrl($imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMainStatus()
    {
        return $this->mainStatus;
    }

    /**
     * @param mixed $mainStatus
     * @return self
     */
    public function setMainStatus($mainStatus): self
    {
        $this->mainStatus = $mainStatus;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurlRequest()
    {
        return $this->curlRequest;
    }

    /**
     * @param mixed $curlRequest
     * @return self
     */
    public function setCurlRequest($curlRequest): self
    {
        $this->curlRequest = $curlRequest;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTempFilePath(): ?string
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
     * @return string
     */
    public function getCleanTempFilePath(): ?string
    {
        return $this->cleanTempFilePath;
    }

    /**
     * @param string $cleanTempFilePath
     * @return self
     */
    public function setCleanTempFilePath(string $cleanTempFilePath = null): self
    {
        $this->cleanTempFilePath = $cleanTempFilePath;

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

    public function getParentNode(): ?Node
    {
        return $this->parentNode;
    }

    public function setParentNode(?Node $parentNode): self
    {
        $this->parentNode = $parentNode;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCorrupted(): bool
    {
        return $this->corrupted;
    }

    /**
     * @param bool $corrupted
     * @return File
     */
    public function setCorrupted(bool $corrupted): self
    {
        $this->corrupted = $corrupted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDimensionRatio()
    {
        return $this->dimensionRatio;
    }

    /**
     * @param mixed $dimensionRatio
     * @return File
     */
    public function setDimensionRatio($dimensionRatio): self
    {
        $this->dimensionRatio = $dimensionRatio;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBinHash()
    {
        return $this->binHash;
    }

    /**
     * @param mixed $binHash
     * @return $this
     */
    public function setBinHash($binHash): self
    {
        $this->binHash = $binHash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHexHash()
    {
        return $this->hexHash;
    }

    /**
     * @param mixed $hexHash
     * @return $this
     */
    public function setHexHash($hexHash): self
    {
        $this->hexHash = $hexHash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDuplicateOf()
    {
        return $this->duplicateOf;
    }

    /**
     * @param mixed $duplicateOf
     * @return $this
     */
    public function setDuplicateOf($duplicateOf): self
    {
        $this->duplicateOf = $duplicateOf;

        return $this;
    }

    /**
     * @return NodeSettings|null
     */
    public function getNodeSettings(): ?NodeSettings
    {
        return $this->nodeSettings ?? $this->getFinalNodeSettings();
    }

    /**
     * @param NodeSettings|null $nodeSettings
     * @return $this;
     */
    public function setNodeSettings(?NodeSettings $nodeSettings): self
    {
        $this->nodeSettings = $nodeSettings;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getFinalNodeSettings(): ?NodeSettings
    {
        $nodeSettings = $this->determineNodeSettings($this->getParentNode());
        krsort($nodeSettings);

        if ($nodeSettings) {
            foreach ($nodeSettings as $setting) {
                if (!empty($setting) && !$setting->isEmpty()) {
                    return $setting;
                }
            }
        }

        return null;
    }

    private function determineNodeSettings(Node $node = null): array
    {
        $settingsArray = [];

        if (!$node)
            return [];

        if ($settings = $node->getSettings()) {
            $nodeLevel = NodeLevel::determineLevelValue($node->getLevel());
            $settingsArray[$nodeLevel] = $settings;
        }


        if ($parentNode = $node->getParentNode()) {
            $parentNodeSettings = $this->determineNodeSettings($parentNode);

            if ($parentNodeSettings) {
                foreach ($parentNodeSettings as $parentNodeSetting) {
                    $parentLevel = NodeLevel::determineLevelValue($parentNode->getLevel());
                    $settingsArray[$parentLevel] = $parentNodeSetting;
                }
            }
        }

        return $settingsArray;
    }

    public function getDownloadInfo(): ?array
    {
        return $this->downloadInfo;
    }

    public function setDownloadInfo(?array $downloadInfo): self
    {
        $this->downloadInfo = $downloadInfo;

        return $this;
    }
}
