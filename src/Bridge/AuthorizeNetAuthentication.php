<?php
namespace SafoorSafdar\AuthorizeNet\Bridge;

// this is a fix of crappy auto loading in authorize.net lib.
class_exists('AuthorizeNetException', true);

use net\authorize\api\contract\v1 as AnetAPI;

class AuthorizeNetAuthentication extends AnetAPI\MerchantAuthenticationType
{
    //protected $_sandbox;

    /**
     * @return mixed
     */
    /*public function getSandbox()
    {
        return $this->_sandbox;
    }*/

    /**
     * @param mixed $sandbox
     */
    /*public function setSandbox($sandbox)
    {
        $this->_sandbox = $sandbox;
    }*/
}
