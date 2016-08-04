<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="customers")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repositories\CustomerRepository")
 * @UniqueEntity("email")
 * @ORM\HasLifecycleCallbacks()
 */
class Customer
{
    const NUM_ITEMS = 10;
    const NUM_ITEMS_FR = 10;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=100)
     * @Assert\NotBlank(message="This field is required")
     */
    private $name;

    /**
     * @ORM\Column(name="is_active", type="integer", length=1)
     */
    private $isActive;

    /**
     * @var string
     * @ORM\Column(name="data", type="string", length=150, nullable=true)
     */
    private $data;
    /**
     * @ORM\Column(name="activation_code", type="string", length=100)
     */
    private $activationCode;


    /**
     * @ORM\Column(name="loyality_id", type="string", length=100, unique=true)
     * @Assert\NotBlank(message="This field is required", groups={"registration"})
     */
    private $loyalityId;

    /**
     * @ORM\Column(name="city", type="string", length=100)
     */
    private $city;

    /**
     * @ORM\Column(name="nationality", type="string", length=100)
     */
    private $nationality;


    /**
     * @ORM\Column(name="email", type="string", length=100, unique=true)
     * @Assert\Email()
     * @Assert\NotBlank(message="This field is required", groups={"registration"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank(message="This field is required", groups={"registration"})
     */
    private $password;

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
     * @ORM\OneToMany(targetEntity="CustomerInvitation", mappedBy="refferer")
     */
    private $customerInvitations;

    /**
     * @ORM\OneToMany(targetEntity="CustomerRefferalPointHistory", mappedBy="customer")
     */
    private $customerRefferalPointsHistory;

    /**
     * @ORM\OneToMany(targetEntity="Customer", mappedBy="reffered_by")
     */
    private $refferer;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="refferer")
     * @ORM\JoinColumn(name="refferer_id", referencedColumnName="id")
     */
    private $reffered_by;

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


    public function __construct()
    {
        $this->customerInvitations = new ArrayCollection();
        $this->customerRefferalPointsHistory = new ArrayCollection();
        $this->refferer = new ArrayCollection();
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
     * @return Customer
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
     * @return Customer
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
     * Set activationCode
     *
     * @param string $activationCode
     *
     * @return Customer
     */
    public function setActivationCode($activationCode)
    {
        $this->activationCode = $activationCode;

        return $this;
    }

    /**
     * Get activationCode
     *
     * @return string
     */
    public function getActivationCode()
    {
        return $this->activationCode;
    }

    /**
     * Set loyalityId
     *
     * @param string $loyalityId
     *
     * @return Customer
     */
    public function setLoyalityId($loyalityId)
    {
        $this->loyalityId = $loyalityId;

        return $this;
    }

    /**
     * Get loyalityId
     *
     * @return string
     */
    public function getLoyalityId()
    {
        return $this->loyalityId;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Customer
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set nationality
     *
     * @param string $nationality
     *
     * @return Customer
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * Get nationality
     *
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Customer
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
     * Set password
     *
     * @param string $password
     *
     * @return Customer
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Customer
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
     * @return Customer
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
     * Add customerInvitation
     *
     * @param \AppBundle\Entity\CustomerInvitation $customerInvitation
     *
     * @return Customer
     */
    public function addCustomerInvitation(\AppBundle\Entity\CustomerInvitation $customerInvitation)
    {
        $this->customerInvitations[] = $customerInvitation;

        return $this;
    }

    /**
     * Remove customerInvitation
     *
     * @param \AppBundle\Entity\CustomerInvitation $customerInvitation
     */
    public function removeCustomerInvitation(\AppBundle\Entity\CustomerInvitation $customerInvitation)
    {
        $this->customerInvitations->removeElement($customerInvitation);
    }

    /**
     * Get customerInvitations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomerInvitations()
    {
        return $this->customerInvitations;
    }

    /**
     * Add customerRefferalPointsHistory
     *
     * @param \AppBundle\Entity\CustomerRefferalPointHistory $customerRefferalPointsHistory
     *
     * @return Customer
     */
    public function addCustomerRefferalPointsHistory(\AppBundle\Entity\CustomerRefferalPointHistory $customerRefferalPointsHistory)
    {
        $this->customerRefferalPointsHistory[] = $customerRefferalPointsHistory;

        return $this;
    }

    /**
     * Remove customerRefferalPointsHistory
     *
     * @param \AppBundle\Entity\CustomerRefferalPointHistory $customerRefferalPointsHistory
     */
    public function removeCustomerRefferalPointsHistory(\AppBundle\Entity\CustomerRefferalPointHistory $customerRefferalPointsHistory)
    {
        $this->customerRefferalPointsHistory->removeElement($customerRefferalPointsHistory);
    }

    /**
     * Get customerRefferalPointsHistory
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomerRefferalPointsHistory()
    {
        return $this->customerRefferalPointsHistory;
    }

    /**
     * Add refferer
     *
     * @param \AppBundle\Entity\Customer $refferer
     *
     * @return Customer
     */
    public function addRefferer(\AppBundle\Entity\Customer $refferer)
    {
        $this->refferer[] = $refferer;

        return $this;
    }

    /**
     * Remove refferer
     *
     * @param \AppBundle\Entity\Customer $refferer
     */
    public function removeRefferer(\AppBundle\Entity\Customer $refferer)
    {
        $this->refferer->removeElement($refferer);
    }

    /**
     * Get refferer
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRefferer()
    {
        return $this->refferer;
    }

    /**
     * Set refferedBy
     *
     * @param \AppBundle\Entity\Customer $refferedBy
     *
     * @return Customer
     */
    public function setRefferedBy(\AppBundle\Entity\Customer $refferedBy = null)
    {
        $this->reffered_by = $refferedBy;

        return $this;
    }

    /**
     * Get refferedBy
     *
     * @return \AppBundle\Entity\Customer
     */
    public function getRefferedBy()
    {
        return $this->reffered_by;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return Customer
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}
