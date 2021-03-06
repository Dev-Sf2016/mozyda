<?php
namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="discount")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repositories\DiscountRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Discount
{

    const NUM_ITEMS = 10;
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=500)
     *
     * @Assert\NotBlank(message="This field is required")
     */
    private $title;

    /**
     * @ORM\Column(name="start_date", type="date")
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $startDate;


    /**
     * @ORM\Column(name="end_date", type="date")
     * @Assert\NotBlank(message="This field is required")
     * @Assert\Date()
     * @Assert\Expression(expression="this.getStartDate()<=this.getEndDate()", message="Discount start date is greater then end date")
     */
    private $endDate;

    /**
     * @var string
     * @ORM\Column(name="promotion", type="string", length=100, nullable=true)
     * @Assert\NotBlank(message="This field is required", groups={"addnewonly"})
     * @Assert\Image(maxSize="1Mi")
     */
    private $promotion;
    /**
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

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
     * Set title
     *
     * @param string $title
     *
     * @return Discount
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set startDate
     *
     * @param \Date $starDate
     *
     * @return Discount
     */
    public function setStartDate($starDate)
    {
        $this->startDate = $starDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \Date
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \Date $endDate
     *
     * @return Discount
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \Date
     */
    public function getEndDate()
    {
        return $this->endDate;
    }



    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Discount
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
     * @return Discount
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
     * @param $promotion
     *
     * @return Discount
     */
    public function setPromotion($promotion)
    {
        $this->promotion = $promotion;

        return $this;
    }

    /**
     * @return string
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * Set company
     * @param $company
     * @return Discount
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

}
