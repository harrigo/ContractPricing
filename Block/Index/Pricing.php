<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Harrigo\ContractPricing\Block\Index;

use Magento\Customer\Model\Context as CustomerContext;

/**
 * New products block
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Pricing extends \Magento\Catalog\Block\Product\AbstractProduct implements
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Default value for products count that will be shown
     */
    const DEFAULT_PRODUCTS_COUNT = 10;

    /**
     * Products count
     *
     * @var int
     */
    protected $_productsCount;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;
	
	protected $_customerSession;
    /**
     * @param Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
	 
	protected $_catalogProductTypeConfigurable;
	 
	protected $productRepository; 
	
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Catalog\Block\Product\ListProduct $listProductBlock,
		\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
		\Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->httpContext = $httpContext;
		$this->_customerSession = $customerSession;
		$this->listProductBlock = $listProductBlock;
		$this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
		$this->_productloader = $_productloader;
        parent::__construct(
            $context,
            $data
        );
    }

	public function getProductUrlCustom($_product) {
		$_productId = $_product->getId();
		$_parentid = $this->_catalogProductTypeConfigurable->getParentIdsByChild($_productId);			
		$_parent = $this->_productloader->create()->load($_parentid);
		if($_parentid) {
			return $_parent->getProductUrl();
		} else {
			return $_product->getProductUrl();
		}
		
	}

	public function getAddToCartPostParams($product)
	{
		return $this->listProductBlock->getAddToCartPostParams($product);
	}
	
    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
           'CATALOG_PRODUCT_SPECIAL',
           $this->_storeManager->getStore()->getId(),
           $this->_design->getDesignTheme()->getId(),
           $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP),
           'template' => $this->getTemplate(),
           $this->getProductsCount()
        ];
    }

    /**
     * Prepare and return product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
	 
	public function getGroupId(){
		if($this->_customerSession->isLoggedIn()):
			return $customerGroup=$this->_customerSession->getCustomer()->getGroupId();
		else:
			return "No Group";
		endif;
		
	}
	 public function getGroupName(){
		if($this->_customerSession->isLoggedIn()):
		    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$groups = $objectManager->get('\Magento\Customer\Model\ResourceModel\Group\Collection');
			$groups->addFilter("customer_group_id", $this->getGroupId());
			$group = $groups->getFirstItem();
			return $groupName = $group->getData("customer_group_code");
		else:
			return "No Group";
		endif;
		
	}
	 
    protected function _getProductCollection()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->setPageSize(
            $this->getProductsCount()
        )->setCurPage(
            1
        );
		
		$collection->getSelect()->join('catalog_product_entity_tier_price as tier', 'price_index.entity_id = tier.entity_id');
		
		$collection->addFinalPrice()
              ->addMinimalPrice()
              ->getSelect()
			  ->where('tier.all_groups = 0')
			  ->where('price_index.Tier_price < price_index.price');
		
        return $collection;
    }
	public function getMode() {
		return "list";
	}
    /**
     * Prepare collection with new products
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        $this->setProductCollection($this->_getProductCollection());
        return parent::_beforeToHtml();
    }

    /**
     * Set how much product should be displayed at once.
     *
     * @param int $count
     * @return $this
     */
    public function setProductsCount($count)
    {
        $this->_productsCount = $count;
        return $this;
    }


	
    /**
     * Get how much products should be displayed at once.
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (null === $this->_productsCount) {
            $this->_productsCount = 32;
        }
        return $this->_productsCount;
    }

	    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */

	
    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Catalog\Model\Product::CACHE_TAG];
    }
}