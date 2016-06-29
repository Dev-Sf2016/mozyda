<?php
namespace AppBundle\Entity;



use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="company")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repositories\CompanyRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Company
{

    const NUM_ITEMS = 10;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=200, unique=true)
     *
     * @Assert\NotBlank(message="This field is required")
     */
    private $name;

    /**
     * @ORM\Column(name="is_active", type="integer", length=1)
     */
    private $isActive;


    /**
     * @ORM\Column(name="url", type="string", length=150)
     * @Assert\NotBlank(message="This field is required")
     * @Assert\Url()
     */
    private $url;

    /**
     * @ORM\Column(name="logo", type="string", length=64)
     * @Assert\NotBlank(message="Please select logo")
     * @Assert\Image(
     *     maxSize="1Mi",
     *     maxSizeMessage="Maximum Image size allowed is 1Mi"
     * )
     */
    private $logo;

    /**
     * @var ArrayCollection
     *
     * @Assert\Valid()
     */
    private $companyDelegate;

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

    /**
     * Triggered on insert

     * @ORM\PrePersist
     */
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
     * Set name
     *
     * @param string $name
     *
     * @return Company
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isActive
     *
     * @param integer $isActive
     *
     * @return Company
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return integer
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Company
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set logo
     *
     * @param string $logo
     *
     * @return Company
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Add CompanyDelegate
     * @param CompanyDelegate $companyDelegate
     *
     * @return Company
     */
    public function addCompanyDelegate($companyDelegate)
    {
        $this->companyDelegate->add($companyDelegate);

        return $this;
    }

    /**
     * @return CompanyDelegate
     */
    public function getCompanyDelegate()
    {
        return $this->companyDelegate;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Company
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
     * @return Company
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

    public function __construct()
    {
        $this->companyDelegate = new ArrayCollection();
    }
}
