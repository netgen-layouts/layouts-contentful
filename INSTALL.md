Netgen Block Manager & Contentful integration installation instructions
=======================================================================

Use Composer to install the integration
---------------------------------------

Run the following command to install Netgen Block Manager & Contentful
integration:

```
composer require netgen/block-manager-contentful:^1.0
```

Activating integration bundle
-----------------------------

After completing standard Block Manager install instructions, you also need to
activate `NetgenContentfulBlockManagerBundle` together with its dependencies.
Make sure it is activated after all other Block Manager bundles.

```
...
new Contentful\ContentfulBundle\ContentfulBundle(),
new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
new Netgen\ContentfulBlockManagerBundle\NetgenContentfulBlockManagerBundle(),
...
```

Activate routes in routing.yml
------------------------------

```
netgen_block_manager_contentful:
    resource: "@NetgenContentfulBlockManagerBundle/Resources/config/routing.yml"
```

Configure Contentful bundle
---------------------------

To work with Contentful resources, you need to configure a clieent for
every space you wish to use. For every client, you need to specify the
space ID and its token.

```
contentful:
    delivery:
        clients:
            default:
                space: space1_identifier
                token: space1_token
                cache: true
            products:
                space: space1_identifier
                token: space2_token
                cache: false
```

For more information, see Contentful bundle official repo at
https://github.com/contentful/ContentfulBundle

Configure the CMF Routing component
-----------------------------------

The integration uses CMF routing component and its dynamic router to match
routes and generate URLs to resources from Contentful. You need to enable
the dynamic router in your configuration:

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

For more information, see CMF Routing docs at
http://symfony.com/doc/master/cmf/bundles/routing/index.html