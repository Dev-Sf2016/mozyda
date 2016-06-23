<?php
namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ProductRepository")
 * @UniqueEntity("id")
 * @ORM\HasLifecycleCallbacks()
 */
class Product
{

    /**
     * @ORM\OneToMany(targetEntity="ProdCat", mappedBy="prod_id")
     */

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="product_name", type="string", length=200, nullable=false)
     *
     */
    private $productName;


    /**
     * @ORM\Column(name="product_desc", type="text", nullable=true)
     *
     */
    private $productDesc;


    /**
     * @ORM\Column(name="lang_id", type="integer", length=2)
     */
    private $langId;


    /**
     * @ORM\Column(name="sku", type="string", length=200)
     */
    private $sku;


    /**
     * @ORM\Column(name="price", type="float", nullable=false)
     *
     */
    private $price;


    /**
     * @ORM\Column(name="special_price", type="float", nullable=true)
     *
     */
    private $specialPrice;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;
    public function onPrePersist()
    {
        $this->created = new \DateTime("now");
    }

    /**
     * Triggered on update

     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new \DateTime("now");
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Product
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Product
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set productDesc
     *
     * @param string $productDesc
     *
     * @return Product
     */
    public function setProductDesc($productDesc)
    {
        $this->productDesc = $productDesc;

        return $this;
    }

    /**
     * Get productDesc
     *
     * @return string
     */
    public function getProductDesc()
    {
        return $this->productDesc;
    }

    /**
     * Set price
     *
     * @param \double $price
     *
     * @return Product
     */
    public function setPrice(\double $price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return \double
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set specialPrice
     *
     * @param \double $specialPrice
     *
     * @return Product
     */
    public function setSpecialPrice(\double $specialPrice)
    {
        $this->specialPrice = $specialPrice;

        return $this;
    }

    /**
     * Get specialPrice
     *
     * @return \double
     */
    public function getSpecialPrice()
    {
        return $this->specialPrice;
    }

    /**
     * Set productName
     *
     * @param string $productName
     *
     * @return Product
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * Get productName
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * Set langId
     *
     * @param integer $langId
     *
     * @return Product
     */
    public function setLangId($langId)
    {
        $this->langId = $langId;

        return $this;
    }

    /**
     * Get langId
     *
     * @return integer
     */
    public function getLangId()
    {
        return $this->langId;
    }

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return Product
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }
}
