<?php

namespace SafoorSafdar\AuthorizeNet;

#Bridge
use SafoorSafdar\AuthorizeNet\Bridge\AuthorizeNetAuthentication;

class AuthorizeNet
{
    private $name;
    private $transactionKey;
    private $sandbox;
    private $auth;

    public function __construct($name, $transaction, $sandbox)
    {
        $this->setName($name);
        $this->setTransactionKey($transaction);
        $this->setSandbox($sandbox);
        $this->prepareAuth();

    }
    public function getEnvironment(){
        return ($this->sandbox)?\net\authorize\api\constants\ANetEnvironment::SANDBOX:\net\authorize\api\constants\ANetEnvironment::PRODUCTION;
    }
    protected function prepareAuth(){
        $authorizeNetAuth = new AuthorizeNetAuthentication();
        $authorizeNetAuth->setName($this->name);
        $authorizeNetAuth->setTransactionKey($this->transactionKey);
        $this->setAuth($authorizeNetAuth);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getTransactionKey()
    {
        return $this->transactionKey;
    }

    /**
     * @param mixed $transactionKey
     */
    public function setTransactionKey($transactionKey)
    {
        $this->transactionKey = $transactionKey;
    }

    /**
     * @return mixed
     */
    public function getSandbox()
    {
        return $this->sandbox;
    }

    /**
     * @param mixed $sandbox
     */
    public function setSandbox($sandbox)
    {
        $this->sandbox = $sandbox;
    }

    /**
     * @return mixed
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param mixed $auth
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;
    }
}