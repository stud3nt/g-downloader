<?php

namespace App\Model;

use App\Annotation\Serializer\ObjectVariable;
use App\Enum\FolderType;
use App\Enum\PrefixSufixType;

class ParsedNodeSettings extends AbstractModel
{
    /**
     * @ObjectVariable(type="integer")
     */
    public ?int $id = null;

    /**
     * @ObjectVariable(type="string")
     */
    public string $prefixType = PrefixSufixType::CustomText;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $prefix = null;

    /**
     * @ObjectVariable(type="string")
     */
    public string $sufixType = PrefixSufixType::CustomText;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $sufix = null;

    /**
     * @ObjectVariable(type="string")
     */
    public string $folderType = FolderType::CustomText;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $folder = null;

    /**
     * @ObjectVariable(type="integer")
     */
    public int $maxWidth = 0;

    /**
     * @ObjectVariable(type="integer")
     */
    public int $maxHeight = 0;

    /**
     * @ObjectVariable(type="integer")
     */
    public int $maxSize = 0;

    /**
     * @ObjectVariable(type="integer")
     */
    public int $minLength = 0;

    /**
     * @ObjectVariable(type="string")
     */
    public string $sizeUnit = 'B';

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string|null $prefix
     * @return $this;
     */
    public function setPrefix(string $prefix = null): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getSufix(): string
    {
        return $this->sufix;
    }

    /**
     * @param string|null $sufix
     * @return $this;
     */
    public function setSufix(string $sufix = null): self
    {
        $this->sufix = $sufix;

        return $this;
    }

    /**
     * @return string
     */
    public function getFolder(): string
    {
        return $this->folder;
    }

    /**
     * @param string|null $folder
     * @return $this;
     */
    public function setFolder(string $folder = null): self
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxWidth(): int
    {
        return $this->maxWidth;
    }

    /**
     * @param int $maxWidth
     * @return $this;
     */
    public function setMaxWidth(int $maxWidth = 0): self
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxHeight(): int
    {
        return $this->maxHeight;
    }

    /**
     * @param int $maxHeight
     * @return $this;
     */
    public function setMaxHeight(int $maxHeight = 0): self
    {
        $this->maxHeight = $maxHeight;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * @param int $maxSize
     * @return self
     */
    public function setMaxSize(int $maxSize = 0): self
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefixType(): ?string
    {
        return $this->prefixType;
    }

    /**
     * @param string $prefixType
     * @return self
     */
    public function setPrefixType(?string $prefixType): self
    {
        $this->prefixType = $prefixType;

        return $this;
    }

    /**
     * @return string
     */
    public function getSufixType(): ?string
    {
        return $this->sufixType;
    }

    /**
     * @param string $sufixType
     * @return self
     */
    public function setSufixType(?string $sufixType): self
    {
        $this->sufixType = $sufixType;

        return $this;
    }

    /**
     * @return string
     */
    public function getFolderType(): ?string
    {
        return $this->folderType;
    }

    /**
     * @param string $folderType
     * @return self
     */
    public function setFolderType(?string $folderType): self
    {
        $this->folderType = $folderType;

        return $this;
    }

    /**
     * @return string
     * @return self
     */
    public function getSizeUnit(): ?string
    {
        return $this->sizeUnit;
    }

    /**
     * @param string $sizeUnit
     */
    public function setSizeUnit(?string $sizeUnit): self
    {
        $this->sizeUnit = $sizeUnit;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinLength(): int
    {
        return $this->minLength;
    }

    /**
     * @param int $minLength
     * @return self
     */
    public function setMinLength(int $minLength = 0): self
    {
        $this->minLength = $minLength;

        return $this;
    }
}