<?php

namespace Svea\Checkout\Controller\Order;

class SaveCoupon extends \Svea\Checkout\Controller\Order\Update
{

    public function execute()
    {
        if ($this->ajaxRequestAllowed()) {
            return;
        }

        $quote = $this->getSveaCheckout()->getQuote();

        $couponCode    = (string)$this->getRequest()->getParam('coupon_code');
        $oldCouponCode = $quote->getCouponCode();
        $remove        = (int)$this->getRequest()->getParam('remove') > 0;

        if($remove) {
            $couponCode    = '';
        } elseif($couponCode) {

            $codeLength = strlen($couponCode);
            if($codeLength > 255) {
                //invalid
                $couponCode = '';
            }
        }

        if(!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->messageManager->addError(__('Coupon code is not valid (or missing)'));
            $this->_sendResponse('coupon',$updateCheckout = false);
            return;
        }



        try {

            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setCouponCode($couponCode)->collectTotals()->save();

            if($couponCode) {
                if ($couponCode == $quote->getCouponCode()) {
                    $this->messageManager->addSuccess(__('Coupon code "%1" was applied.',$couponCode));
                } else {
                    $this->messageManager->addError(__('Coupon code "%1" is not valid.',$couponCode));
                }
            } else {
                $this->messageManager->addSuccess(__('Coupon code was canceled.',$couponCode));
            }

        }  catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t apply your coupon.')
            );
        }
        $this->_sendResponse(['cart','coupon','messages','shipping','svea']);

    }

}

