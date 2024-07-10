<?php

declare(strict_types=1);

namespace Catch\TradeEngineering\Tests\Unit\Order\Impl;

use Catch\TradeEngineering\Order\Impl\OrderServiceImpl;
use Catch\TradeEngineering\Order\OrderServiceInterface;
use Catch\TradeEngineering\Tests\BaseTestCase;
use Catch\TradeEngineering\Tests\Util\TestCartBuilder;
use Catch\TradeEngineering\Order\Model\Category;
use Catch\TradeEngineering\Order\Model\Customer;
use Catch\TradeEngineering\Order\Model\Membership;
use Catch\TradeEngineering\Order\Model\Product;
use Catch\TradeEngineering\Order\Model\ProductCategory;

final class OrderServiceImplTest extends BaseTestCase
{
    private OrderServiceInterface $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderServiceImpl();
    }

    public function testOrderServiceCanBeCreated(): void
    {
        $this->assertInstanceOf(OrderServiceImpl::class, $this->orderService);
    }

    public function testOrderCanCheckoutWithCart(): void
    {
        $testCart = TestCartBuilder::begin()
            ->withRandomCustomer()
            ->addRandomProduct()
            ->addRandomProduct()
            ->build();

        $order = $this->orderService->checkout($testCart);
        $this->assertSuccessfulCheckout($order);
    }

    public function testCustomerMembershipHasBeenUpgraded(): void
    {
        $customer = new Customer('Test Customer', 'test@test.com', '123 Test St', new Membership(Membership::MEMBERSHIP_TYPE_ONEPASS));
        $product = new Product(1, "Membership Upgrade", new Category("Category Type", Category::CATEGORY_TYPE_DIGITAL),new ProductCategory("Product Category",ProductCategory::CATEGORY_PRODUCT_ESSENTIALS), 10.00,10);
        $testCart = TestCartBuilder::begin()
            ->forCustomer($customer)
            ->addProduct($product)
            ->build();

        $order = $this->orderService->checkout($testCart);
        $this->assertSuccessfulCheckout($order);
        //$this->assertEquals(Membership::MEMBERSHIP_TYPE_ONEPASS, $order->getCustomer()->getMembership()->getType());
        $this->assertEquals(Category::CATEGORY_TYPE_DIGITAL,$product->getCategory()->getType());
        $this->assertStringStartsWith("Download Link:www.mockdigitalcard.com/".md5($product->getName()), $testCart->generateDownloadLink($product));
        $this->assertEquals(ProductCategory::CATEGORY_PRODUCT_ESSENTIALS,$product->getProductCategory()->getCategory());
        $this->assertEquals(10, $testCart->productCategoryCalculateTax($product->getProductCategory()->getCategory(),$product));
        $this->assertEquals(1, $testCart->memberShipDiscount(Membership::MEMBERSHIP_TYPE_ONEPASS,$product));
        $this->assertEquals(10,$testCart->totalBeforeTax($product));


    }
 
}
