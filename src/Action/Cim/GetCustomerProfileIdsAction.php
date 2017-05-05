<?php


namespace SafoorSafdar\AuthorizeNet\Action\Cim;


use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use SafoorSafdar\AuthorizeNet\AuthorizeNet;

#Exception
use Payum\Core\Exception\UnsupportedApiException;

class GetCustomerProfileIdsAction extends GatewayAwareAction implements
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
        $refId = 'ref' . time();

        //Setting the paging
        $paging = new AnetAPI\PagingType();
        $paging->setLimit("1000");
        $paging->setOffset("1");

        //Setting the sorting
        $sorting = new AnetAPI\CustomerPaymentProfileSortingType();
        $sorting->setOrderBy("id");
        $sorting->setOrderDescending("false");

        //Creating the request with the required parameters
        $request = new AnetAPI\GetCustomerPaymentProfileListRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setPaging($paging);
        $request->setSorting($sorting);
        $request->setSearchType("cardsExpiringInMonth");
        $request->setMonth("2020-12");

        // Controller
        $controller = new AnetController\GetCustomerPaymentProfileListController($request);
        // Getting the response
        $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {

    }

}