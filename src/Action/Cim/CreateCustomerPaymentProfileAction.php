<?php

namespace SafoorSafdar\AuthorizeNet\Action\Cim;

use SafoorSafdar\AuthorizeNet\AuthorizeNet;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
#Exception
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;
#Request
use SafoorSafdar\AuthorizeNet\Request\Cim\CreateCustomerPaymentProfile;

class CreateCustomerPaymentProfileAction extends GatewayAwareAction implements
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

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($model->get('paymentProfiles.payment.creditCard.cardNumber'));
        $creditCard->setExpirationDate($model->get('paymentProfiles.payment.creditCard.expirationDate'));
        $creditCard->setCardCode($model->get('paymentProfiles.payment.creditCard.cardCode'));

        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info for new payment type
        $billTo = new AnetAPI\CustomerAddressType();
        $billTo->setFirstName($model->get('paymentProfiles.billTo.firstName'));
        $billTo->setLastName($model->get('paymentProfiles.billTo.lastName'));
        $billTo->setCompany($model->get('paymentProfiles.billTo.company', ''));
        $billTo->setAddress($model->get('paymentProfiles.billTo.address', ''));
        $billTo->setCity($model->get('paymentProfiles.billTo.city', ''));
        $billTo->setState($model->get('paymentProfiles.billTo.state', ''));
        $billTo->setZip($model->get('paymentProfiles.billTo.zip', ''));
        $billTo->setPhoneNumber($model->get('paymentProfiles.billTo.phoneNumber',
            ''));
        $billTo->setfaxNumber($model->get('paymentProfiles.billTo.faxNumber',
            ''));
        $billTo->setCountry($model->get('paymentProfiles.billTo.country', ''));

        // Create a new Customer Payment Profile
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType($model->get('paymentProfiles.customerType',
            'individual'));
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($paymentCreditCard);

        // Submit a CreateCustomerPaymentProfileRequest to create a new Customer Payment Profile
        $paymentProfileRequest
            = new AnetAPI\CreateCustomerPaymentProfileRequest();
        $paymentProfileRequest->setMerchantAuthentication($this->api->getAuth());
        //Use an existing profile id
        $paymentProfileRequest->setCustomerProfileId($model->get('customerProfileId'));
        $paymentProfileRequest->setPaymentProfile($paymentProfile);

        $controller
            = new AnetController\CreateCustomerPaymentProfileController($paymentProfileRequest);
        $response
            = $controller->executeWithApiResponse($this->api->getEnvironment());
        if (($response != null)
            && ($response->getMessages()->getResultCode() == "Ok")
        ) {
            $model['customerPaymentProfileId']
                = $response->getCustomerPaymentProfileId();
        }
        $errorMessages = $response->getMessages()->getMessage();
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
        return $request instanceof CreateCustomerPaymentProfile
        && $request->getModel() instanceof \ArrayAccess;
    }

}