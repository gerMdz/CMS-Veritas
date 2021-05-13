<?php


namespace App\Entity\Traits;


use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;

trait ImageTrait
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageFilename;

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function getImagePath()
    {
        return UploaderHelper::IMAGE_ENTRADA.'/'.$this->getImageFilename();
    }
}