<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use DateTimeInterface;

/**
 * ProductData
 *
 * @ORM\Table(name="tblproductdata", uniqueConstraints={@ORM\UniqueConstraint(name="strProductCode", columns={"strProductCode"})})
 * @ORM\Entity(repositoryClass="App\Repository\ProductDataRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ProductData
{
    /**
     * @var int
     *
     * @ORM\Column(name="intProductDataId", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductName", type="string", length=50, nullable=false)
     */
    private $productName;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductDesc", type="string", length=255, nullable=false)
     */
    private $productDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductCode", type="string", length=10, nullable=false)
     */
    private $productCode;

    /**
     * @var int
     *
     * @ORM\Column(name="intStock", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $stock;

    /**
     * @var float
     *
     * @ORM\Column(name="fCost", type="float", nullable=true)
     */
    private $cost;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="dtmAdded", type="datetime", nullable=true)
     */
    private $added;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="dtmDiscontinued", type="datetime", nullable=true)
     */
    private $discontinued;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="stmTimestamp", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $timestamp = 'CURRENT_TIMESTAMP';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getProductDesc(): ?string
    {
        return $this->productDesc;
    }

    public function setProductDesc(string $productDesc): self
    {
        $this->productDesc = $productDesc;

        return $this;
    }

    public function getProductCode(): ?string
    {
        return $this->productCode;
    }

    public function setProductCode(string $productCode): self
    {
        $this->productCode = $productCode;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(?float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getAdded(): ?DateTimeInterface
    {
        return $this->added;
    }

    /**
     * @param DateTimeInterface|null $added
     * @return $this
     */
    public function setAdded(?DateTimeInterface $added): self
    {
        $this->added = $added;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setAddedValue()
    {
        $this->added = new DateTime();
    }

    public function getDiscontinued(): ?DateTimeInterface
    {
        return $this->discontinued;
    }

    public function setDiscontinued(?DateTimeInterface $discontinued): self
    {
        $this->discontinued = $discontinued;

        return $this;
    }

    public function getTimestamp(): ?DateTimeInterface
    {
        return $this->timestamp;
    }

    /**
     * @param DateTimeInterface $timestamp
     * @return $this
     */
    public function setTimestamp(DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setTimestampValue()
    {
        $this->timestamp = new DateTime();
    }
}
