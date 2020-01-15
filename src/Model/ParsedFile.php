<?php

namespace App\Model;

use App\Annotation\ModelVariable;
use App\Utils\FilesHelper;

class ParsedFile extends AbstractModel
{
    /**
     * @ModelVariable()
     */
    public $name;

    /**
     * @ModelVariable()
     */
    public $title;

    /**
     * @ModelVariable()
     */
    public $description;

    /**
     * @ModelVariable()
     */
    public $domain;

    /**
     * @ModelVariable()
     */
    public $extension;

    /**
     * @ModelVariable()
     */
    public $url;

    /**
     * @ModelVariable()
     */
    public $fileUrl;

    /**
     * @ModelVariable()
     */
    public $localUrl;

    /**
     * @ModelVariable()
     */
    public $previewUrl;

    /**
     * @ModelVariable()
     */
    public $mimeType;

    /**
     * @ModelVariable()
     */
    public $type;

    /**
     * @ModelVariable()
     */
    public $uploadedAt;

    /**
     * @ModelVariable()
     */
    public $downloadedAt;

    /**
     * @ModelVariable(type="integer")
     */
    public $width = 0;

    /**
     * @ModelVariable(type="integer")
     */
    public $height = 0;

    /**
     * @ModelVariable(type="integer")
     */
    public $size = 0;

    /**
     * @ModelVariable(type="integer")
     */
    public $length = 0;

    /**
     * @ModelVariable()
     */
    public $textSize = null;

    /**
     * @ModelVariable()
     */
    public $identifier;

    /**
     * @ModelVariable()
     */
    public $parser;

    /**
     * @ModelVariable()
     */
    public $thumbnail;

    /**
     * @ModelVariable()
     */
    public $localThumbnail;

    /**
     * @ModelVariable()
     */
    public $htmlPreview;

    /**
     * @ModelVariable(type="stdClass")
     */
    public $statuses;

    public function __construct(string $parser = null, string $type = null)
    {
        if ($parser) {
            $this->setParser($parser);
        }

        if ($type) {
            $this->setType($type);
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     * @return $this
     */
    public function setExtension($extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getFullFilename(): string
    {
        return $this->getName().(!empty($this->getExtension())
            ? '.'.$this->getExtension()
            : '');
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return $this
     */
    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return $this;
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     * @return $this;
     */
    public function setDomain($domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return $this
     */
    public function setUrl($url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param mixed $mimeType
     * @return $this
     */
    public function setMimeType($mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return $this
     */
    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUploadedAt()
    {
        return $this->uploadedAt;
    }

    /**
     * @param mixed $uploadedAt
     * @return $this
     */
    public function setUploadedAt($uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDownloadedAt()
    {
        return $this->downloadedAt;
    }

    /**
     * @param mixed $downloadedAt
     * @return $this
     */
    public function setDownloadedAt($downloadedAt): self
    {
        $this->downloadedAt = $downloadedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     * @return $this
     */
    public function setWidth($width): self
    {
        $this->width = (int)$width;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     * @return $this
     */
    public function setHeight($height): self
    {
        $this->height = (int)$height;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize(int $size): self
    {
        $this->size = (int)$size;

        if (empty($this->getTextSize())) {
            $this->setTextSize(FilesHelper::bytesToSize($size));
        }

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
     * @param string $textSize
     * @return $this
     */
    public function setTextSize(string $textSize): self
    {
        $this->textSize = $textSize;

        if ($this->getSize() === 0) {
            $this->setSize(FilesHelper::sizeToBytes($textSize));
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     * @return $this;
     */
    public function setLength($length): self
    {
        $this->length = (int)$length;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param mixed $identifier
     * @return $this
     */
    public function setIdentifier($identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @param mixed $parser
     * @return $this
     */
    public function setParser($parser): self
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param mixed $thumbnail
     * @return $this
     */
    public function setThumbnail($thumbnail): self
    {
        $this->thumbnail = $thumbnail;

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
     * @return $this
     */
    public function setLocalThumbnail($localThumbnail): self
    {
        $this->localThumbnail = $localThumbnail;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPreviewUrl()
    {
        return $this->previewUrl;
    }

    /**
     * @param mixed $previewUrl
     * @return $this
     */
    public function setPreviewUrl($previewUrl): self
    {
        $this->previewUrl = $previewUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileUrl()
    {
        return $this->fileUrl;
    }

    /**
     * @param mixed $fileUrl
     * @return $this;
     */
    public function setFileUrl($fileUrl): self
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocalUrl()
    {
        return $this->localUrl;
    }

    /**
     * @param mixed $localUrl
     */
    public function setLocalUrl($localUrl): void
    {
        $this->localUrl = $localUrl;
    }

    /**
     * @param mixed $statuses
     * @return $this
     */
    public function setStatuses($statuses): self
    {
        $this->statuses = $statuses;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHtmlPreview()
    {
        return $this->htmlPreview;
    }

    /**
     * @param mixed $htmlPreview
     * @return $this;
     */
    public function setHtmlPreview($htmlPreview): self
    {
        $this->htmlPreview = $htmlPreview;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    public function clearStatuses(): self
    {
        $this->statuses = [];

        return $this;
    }

    public function addStatus(string $status): self
    {
        if (!$this->hasStatus($status)) {
            $this->statuses[] = $status;
        }

        return $this;
    }

    public function removeStatus(string $deletingStatus): self
    {
        if ($this->statuses) {
            foreach ($this->statuses as $statusIndex => $status) {
                if ($status === $deletingStatus) {
                    unset($this->statuses[$statusIndex]);
                }
            }
        }

        return $this;
    }

    public function hasStatus(string $checkedStatus): bool
    {
        if ($this->statuses) {
            foreach ($this->statuses as $status) {
                if ($status === $checkedStatus) {
                    return true;
                }
            }
        }

        return false;
    }
}