<?php


namespace SafoorSafdar\AuthorizeNet\Action\Arb;


use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use SafoorSafdar\AuthorizeNet\AuthorizeNet;
use SafoorSafdar\AuthorizeNet\Request\Arb\UpdateSubscription;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
#Exception
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;

class UpdateSubscriptionAction extends GatewayAwareAction implements
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

        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName($model->get('subscription.name', null));
        $subscription->setAmount($model->get('subscription.amount', null));
        //set profile information
        $profile = new AnetAPI\CustomerProfileIdType();
        $profile->setCustomerProfileId($model->get('subscription.profile.customerProfileId',
            null));
        $profile->setCustomerPaymentProfileId($model->get('subscription.profile.customerPaymentProfileId',
            null));
        $profile->setCustomerAddressId($model->get('subscription.profile.customerAddressId',
            null));
        //set customer profile information
        $subscription->setProfile($profile);

        $request = new AnetAPI\ARBUpdateSubscriptionRequest();
        $request->setMerchantAuthentication($this->api->getAuth());
        $request->setRefId('ref'.time());
        $request->setSubscriptionId($model->get('subscriptionId'));
        $request->setSubscription($subscription);

        $controller
            = new AnetController\ARBUpdateSubscriptionController($request);

        $response
            = $controller->executeWithApiResponse($this->api->getEnvironment());

        $errorMessages     = $response->getMessages()->getMessage();
        $model['messages'] = [
            'resultCode' => $response->getMessages()->getResultCode(),
            'message'    => [
                'code' => $errorMessages[0]->getCode(),
                'text' => $errorMessages[0]->getText(),
            ],
        ];
        $model->replace(get_object_vars($response));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof UpdateSubscription
        && $request->getModel() instanceof \ArrayAccess;
    }

}