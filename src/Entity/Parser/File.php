<?php

namespace App\Entity\Parser;

use App\Annotation\EntityVariable;
use App\Entity\Base\AbstractEntity;
use App\Entity\Traits\{CreatedAtTrait, IdentifierTrait, ParserTrait, NameTrait, UpdatedAtTrait, UrlTrait };
use Doctrine\ORM\Mapping as ORM;

/**
 * Files
 *
 * @ORM\Table(name="files", indexes={
 *     @ORM\Index(name="identifier_idx", columns={"identifier"})
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
     * @ORM\Column(name="width", type="integer", nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $width = 0;

    /**
     * @ORM\Column(name="height", type="integer", nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $height = 0;

    /**
     * @ORM\Column(name="length", type="integer", nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $length = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $size = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="downloaded_at", type="datetime", nullable=true)
     * @EntityVariable(convertable=true, converter="DateTime", writable=true, readable=true)
     */
    protected $downloadedAt;

    /**
     * @ORM\Column(name="color_hash", type="string", length=64, nullable=true)
     */
    protected $colorHash;

    /**
     * @EntityVariable(convertable=true, writable=false, readable=true)
     */
    protected $imageUrl;

    /**
     * @EntityVariable(convertable=true, writable=false, readable=true)
     */
    protected $mainStatus;

    public function __construct()
    {
        if (!$this->id) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
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

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

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

    public function getColorHash(): ?string
    {
        return $this->colorHash;
    }

    public function setColorHash(?string $colorHash): self
    {
        $this->colorHash = $colorHash;

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
}
