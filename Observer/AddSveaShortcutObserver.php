<?php
namespace Svea\Checkout\Observer;


use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddSveaShortcutObserver implements ObserverInterface
{

    /**
     * @var \Svea\Checkout\Helper\Data
     */
    protected $helper;

    public function __construct(\Svea\Checkout\Helper\Data $helper)
    {
        $this->helper = $helper;
    }


    public function execute(EventObserver $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }

        // since we will replace the checkout URL, we don't need to add an extra button
        if ($this->helper->replaceCheckout()) {
            return $this;
        }

        //do nothing
        if ($observer->getEvent()->getIsCatalogProduct()) {
            return $this;
        }


        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();



        $params = [];
        $params['checkoutSession'] = $observer->getEvent()->getCheckoutSession();


        // we believe it's \Magento\Framework\View\Element\Template
        $shortcut = $shortcutButtons->getLayout()->createBlock(
            'Svea\Checkout\Block\Checkout\Shortcut',
            '',
            $params
        );
        $shortcut->setIsInCatalogProduct(
            $observer->getEvent()->getIsCatalogProduct()
        )->setShowOrPosition(
            $observer->getEvent()->getOrPosition()
        );
      //  $shortcutButtons->addShortcut($shortcut);

    }

}