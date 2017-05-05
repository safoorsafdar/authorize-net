<?php


namespace SafoorSafdar\AuthorizeNet\Action\Cim;


use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use SafoorSafdar\AuthorizeNet\AuthorizeNet;
use Payum\Core\Bridge\Spl\ArrayObject;
use SafoorSafdar\AuthorizeNet\Request\Cim\GetCustomerPaymentProfile;
#Exception
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;

class GetCustomerPaymentProfileAction extends GatewayAwareAction implements
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

        //request requires customerProfileId and customerPaymentProfileId
        $request = new AnetAPI\GetCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($this->api->getAuth());
        $request->setRefId('ref'.time());
        $request->setCustomerProfileId($model->get('customerProfileId'));
        $request->setCustomerPaymentProfileId($model->get('customerPaymentProfileId'));
        $request->setUnmaskExpirationDate($model->get('unmaskExpirationDate'));

        $controller
            = new AnetController\GetCustomerPaymentProfileController($request);
        $response
            = $controller->executeWithApiResponse($this->api->getEnvironment());

        if (($response != null)
            && ($response->getMessages()->getResultCode() == "Ok")
        ) {
            //dd($this->toArray($response->getPaymentProfile()));
            $model['paymentProfile']
                = $this->toArray($response->getPaymentProfile());
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
        return $request instanceof GetCustomerPaymentProfile
        && $request->getModel() instanceof \ArrayAccess;
    }

    function toArray($obj)
    {
        if (is_object($obj)) {
            $obj = (array)$obj;
        }
        if (is_array($obj)) {
            $new = array();
            foreach ($obj as $key => $val) {
                $k       = preg_match('/^\x00(?:.*?)\x00(.+)/', $key, $matches)
                    ? $matches[1] : $key;
                $new[$k] = $this->toArray($val);
            }
        } else {
            $new = $obj;
        }

        return $new;
    }

}