<?php

declare(strict_types=1);

namespace Catch\TradeEngineering\Order\Model;

final class Cart
{
    /**
     * @var array<int, CartProduct>
     */
    private array $cartProducts = [];

    public function __construct(private readonly Customer $customer)
    {
    }

    public function addProduct(Product $product): self
    {
        if (array_key_exists($product->getId(), $this->cartProducts)) {
            $this->incrementCartProductQuantity($product);
        } else {
            $this->addNewItemToCart($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if (array_key_exists($product->getId(), $this->cartProducts)) {
            $this->decrementCartProductQuantity($product);
        }

        return $this;
    }

    /**
     * @return CartProduct[]
     */
    public function getCartProducts(): array
    {
        return $this->cartProducts;
    }

    public function incrementCartProductQuantity(Product $product): self
    {
        $existingCartProduct = $this->cartProducts[$product->getId()];
        $existingCartProduct->incrementQuantity();
        return $this;
    }

    public function decrementCartProductQuantity(Product $product): self
    {
        $existingCartProduct = $this->cartProducts[$product->getId()];
        $existingCartProduct->decrementQuantity();

        if ($existingCartProduct->getQuantity() === 0) {
            unset($this->cartProducts[$product->getId()]);
        }

        return $this;
    }

    private function addNewItemToCart(Product $product): void
    {
        $this->cartProducts[$product->getId()] = new CartProduct($product);
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function calculateBaseTotal(): float
    {
        return array_reduce(
            $this->cartProducts,
            static fn(float $carry, CartProduct $cartProduct) =>
                (float) bcadd((string)$carry, (string)$cartProduct->getProduct()->getPrice()),
            0.0
        );
    }

    public function calculateTaxTotal(): float
    {
        return array_reduce(
            $this->cartProducts,
            static fn(float $carry, CartProduct $cartProduct) =>
                (float) bcadd(
                    (string)$carry,
                    (string) bcmul(
                        (string)$cartProduct->getProduct()->getPrice(),
                        (string) bcdiv((string)$cartProduct->getProduct()->getTaxRate(), '100', 2),
                        2
                    ),
                    2
                ),
            0.0
        );
    }

    //Task 1 Customer orders Membership Upgrade
    public function memberShipUpgrade($memberShipLevel,$customer){
        if($memberShipLevel==Membership::MEMBERSHIP_TYPE_BASIC){
            $customer->setMembership(new Membership(Membership::MEMBERSHIP_TYPE_ONEPASS));
            }else if($memberShipLevel==Membership::MEMBERSHIP_TYPE_ONEPASS){
            $customer->setMembership(new Membership(Membership::MEMBERSHIP_TYPE_ONEPASS_PREMIUM));
        }
    }
    //Task 2   customer orders a physical product/Digital.
    public function productType($type,$customer,$product){
        if($type==Category::CATEGORY_TYPE_DIGITAL){
            $this->generateShippingLabel($customer);
        }else if($type==Category::CATEGORY_TYPE_PHYSICAL){
            $this->generateDownloadLink($product);
        }
    }

    public function generateShippingLabel($customer){
        return "Shipping Label:" .$customer->getName().",".$customer->getAddress();
    }
    public function generateDownloadLink($product){
        return "Download Link:"."www.mockdigitalcard.com/".md5($product->getName());
    }

    //Task 3 and 4 Customer orders a product with a category of Essentials/Luxury.
    public function productCategoryCalculateTax($productCategory,$product){
        if($productCategory==ProductCategory::CATEGORY_PRODUCT_ESSENTIALS){
           return $product->getTaxRate();
        }else if($productCategory==ProductCategory::CATEGORY_PRODUCT_LUXURY){
            return $product->getTaxRate()*2;
        }
    }

    public function memberShipDiscount($memberShipLevel,$product){
        if($memberShipLevel == Membership::MEMBERSHIP_TYPE_ONEPASS){
            return $this->applyDiscount($product->getPrice(),10);
        }else if($memberShipLevel == Membership::MEMBERSHIP_TYPE_ONEPASS_PREMIUM){
           return  $this->applyDiscount($product->getPrice(),20);   
        }
    }


    public function totalBeforeTax($product){
        return $product->getPrice();
    }

        //apply Discount
    public function applyDiscount($price,$percentage){
        return ($percentage / 100) * $price;
    }
    
}