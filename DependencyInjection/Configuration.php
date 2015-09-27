<?php

namespace HeidiLabs\SauthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('heidi_labs_sauth');

        $rootNode->children()
            ->scalarNode('home')->defaultValue('homepage')->end()
            ->scalarNode('user_class')->defaultValue('AppBundle\Entity\User')->end()
            ->scalarNode('credentials_class')->defaultValue('AppBundle\Entity\Credentials')->end()
            ->booleanNode('allow_registration')->defaultValue(true)->end()
            ->arrayNode('services')
                    ->append($this->getServiceNode('google', 'HeidiLabs\SauthBundle\OAuthService\GoogleService'))
                    ->append($this->getServiceNode('github', 'HeidiLabs\SauthBundle\OAuthService\GithubService'))
            ->end();

        return $treeBuilder;
    }

    /**
     * @param string $service
     * @return NodeDefinition $node
     */
    private function getServiceNode($service, $defaultServiceClass)
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($service);

        $rootNode
            ->children()
                ->scalarNode('class')->defaultValue($defaultServiceClass)->end()
                    ->arrayNode('config')
                        ->children()
                            ->scalarNode('client_id')->cannotBeEmpty()->end()
                            ->scalarNode('client_secret')->cannotBeEmpty()->end()
                            ->scalarNode('client_name')->defaultValue('Sauth')->end()
                            ->scalarNode('callback_url')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }
}
