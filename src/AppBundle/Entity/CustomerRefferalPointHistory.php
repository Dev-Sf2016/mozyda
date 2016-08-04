<?php
namespace AppBundle\Entity;



use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="customer_refferal_points_history")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repositories\CustomerRefferalPointHistoryRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("id")
 */
class CustomerRefferalPointHistory
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="customerRefferalPointsHistory")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;


    /**
     * @ORM\Column(name="points_added", type="float", length=200)
     */
    private $pointsAdded;

    /**
     * @ORM\Column(name="points_reason", type="float", length=200)
     */
    private $pointsReason;
    
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return CustomerRefferalPointHistory
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
     * @return CustomerRefferalPointHistory
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
     * Set customer
     *
     * @param \AppBundle\Entity\Customer $customer
     *
     * @return CustomerRefferalPointHistory
     */
    public function setCustomer(\AppBundle\Entity\Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \AppBundle\Entity\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set pointsAdded
     *
     * @param double $pointsAdded
     *
     * @return CustomerRefferalPointHistory
     */
    public function setPointsAdded($pointsAdded)
    {
        $this->pointsAdded = $pointsAdded;

        return $this;
    }

    /**
     * Get pointsAdded
     *
     * @return \double
     */
    public function getPointsAdded()
    {
        return $this->pointsAdded;
    }

    /**
     * Set pointsReason
     *
     * @param \double $pointsReason
     *
     * @return CustomerRefferalPointHistory
     */
    public function setPointsReason($pointsReason)
    {
        $this->pointsReason = $pointsReason;

        return $this;
    }

    /**
     * Get pointsReason
     *
     * @return \double
     */
    public function getPointsReason()
    {
        return $this->pointsReason;
    }
}
