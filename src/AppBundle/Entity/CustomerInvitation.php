<?php
namespace AppBundle\Entity;



use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="customer_invitation")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repositories\CustomerInvitationRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("id")
 */
class CustomerInvitation
{
    const SEND_INVITATION = 'invited';
    const USER_REGISTERED = 'registered';
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="customerInvitation")
     * @ORM\JoinColumn(name="refferer_id", referencedColumnName="id")
     */
    private $customer;
    /**
     * @ORM\Column(name="reffered_email", type="string", length=100)
     * @Assert\Email()
     * @Assert\NotBlank(message="This field is required")
     */
    private $email;
    /**
     * @ORM\Column(name="status", type="string", columnDefinition="enum('invited','registered')")
     */
    private $status;
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
     * Set customer
     *
     * @param \AppBundle\Entity\Customer $customer
     *
     * @return CustomerRefferal
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
     * Set email
     *
     * @param string $email
     *
     * @return CustomerRefferal
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return CustomerRefferal
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return CustomerRefferal
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
     * @return CustomerRefferal
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
     * Set refferer
     *
     * @param \AppBundle\Entity\Customer $refferer
     *
     * @return CustomerInvitationr
     */
    public function setRefferer(\AppBundle\Entity\Customer $refferer = null)
    {
        $this->refferer = $refferer;

        return $this;
    }

    /**
     * Get refferer
     *
     * @return \AppBundle\Entity\Customer
     */
    public function getRefferer()
    {
        return $this->refferer;
    }
}
