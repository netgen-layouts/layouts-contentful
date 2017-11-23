Installation instructions
=============================================================================

Use Composer to install the integration
---------------------------------------

Run the following command to install Netgen Block Manager & Contentful  integration:

```
composer require "netgen/block-manager-contentful:^1.0"
```

Activating integration bundle
-----------------------------

After completing standard Block Manager install instructions, you also need to activate `NetgenSyliusBlockManagerBundle`. Make sure it is activated after all other Block Manager bundles.

```
            new Contentful\ContentfulBundle\ContentfulBundle(),
            new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new FOS\HttpCacheBundle\FOSHttpCacheBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new Netgen\Bundle\CoreUIBundle\NetgenCoreUIBundle(),
            new Netgen\Bundle\ContentBrowserBundle\NetgenContentBrowserBundle(),
            new Netgen\Bundle\ContentBrowserUIBundle\NetgenContentBrowserUIBundle(),
            new Netgen\ContentfulBlockManagerBundle\NetgenContentfulBlockManagerBundle(),
            new Netgen\Bundle\BlockManagerBundle\NetgenBlockManagerBundle(),
            new Netgen\Bundle\BlockManagerUIBundle\NetgenBlockManagerUIBundle(),
            new Netgen\Bundle\BlockManagerAdminBundle\NetgenBlockManagerAdminBundle(),

```

For dev mode

```
            $bundles[] = new Netgen\Bundle\BlockManagerDebugBundle\NetgenBlockManagerDebugBundle();
```


Activate routing in routing.yml
-------------------------------
```
netgen_contentful_block_manager:
    resource: "@NetgenContentfulBlockManagerBundle/Resources/config/routing.yml"
    prefix:   /
```


Configure Contentful bundle
---------------------------
```
contentful:
    delivery:
        clients:
            default:
                space: space1_identifier
                token: space1_token_1b715a4ece19630de301bbb9d2ec3f89ea86
                cache: true
            products:
                space: space1_identifier
                token: space2_token_331af1d41ad61b715a4ece19630de301bbb9
                cache: false
```

https://github.com/contentful/ContentfulBundle


Configure CMS router
--------------------

```
cmf_routing:
    chain:
        routers_by_id:
            router.default: 200
            cmf_routing.dynamic_router: 100
    dynamic:
        default_controller: Netgen\ContentfulBlockManagerBundle:Contentful:view
        persistence:
            orm:
                enabled: true
```


http://symfony.com/doc/master/cmf/bundles/routing/introduction.html#installation


Enable translator
-----------------

```
framework:
    translator: { fallbacks: ['%locale%'] }
```


