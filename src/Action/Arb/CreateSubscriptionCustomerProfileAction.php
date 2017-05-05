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
use SafoorSafdar\AuthorizeNet\Request\Arb\CreateSubscriptionCustomerProfile;

class CreateSubscriptionCustomerProfileAction extends GatewayAwareAction implements
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

        // Subscription Type Info
        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName($model->get('subscription.name',''));

        $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
        $interval->setLength($model->get('subscription.paymentSchedule.interval.length'));
        $interval->setUnit($model->get('subscription.paymentSchedule.interval.unit'));

        $paymentSchedule = new AnetAPI\PaymentScheduleType();
        $paymentSchedule->setInterval($interval);
        $paymentSchedule->setStartDate(new \DateTime($model->get('subscription.paymentSchedule.startDate')));
        $paymentSchedule->setTotalOccurrences($model->get('subscription.paymentSchedule.totalOccurrences'));
        $paymentSchedule->setTrialOccurrences($model->get('subscription.paymentSchedule.trialOccurrences',0));
        $subscription->setPaymentSchedule($paymentSchedule);

        $subscription->setAmount($model->get('subscription.amount'));
        $subscription->setTrialAmount($model->get('subscription.trialAmount',0.0));

        $profile = new AnetAPI\CustomerProfileIdType();
        $profile->setCustomerProfileId($model->get('subscription.profile.customerProfileId'));
        $profile->setCustomerPaymentProfileId($model->get('subscription.profile.customerPaymentProfileId'));
        //$profile->setCustomerAddressId($customerAddressId);

        $subscription->setProfile($profile);
        $request = new AnetAPI\ARBCreateSubscriptionRequest();
        $request->setmerchantAuthentication($this->api->getAuth());
        $request->setRefId('ref'.time());
        $request->setSubscription($subscription);
        $controller = new AnetController\ARBCreateSubscriptionController($request);
        $response = $controller->executeWithApiResponse( $this->api->getEnvironment());

        if (($response != null)
            && ($response->getMessages()->getResultCode() == "Ok")
        ) {
            $model['subscriptionId']        = $response->getSubscriptionId();
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
        return $request instanceof CreateSubscriptionCustomerProfile
        && $request->getModel() instanceof \ArrayAccess;
    }

}