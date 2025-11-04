<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Elasticsearch Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Elasticsearch client connection and settings.
    | These settings are used by the ElasticsearchClient.
    |
    */

  'hosts' => [
    env('ELASTICSEARCH_HOST', 'localhost:9200'),
  ],

  'auth' => [
    'username' => env('ELASTICSEARCH_USERNAME'),
    'password' => env('ELASTICSEARCH_PASSWORD'),
  ],

  'ssl' => [
    'verify' => env('ELASTICSEARCH_SSL_VERIFY', true),
    'ca_bundle' => env('ELASTICSEARCH_CA_BUNDLE'),
  ],

  'indices' => [
    'products' => [
      'name' => env('ELASTICSEARCH_PRODUCTS_INDEX', 'marketplace_products'),
      'settings' => [
        'number_of_shards' => env('ELASTICSEARCH_SHARDS', 1),
        'number_of_replicas' => env('ELASTICSEARCH_REPLICAS', 0),
      ],
      'mappings' => [
        'properties' => [
          'title' => [
            'type' => 'text',
            'analyzer' => 'standard',
            'fields' => [
              'keyword' => [
                'type' => 'keyword',
                'ignore_above' => 256
              ]
            ]
          ],
          'brand' => [
            'type' => 'keyword',
          ],
          'category_id' => [
            'type' => 'keyword',
          ],
          'price' => [
            'type' => 'float',
          ],
          'rating' => [
            'type' => 'float',
          ],
          'stock' => [
            'type' => 'integer',
          ],
          'popularity' => [
            'type' => 'integer',
          ],
          'attributes' => [
            'type' => 'object',
            'dynamic' => true,
          ],
          'created_at' => [
            'type' => 'date',
            'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
          ],
        ],
      ],
    ],
  ],

  'query_defaults' => [
    'size' => 15,
    'from' => 0,
    'timeout' => '30s',
    'track_total_hits' => true,
  ],

  'circuit_breaker' => [
    'failure_threshold' => env('ELASTICSEARCH_FAILURE_THRESHOLD', 5),
    'timeout' => env('ELASTICSEARCH_TIMEOUT', 60),
    'expected_exception' => \Exception::class,
  ],

  'mock' => [
    'enabled' => env('ELASTICSEARCH_MOCK', false),
    'delay' => env('ELASTICSEARCH_MOCK_DELAY', 0.1), // seconds
  ],
];
