<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TrackingBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $builder = $configuration->getConfigTreeBuilder();

        $this->assertInstanceOf(TreeBuilder::class, $builder);
    }

    /**
     * @dataProvider processConfigurationDataProvider
     */
    public function testProcessConfiguration(array $configs, array $expected)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $this->assertEquals($expected, $processor->processConfiguration($configuration, $configs));
    }

    public function processConfigurationDataProvider(): array
    {
        return [
            'empty' => [
                'configs'  => [[]],
                'expected' => [
                    'settings' => [
                        'resolved'                 => 1,
                        'dynamic_tracking_enabled' => [
                            'value' => false,
                            'scope' => 'app'
                        ],
                        'log_rotate_interval'      => [
                            'value' => 60,
                            'scope' => 'app'
                        ],
                        'piwik_host'               => [
                            'value' => null,
                            'scope' => 'app'
                        ],
                        'piwik_token_auth'         => [
                            'value' => null,
                            'scope' => 'app'
                        ],
                        'feature_enabled'          => [
                            'value' => true,
                            'scope' => 'app'
                        ],
                        'precalculated_statistic_enabled' => [
                            'value' => true,
                            'scope' => 'app'
                        ]
                    ]
                ]
            ]
        ];
    }
}
