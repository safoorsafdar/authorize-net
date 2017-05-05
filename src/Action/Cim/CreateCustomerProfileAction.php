<?php
namespace SafoorSafdar\AuthorizeNet\Action\Cim;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;

use SafoorSafdar\AuthorizeNet\AuthorizeNet;
use SafoorSafdar\AuthorizeNet\Request\Cim\CreateCustomerProfile;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class CreateCustomerProfileAction extends GatewayAwareAction implements
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
     * @param CreateCustomerProfile $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        // Create the payment data for a credit card
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($model->get('paymentProfiles.payment.creditCard.cardNumber'));
        $creditCard->setExpirationDate($model->get('paymentProfiles.payment.creditCard.expirationDate'));
        $creditCard->setCardCode($model->get('paymentProfiles.payment.creditCard.cardCode'));
        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info
        $billTo = new AnetAPI\CustomerAddressType();
        $billTo->setFirstName($model->get('paymentProfiles.billTo.firstName'));
        $billTo->setLastName($model->get('paymentProfiles.billTo.lastName'));
        /*$billTo->setCompany("Souveniropolis");
        $billTo->setAddress("14 Main Street");
        $billTo->setCity("Pecan Springs");
        $billTo->setState("TX");
        $billTo->setZip("44628");
        $billTo->setCountry("USA");*/

        // Create a Customer Profile Request
        //  1. create a Payment Profile
        //  2. create a Customer Profile
        //  3. Submit a CreateCustomerProfile Request
        //  4. Validate Profile ID returned
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($paymentCreditCard);
        $paymentProfiles[] = $paymentProfile;

        $customerProfile = new AnetAPI\CustomerProfileType();
        //$customerProfile->setDescription("Customer 2 Test PHP 321");
        $customerProfile->setMerchantCustomerId($model->get('profile.merchantCustomerId'));
        $customerProfile->setEmail($model->get('profile.email'));
        $customerProfile->setPaymentProfiles($paymentProfiles);

        $apiRequest = new AnetAPI\CreateCustomerProfileRequest();
        $apiRequest->setMerchantAuthentication($this->api->getAuth());
        $apiRequest->setRefId('ref'.time());
        $apiRequest->setProfile($customerProfile);
        $execution
            = new AnetController\CreateCustomerProfileController($apiRequest);
        //sandbox vs live
        $response
            = $execution->executeWithApiResponse($this->api->getEnvironment());
        if (($response != null)
            && ($response->getMessages()->getResultCode() == "Ok")
        ) {
            $model['customerProfileId']        = $response->getCustomerProfileId();
            $paymentProfiles
                                               = $response->getCustomerPaymentProfileIdList();
            $model['customerPaymentProfileId'] = $paymentProfiles[0];
        }
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
        return $request instanceof CreateCustomerProfile
        && $request->getModel() instanceof \ArrayAccess;
    }

}