<?php

declare(strict_types=1);

namespace Catch\TradeEngineering\Order\Impl;

use Catch\TradeEngineering\Order\Exception\CartCannotCheckoutException;
use Catch\TradeEngineering\Order\Model\Cart;
use Catch\TradeEngineering\Order\Model\ConfirmedOrder;
use Catch\TradeEngineering\Order\Model\Membership;
use Catch\TradeEngineering\Order\Model\Payment;
use Catch\TradeEngineering\Order\Model\ProductCategory;
use Catch\TradeEngineering\Order\Model\Category;
use Catch\TradeEngineering\Order\OrderServiceInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;

final class OrderServiceImpl implements OrderServiceInterface
{

    /**
     * @throws CartCannotCheckoutException
     * @throws RuntimeException
     */
    public function checkout(Cart $cart): ConfirmedOrder
    {
        if (!$this->canCartCheckout($cart)) {
            throw new CartCannotCheckoutException($cart);
        }

        $payment = $this->collectPayment($cart);
        $order = new ConfirmedOrder();
        $order->setCustomer($cart->getCustomer())
            ->setItems($cart->getCartProducts())
            ->setPayment($payment);

        $this->applyBusinessRules($cart, $order);

        return $order;
    }

    private function collectPayment(Cart $cart): Payment
    {
        $baseAmount = $cart->calculateBaseTotal();
        $taxTotal = $cart->calculateTaxTotal();
        return new Payment(
            Uuid::uuid4()->toString(),
            Payment::PAYMENT_METHOD_CREDIT_CARD,
            Payment::PAYMENT_STATUS_CONFIRMED,
            (float) bcadd((string)$baseAmount, (string)$taxTotal, 2)
        );
    }

    private function canCartCheckout(Cart $cart): bool
    {
        return $cart->getCartProducts() !== [];
    }

    private function applyBusinessRules(Cart $cart, ConfirmedOrder $confirmedOrder): void
    {
        //Customer orders a product based on MembershipLevel
            foreach($cart->getCartProducts() as $cartProduct){
                if($cartProduct->getProduct()->getName() == "Membership Upgrade"){
                    $cart->memberShipUpgrade($confirmedOrder->getCustomer()->getMembership()->getType(),$confirmedOrder->getCustomer());
                }
                //Task 2
               $cart->productType($cartProduct->getProduct()->getCategory(),$confirmedOrder->getCustomer(),$cartProduct->getProduct());
                // Task 3 and 4
                $cart->productCategoryCalculateTax($cartProduct->getProduct()->getCategory(),$cartProduct->getProduct());
                  //Task 5 and 6  customer is a OnePass member, they should receive a 10% discount on their order.
                    //If the customer is a OnePass Premium member, they should receive a 20% discount on their order.
                        
                $cart->memberShipDiscount($confirmedOrder->getCustomer()->getMembership()->getType(),$cartProduct->getProduct());
                //Task 7 customer order more than $100 worth of products before tax, they should receive a 10% discount on their order. 
                 if($cart->totalBeforeTax($cartProduct->getProduct())>100){
                    $cart->applyDiscount($cartProduct->getProduct()->getPrice(),10);
                 }
                }
    }

}