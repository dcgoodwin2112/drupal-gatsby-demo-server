<?php

namespace Drupal\Tests\jsonapi_hypermedia\Kernel\Normalizer;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Url;
use Drupal\jsonapi\JsonApiResource\JsonApiDocumentTopLevel;
use Drupal\jsonapi\JsonApiResource\Link;
use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\NullIncludedData;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi\Normalizer\Value\CacheableNormalization;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test the link collection normalizer that replaces the core normalizer.
 *
 * @group jsonapi_hypermedia
 *
 * @internal
 */
final class JsonapiHypermediaLinkCollectionNormalizerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'jsonapi',
    'serialization',
    'jsonapi_hypermedia',
  ];

  /**
   * The normalizer under test.
   *
   * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
   */
  protected $normalizer;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->normalizer = $this->container->get('serializer.normalizer.link_collection.jsonapi');
    $this->normalizer->setSerializer($this->container->get('jsonapi.serializer'));
  }

  /**
   * Tests link collection normalization.
   */
  public function testNormalize() {
    // A single link with a key that matches its link relation type.
    $this->assertSame([
      'self' => [
        'href' => 'https://jsonapi.org',
      ],
    ], $this->getNormalization($this->getTestLinkCollection([
      // @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
      'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'self'),
      /*'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'self'),*/
    ])));

    // A single link with a key that's different from its link relation type.
    $this->assertSame([
      'self' => [
        'href' => 'https://jsonapi.org',
        'meta' => [
          'linkParams' => [
            'rel' => ['describedby'],
          ],
        ],
      ],
    ], $this->getNormalization($this->getTestLinkCollection([
      // @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
      'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'describedby'),
      /*'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'describedby'),*/
    ])));

    // Two links with a matching keys and matching link relation types.
    $link_collection = $this->getTestLinkCollection([
      // @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
      'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'self'),
      /*'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'self'),*/
    ]);
    // @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
    $link_collection = $link_collection->withLink('self', new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'self'));
    /*$link_collection = $link_collection->withLink('self', new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'self'));*/
    $this->assertSame([
      'self' => [
        'href' => 'https://jsonapi.org',
      ],
    ], $this->getNormalization($link_collection));

    // Two links with a matching keys but different link relation types.
    $link_collection = $this->getTestLinkCollection([
      // @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
      'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'self'),
      /*'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'self'),*/
    ]);
    // @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
    $link_collection = $link_collection->withLink('self', new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'describedby'));
    /*$link_collection = $link_collection->withLink('self', new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'describedby'));*/
    $this->assertSame([
      'self' => [
        'href' => 'https://jsonapi.org',
        'meta' => [
          'linkParams' => [
            'rel' => ['self', 'describedby'],
          ],
        ],
      ],
    ], $this->getNormalization($link_collection));

    // Two links with a matching keys and matching link relation types and
    // target attributes.
    $link_collection = $this->getTestLinkCollection([
      // @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
      'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'self', ['foo' => 'bar']),
      /*'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'self', ['foo' => 'bar']),*/
    ]);
    // @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
    $link_collection = $link_collection->withLink('self', new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'self', ['foo' => 'bar']));
    /*$link_collection = $link_collection->withLink('self', new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'self', ['foo' => 'bar']));*/
    $actual = $this->getNormalization($link_collection);
    $this->assertCount(1, $actual, var_export($actual, TRUE));
    $this->assertSame([
      'self' => [
        'href' => 'https://jsonapi.org',
        'meta' => [
          'linkParams' => [
            // @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
            'foo' => ['bar', 'bar'],
            /*'foo' => 'bar',*/
          ],
        ],
      ],
    ], $actual);

    // @todo: uncomment this section when https://www.drupal.org/project/drupal/issues/3080259 lands.
    //// Two links with a matching keys and matching link relation types, but
    //// different target attributes.
    //$link_collection = $this->getTestLinkCollection([
    //  // @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
    //  'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'self', ['foo' => 'bar']),
    //  /*'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'self', ['foo' => 'bar']),*/
    //]);
    //// @todo: delete the next line and uncomment the one after it when https://www.drupal.org/project/drupal/issues/3080259 lands.
    //$link_collection = $link_collection->withLink('self', new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'self', ['foo' => 'baz']));
    ///*$link_collection = $link_collection->withLink('self', new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'self', ['foo' => 'baz']));*/
    //$actual = $this->getNormalization($link_collection);
    //$this->assertCount(2, $actual, var_export($actual, TRUE));
    //$normalized_keys = array_keys($actual);
    //$this->assert(array_reduce($normalized_keys, function ($bool, $key) {
    //  return $bool ? strpos($key, 'self--') === 0 : FALSE;
    //}, TRUE), var_export($actual, TRUE));
    //$this->assertSame([
    //  'href' => 'https://jsonapi.org',
    //  'meta' => [
    //    'linkParams' => [
    //      'foo' => 'bar',
    //    ],
    //  ],
    //], $actual[$normalized_keys[0]]);
    //$this->assertSame([
    //  'href' => 'https://jsonapi.org',
    //  'meta' => [
    //    'linkParams' => [
    //      'foo' => 'baz',
    //    ],
    //  ],
    //], $actual[$normalized_keys[1]]);

    // Two links with different keys and link relation types that match their
    // keys.
    $this->assertSame([
      'related' => [
        'href' => 'https://jsonapi.org',
      ],
      'self' => [
        'href' => 'https://jsonapi.org',
      ],
    ], $this->getNormalization($this->getTestLinkCollection([
      // @todo: delete the next two lines and uncomment the ones after them when https://www.drupal.org/project/drupal/issues/3080259 lands.
      'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'self'),
      'related' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'related'),
      /*'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'self'),*/
      /*'related' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'related'),*/
    ])));

    // Two links with different keys and link relation types that match their
    // sibling's keys.
    $this->assertSame([
      'related' => [
        'href' => 'https://jsonapi.org',
        'meta' => [
          'linkParams' => [
            'rel' => ['self'],
          ],
        ],
      ],
      'self' => [
        'href' => 'https://jsonapi.org',
        'meta' => [
          'linkParams' => [
            'rel' => ['related'],
          ],
        ],
      ],
    ], $this->getNormalization($this->getTestLinkCollection([
      // @todo: delete the next two lines and uncomment the ones after them when https://www.drupal.org/project/drupal/issues/3080259 lands.
      'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'related'),
      'related' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), (array) 'self'),
      /*'self' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'related'),*/
      /*'related' => new Link(new CacheableMetadata(), Url::fromUri('https://jsonapi.org'), 'self'),*/
    ])));
  }

  /**
   * Gets a normalized array using the SUT.
   */
  protected function getNormalization(LinkCollection $link_collection) {
    $normalization = $this->normalizer->normalize($link_collection, 'api_json', []);
    $this->assertInstanceOf(CacheableNormalization::class, $normalization);
    return $normalization->getNormalization();
  }

  /**
   * Creates a link collection with which to test normalization.
   */
  protected function getTestLinkCollection(array $links) {
    Inspector::assertAllObjects($links, Link::class);
    $dummy_link_context = new JsonApiDocumentTopLevel(new ResourceObjectData([]), new NullIncludedData(), new LinkCollection($links));
    return $dummy_link_context->getLinks();
  }

}
