<?php
namespace SafoorSafdar\AuthorizeNet;

#Actions
#Bridge
//use SafoorSafdar\AuthorizeNet\Bridge\AuthorizeNetAuthentication;
use SafoorSafdar\AuthorizeNet\AuthorizeNet;
#Core
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
#Actions
use SafoorSafdar\AuthorizeNet\Action\Cim\CreateCustomerProfileAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\UpdateSplitTenderGroupAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\UpdateCustomerShippingAddressAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\GetCustomerPaymentProfileAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\UpdateCustomerPaymentProfileAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\CreateCustomerShippingAddressAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\GetCustomerProfileIdsAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\UpdateCustomerProfileAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\CreateCustomerProfileTransactionAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\GetHostedProfilePageRequestAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\ValidateCustomerPaymentProfileAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\DeleteCustomerPaymentProfileAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\CreateCustomerPaymentProfileAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\DeleteCustomerProfileAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\DeleteCustomerShippingAddressAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\GetCustomerProfileAction;
use SafoorSafdar\AuthorizeNet\Action\Cim\GetCustomerShippingAddressAction;

use SafoorSafdar\AuthorizeNet\Action\Arb\CancelSubscriptionAction;
use SafoorSafdar\AuthorizeNet\Action\Arb\GetSubscriptionStatusAction;
use SafoorSafdar\AuthorizeNet\Action\Arb\CreateSubscriptionCustomerProfileAction;
use SafoorSafdar\AuthorizeNet\Action\Arb\UpdateSubscriptionAction;
use SafoorSafdar\AuthorizeNet\Action\Arb\CreateSubscriptionAction;
use SafoorSafdar\AuthorizeNet\Action\Arb\GetSubscriptionListAction;

class AuthorizeNetGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if ( ! class_exists(\net\authorize\api\contract\v1\MerchantAuthenticationType::class)) {
            throw new \LogicException('You must install "authorizenet/authorizenet" library.');
        }

        $config->defaults(array(
            'payum.factory_name'                                => 'authorize_net',
            'payum.factory_title'                               => 'Authorize.NET',
            //need to organize according to Authorize.NET CIM
            'payum.action.create_customer_profile'              => new CreateCustomerProfileAction(),
            'payum.action.update_split_tender_group'            => new UpdateSplitTenderGroupAction(),
            'payum.action.update_customer_shipping_address'     => new UpdateCustomerShippingAddressAction(),
            'payum.action.get_customer_payment_profile'         => new GetCustomerPaymentProfileAction(),
            'payum.action.update_customer_payment_profile'      => new UpdateCustomerPaymentProfileAction(),
            'payum.action.create_customer_shipping_address'     => new CreateCustomerShippingAddressAction(),
            'payum.action.get_customer_profile_ids'             => new GetCustomerProfileIdsAction(),
            'payum.action.update_customer_profile'              => new UpdateCustomerProfileAction(),
            'payum.action.create_customer_profile_transaction'  => new CreateCustomerProfileTransactionAction(),
            'payum.action.get_hosted_profile_page_request'      => new GetHostedProfilePageRequestAction(),
            'payum.action.validate_customer_payment_profile'    => new ValidateCustomerPaymentProfileAction(),
            'payum.action.delete_customer_payment_profile'      => new DeleteCustomerPaymentProfileAction(),
            'payum.action.create_customer_payment_profile'      => new CreateCustomerPaymentProfileAction(),
            'payum.action.delete_customer_profile'              => new DeleteCustomerProfileAction(),
            'payum.action.delete_customer_shipping_address'     => new DeleteCustomerShippingAddressAction(),
            'payum.action.get_customer_profile'                 => new GetCustomerProfileAction(),
            'payum.action.get_customer_shipping_address'        => new GetCustomerShippingAddressAction(),
            //
            'payum.action.cancel_subscription'                  => new CancelSubscriptionAction(),
            'payum.action.get_subscription_status'              => new GetSubscriptionStatusAction(),
            'payum.action.create_subscription_customer_profile' => new CreateSubscriptionCustomerProfileAction(),
            'payum.action.update_subscription'                  => new UpdateSubscriptionAction(),
            'payum.action.create_subscription'                  => new CreateSubscriptionAction(),
            'payum.action.get_subscription_list'                => new GetSubscriptionListAction(),


        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'login_id'        => '',
                'transaction_key' => '',
                'sandbox'         => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array(
                'login_id',
                'transaction_key',
            );

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);
                //$api = new AuthorizeNetAuthentication();
                //$api->setName($config['login_id']);
                //$api->setTransactionKey($config['transaction_key']);
                //$api->setSandbox($config['sandbox']);
                //return $api;
                return new AuthorizeNet($config['login_id'],
                    $config['transaction_key'], $config['sandbox']);
            };
        }
    }
}
