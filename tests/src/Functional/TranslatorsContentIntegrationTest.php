<?php

namespace Drupal\Tests\translation_views\Functional;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\translators_content\Functional\TranslatorsContentTestsTrait;

/**
 * Class TranslatorsContentIntegrationTest.
 *
 * @package Drupal\Tests\translation_views\Functional
 *
 * @group translation_views
 * @requires module translators
 */
class TranslatorsContentIntegrationTest extends BrowserTestBase {
  use TranslatorsContentTestsTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  public static $modules = ['translation_views_translators_test'];

  /**
   * Translators skills service.
   *
   * @var \Drupal\translators\Services\TranslatorSkills
   */
  protected $translatorSkills;
  /**
   * User registered skills.
   *
   * @var array
   */
  protected static $registeredSkills = ['en', 'fr'];
  /**
   * User unregistered skills.
   *
   * @var array
   */
  protected static $unregisteredSkills = ['de', 'sq'];
  /**
   * Default language ID.
   *
   * @var string
   */
  protected $defaultLanguage = 'en';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalLogin($this->rootUser);
    $this->translatorSkills = $this->container->get('translators.skills');
    $this->createLanguages(['fr', 'de', 'sq']);
    $this->enableTranslation('node', 'article');
    $this->drupalLogout();
  }

  /**
   * Simply check that all required modules have been installed.
   */
  public function testDependencyInstallation() {
    $this->assertTrue($this->container->get('module_handler')
      ->moduleExists('translators'));
    $this->assertTrue($this->container->get('module_handler')
      ->moduleExists('translators_content'));
    $this->assertTrue($this->container->has('translators.skills'));
  }

  /**
   * Test Content Translators integration for target language filter.
   */
  public function testTranslatorsLanguageFilterInView() {
    $this->drupalLogin($this->rootUser);
    $this->addSkill(['en', 'fr']);
    $node = $this->createTestNode();
    $this->addAllNodeTranslations($node);

    // Check that all languages are available as target language.
    $this->drupalGet('/test-translators-content-filter');
    $this->assertResponse(200);

    $this->assertOptionCount('translation_target_language', 4);
    $this->assertOptionAvailable('translation_target_language', 'en');
    $this->assertOptionAvailable('translation_target_language', 'fr');
    $this->assertOptionAvailable('translation_target_language', 'de');
    $this->assertOptionAvailable('translation_target_language', 'sq');

    // Limit target languages to translation skills.
    $this->drupalGet('/admin/structure/views/nojs/handler/test_translators_content_integration/page_1/filter/translation_target_language');
    // Check for the default state of the options.
    $this->assertSession()->checkboxNotChecked('options[limit]');
    $this->assertSession()->checkboxNotChecked('options[column][source]');
    $this->assertSession()->checkboxChecked('options[column][target]');
    // Update options.
    $this->drupalPostForm(NULL, [
      'options[limit]'        => 1,
      'options[column][source]' => 1,
      'options[column][target]'   => 1,
    ], 'Apply');
    $this->click('input[value="Save"]');

    // Check that all languages are available as target language.
    $this->drupalGet('/test-translators-content-filter');
    $this->assertResponse(200);

    $this->assertOptionCount('translation_target_language', 2);
    $this->assertOptionAvailable('translation_target_language', 'en');
    $this->assertOptionAvailable('translation_target_language', 'fr');
    $this->assertOptionNotAvailable('translation_target_language', 'de');
    $this->assertOptionNotAvailable('translation_target_language', 'sq');

    // Check results without any registered translation skills.
    $this->removeSkills();
    $this->drupalGet('/test-translators-content-filter');
    $this->assertResponse(200);
      $this->assertSession()
        ->elementNotExists('css', 'table > tbody > tr:nth-child(1)');
    $this->assertOptionCount('translation_target_language', 1);
    $this->assertOptionAvailable('translation_target_language', 'All');

  }

  /**
   * Test Content Translators integration for target language filter.
   */
  public function testTranslatorsOperationLinks() {
    $userTranslatorsLimited = $this->createUser([
      'translators_content create content translations',
      'translators_content update content translations',
      'translators_content delete content translations',
      'translate any entity',
    ]);
    Node::create([
      'type' => 'article',
      'title' => "English node",
      'langcode' => 'en',
    ])->save();

    $this->drupalLogin($userTranslatorsLimited);
    $this->addSkill(['en', 'fr']);
    // Check Add translation.
    $this->drupalGet('/test-translators-content-filter', [
      'query' => [
        'langcode' => 'en',
        'translation_target_language' => 'fr',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertSession()
      ->elementTextContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations ul li a",
        'Add'
      );
    $this->drupalGet('/test-translators-content-filter', [
      'query' => [
        'langcode' => 'en',
        'translation_target_language' => 'de',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertSession()
      ->elementTextNotContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations",
        'Add'
      );
    // Check edit Edit and Delete translation.
    Node::load(1)->addTranslation('fr', ['title' => 'French translation '])
      ->save();
    $this->drupalGet('/test-translators-content-filter', [
      'query' => [
        'langcode' => 'en',
        'translation_target_language' => 'fr',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertSession()
      ->elementTextContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations ul .edit a",
        'Edit'
      );
    $this->assertSession()
      ->elementTextContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations ul .delete a",
        'Delete'
      );
    $this->drupalGet('/test-translators-content-filter', [
      'query' => [
        'langcode' => 'en',
        'translation_target_language' => 'de',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertSession()
      ->elementTextNotContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations",
        'Edit'
      );
    $this->assertSession()
      ->elementTextNotContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations",
        'Delete'
      );
  }

}
