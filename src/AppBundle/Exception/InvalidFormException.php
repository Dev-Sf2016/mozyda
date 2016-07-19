<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 7/2/16
 * Time: 4:24 AM
 */

namespace AppBundle\Exception;

/**
 * Class InvalidFormException
 * @package AppBundle\Exception
 */
class InvalidFormException extends \RuntimeException
{

    protected $form;

    public function __construct($message, $form=null)
    {
        parent::__construct($message);
        $this->form = $form;
    }

    /**
     * @return array|null
     */
    public function getForm()
    {
        return $this->form;
    }

}