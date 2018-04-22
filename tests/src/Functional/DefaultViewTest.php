<?php

namespace Drupal\Tests\translation_views\Functional;

use Drupal\Tests\node\Functional\NodeTestBase;

/**
 * This test will check translation_views default view existence & and functionality.
 *
 * @group translation_views
 */
class DefaultViewTest extends NodeTestBase {

  protected $profile = 'standard';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'views_ui', 'translation_views'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $user = $this->drupalCreateUser([
      'administer views',
      'create content translations',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Tests that Content Translation jobs view admin page exists.
   */
  public function testContentTranslationsViewUIBackendPage() {
    $this->drupalGet('admin/structure/views/view/content_translations');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Content Translation jobs');
  }

  /**
   * Tests that Content Translation jobs front-end page exists.
   */
  public function testContentTranslationsJobViewFrontendPage() {
    $this->drupalGet('translate/content');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Content Translation jobs');
  }

  /**
   * Tests that module can be installed and uninstalled successfully.
   */
  public function testModuleInstallUninstall() {
    $this->assertTrue(\Drupal::moduleHandler()
      ->moduleExists('translation_views'), 'Module doesn\'n exist after first installation');

    \Drupal::service('module_installer')->uninstall(['translation_views']);
    $this->assertFalse(\Drupal::moduleHandler()
      ->moduleExists('translation_views'), 'Module exists after deinstallation');

    \Drupal::service('module_installer')->install(['translation_views']);
    $this->assertTrue(\Drupal::moduleHandler()
      ->moduleExists('translation_views'), 'Module doesn\'n exist after re-installation');
  }

}
