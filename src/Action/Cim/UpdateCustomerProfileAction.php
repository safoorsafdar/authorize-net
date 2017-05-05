<?php


namespace SafoorSafdar\AuthorizeNet\Action\Cim;


use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use SafoorSafdar\AuthorizeNet\AuthorizeNet;

#Exception
use Payum\Core\Exception\UnsupportedApiException;

class UpdateCustomerProfileAction extends GatewayAwareAction implements
    ApiAwareInterface
{
    /**
     * @var AuthorizeNet
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof AuthorizeNet) {
            throw new UnsupportedApiException('Not supported.');
        }
        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function execute($request)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {

    }

}