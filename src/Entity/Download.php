<?php

namespace App\Entity;

use App\Entity\Base\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Downloads actions
 *
 * @ORM\Table("download")
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class Download extends AbstractEntity
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="starts_at", type="datetime", nullable=false)
     */
    protected $startsAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ends_at", type="datetime", nullable=false)
     */
    protected $endsAt;

    /**
     * @ORM\Column(name="downloaded_files", type="integer", nullable=true, length=8, options={"unsigned"=true, "default":0})
     */
    protected $downloadedFiles = 0;

    public function getStartsAt(): ?\DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeInterface $startsAt): self
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeInterface $endsAt): self
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getDownloadedFiles(): ?int
    {
        return $this->downloadedFiles;
    }

    public function setDownloadedFiles(?int $downloadedFiles): self
    {
        $this->downloadedFiles = $downloadedFiles;

        return $this;
    }

}
