<?php

namespace Drupal\Tests\views_rss\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the rss fields style display plugin.
 *
 * @group views_rss
 */
class DisplayFeedTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'file',
    'image',
    'node',
    'taxonomy',
    'views',
    'views_rss',
    'views_rss_core',
    'views_rss_test_config',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * Test start timestamp used for time comparisons.
   *
   * @var int
   */
  protected $testStartTime;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->testStartTime = \Drupal::time()->getCurrentTime();
  }

  /**
   * Tests the rendered output.
   */
  public function testFeedOutput() {
    $this->drupalCreateContentType(['type' => 'page']);
    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_image',
      'bundle' => 'page',
    ])->save();
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $image = File::create([
      'uri' => 'public://example.jpg',
    ]);
    $image->save();

    // Verify a title with HTML entities is properly escaped.
    $node_title = 'This "cool" & "neat" article\'s title';
    $node = $this->drupalCreateNode([
      'title' => $node_title,
      'body' => [
        0 => [
          'value' => 'A paragraph',
          'format' => filter_default_format(),
        ],
      ],
      'field_image' => $image,
    ]);
    $node_link = $node->toUrl()->setAbsolute()->toString();
    $node2 = $this->drupalCreateNode();
    $node2->setCreatedTime(strtotime(('-1 day')))->save();

    $this->drupalGet('views-rss.xml');
    $this->assertSession()->responseHeaderEquals('Content-Type', 'application/rss+xml; charset=utf-8');
    $this->assertEquals($node_title, $this->getSession()->getDriver()->getText('//item/title'));
    $this->assertEquals($node_link, $this->getSession()->getDriver()->getText('//item/link'));
    $this->assertEquals($node_link, $this->getSession()->getDriver()->getText('//item/comments'));
    // Verify HTML is properly escaped in the description field.
    $this->assertSession()->responseContains('&lt;p&gt;A paragraph&lt;/p&gt;');
    $selector = sprintf(
      'enclosure[@url="%s"][@length="%s"][@type="%s"]',
      file_create_url('public://example.jpg'),
      $image->getSize(),
      $image->getMimeType()
    );
    $this->assertNotNull($this->getSession()->getDriver()->find($selector));

    // Verify query parameters are included in the output.
    $this->drupalGet('views-rss.xml', ['query' => ['field_tags_target_id' => 1]]);
    $this->assertContains('views-rss.xml?field_tags_target_id=1', $this->getSession()->getDriver()->getText('//item/source/@url'));

    // Verify the channel pubDate matches the highest node pubDate.
    $this->assertEquals(gmdate('r', $node->getCreatedTime()), $this->getSession()->getDriver()->getText('//channel/pubDate'));
    $this->assertGreaterThanOrEqual($this->testStartTime, strtotime($this->getSession()->getDriver()->getText('//channel/lastBuildDate')));
  }

}
