<?php

namespace Drupal\Tests\commerce_webform_order\Functional;

use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;
use Drupal\Tests\webform\Traits\WebformAssertLegacyTrait;
use Drupal\Tests\webform\Traits\WebformBrowserTestTrait;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;

/**
 * Provides a base class for Commerce functional tests.
 */
abstract class CommerceWebformOrderTestBase extends CommerceBrowserTestBase {

  use WebformBrowserTestTrait;
  use WebformAssertLegacyTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * The order item repository.
   *
   * @var \Drupal\commerce_webform_order\OrderItemRepositoryInterface
   */
  protected $orderItemRepository;

  /**
   * The product.
   *
   * @var \Drupal\commerce_product\Entity\ProductInterface
   */
  protected $product;

  /**
   * The product variations.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface[]
   */
  protected $productVariations = [];

  /**
   * Webforms to load.
   *
   * @var array
   */
  protected static $testWebforms = [];

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_product',
    'commerce_order',
    'commerce_cart',
    'commerce_checkout',
    'commerce_checkout_test',
    'views_ui',
    'webform',
  ];

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->purgeSubmissions();

    parent::tearDown();
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->loadWebforms(static::$testWebforms);

    $this->placeBlock('commerce_cart');
    $this->placeBlock('commerce_checkout_progress');

    $this->orderItemRepository = $this->container->get('commerce_webform_order.order_item_repository');

    $this->productVariations['ONE'] = $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'sku' => 'ONE',
      'title' => 'Product variation #1',
      'price' => [
        'number' => 5.00,
        'currency_code' => 'USD',
      ],
    ]);
    $this->productVariations['TWO'] = $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'sku' => 'TWO',
      'title' => 'Product variation #2',
      'price' => [
        'number' => 7.00,
        'currency_code' => 'USD',
      ],
    ]);
    $this->productVariations['THREE'] = $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'sku' => 'THREE',
      'title' => 'Product variation #3',
      'price' => [
        'number' => 12.00,
        'currency_code' => 'USD',
      ],
    ]);

    $this->product = $this->createEntity('commerce_product', [
      'type' => 'default',
      'title' => 'My product',
      'variations' => $this->productVariations,
      'stores' => [$this->store],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_checkout_flow',
      'administer views',
      'administer webform',
      'administer webform submission',
    ], parent::getAdministratorPermissions());
  }

  /**
   * Reset caches.
   */
  protected function resetCache() {
    \Drupal::entityTypeManager()->getStorage('webform_submission')->resetCache();
    \Drupal::entityTypeManager()->getStorage('commerce_order')->resetCache();
    \Drupal::entityTypeManager()->getStorage('commerce_order_item')->resetCache();
  }

  /**
   * Post a product variation by SKU to a webform by ID.
   *
   * @param string $product_variation_sku
   *   The product variation SKU.
   * @param string $webform_id
   *   The webform ID.
   * @param string $submit
   *   The id, name, label or value of the submit button which is to be clicked.
   * @param array $options
   *   Options to be forwarded to the url generator.
   *
   * @return \Drupal\webform\WebformSubmissionInterface|null
   *   The current webform submission.
   */
  protected function postProductVariationToWebform($product_variation_sku, $webform_id, $submit = 'Submit', array $options = []) {
    // Post the selected product variation, load the last submission and return
    // it.
    $values = ['product_variation' => $this->productVariations[$product_variation_sku]->id()];

    return $this->postValuesToWebform($values, $webform_id, $submit, $options);
  }

  /**
   * Post a product variation by SKU to a webform by ID.
   *
   * @param array $values
   *   The webform submission values.
   * @param string $webform_id
   *   The webform ID.
   * @param string $submit
   *   The id, name, label or value of the submit button which is to be clicked.
   * @param array $options
   *   Options to be forwarded to the url generator.
   *
   * @return \Drupal\webform\WebformSubmissionInterface|null
   *   The current webform submission.
   */
  protected function postValuesToWebform(array $values, $webform_id, $submit = 'Submit', array $options = []) {
    // Load the webform and get the submission form's submit path.
    /** @var \Drupal\webform\WebformInterface $webform */
    $webform = Webform::load($webform_id);
    $webform_path = $webform->getSetting('page_submit_path');

    // Post the values, load the last submission and return it.
    $this->drupalPostForm($webform_path, $values, $submit, $options);

    $sid = $this->getLastSubmissionId($webform);

    return WebformSubmission::load($sid);
  }

  /**
   * Go to the cart page from add to cart message link.
   */
  protected function gotToCartPageFromLink() {
    // @see \Drupal\commerce_cart\EventSubscriber\CartEventSubscriber::displayAddToCartMessage()
    $cart_link = $this->getSession()->getPage()->findLink('your cart');
    $cart_link->click();
  }

  /**
   * Complete a cart's checkout.
   */
  protected function completeCheckoutFromCart() {
    // Submit the cart's form.
    $this->submitForm([], 'Checkout');

    // We are in the login checkout step, continue.
    $this->submitForm([], 'Continue as Guest');

    // We are in order the information checkout step, continue.
    $this->submitForm([
      'contact_information[email]' => 'guest@example.com',
      'contact_information[email_confirm]' => 'guest@example.com',
      'billing_information[profile][address][0][address][given_name]' => 'John',
      'billing_information[profile][address][0][address][family_name]' => 'Smith',
      'billing_information[profile][address][0][address][organization]' => 'Example',
      'billing_information[profile][address][0][address][address_line1]' => '9 Drupal Ave',
      'billing_information[profile][address][0][address][postal_code]' => '94043',
      'billing_information[profile][address][0][address][locality]' => 'Mountain View',
      'billing_information[profile][address][0][address][administrative_area]' => 'CA',
    ], 'Continue to review');

    // We are in the review checkout step, complete the order.
    $this->submitForm([], 'Complete checkout');
  }

}
