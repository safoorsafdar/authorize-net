<?php
namespace SafoorSafdar\AuthorizeNet\Action\Arb;

use SafoorSafdar\AuthorizeNet\AuthorizeNet;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
#Exception
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use SafoorSafdar\AuthorizeNet\Request\Arb\CancelSubscription;

class CancelSubscriptionAction extends GatewayAwareAction implements
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
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $refId          = 'ref'.time();
        $subscriptionId = $model->get('subscriptionId');
        $request        = new AnetAPI\ARBCancelSubscriptionRequest();
        $request->setMerchantAuthentication($this->api->getAuth());
        $request->setRefId($refId);
        $request->setSubscriptionId($subscriptionId);
        $controller
            = new AnetController\ARBCancelSubscriptionController($request);
        $response
            = $controller->executeWithApiResponse($this->api->getEnvironment());

        if (($response != null)
            && ($response->getMessages()->getResultCode() == "Ok")
        ) {
            $errorMessages = $response->getMessages()->getMessage();
            $model['messages'] = [
                'resultCode' => $response->getMessages()->getResultCode(),
                'message' => [
                    'code' => $errorMessages[0]->getCode(),
                    'text' => $errorMessages[0]->getText(),
                ]
            ];
        }
        $errorMessages = $response->getMessages()->getMessage();
        $model['messages'] = [
            'resultCode' => $response->getMessages()->getResultCode(),
            'message' => [
                'code' => $errorMessages[0]->getCode(),
                'text' => $errorMessages[0]->getText(),
            ]
        ];
        $model->replace(get_object_vars($response));

    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof CancelSubscription
        && $request->getModel() instanceof \ArrayAccess;
    }

}