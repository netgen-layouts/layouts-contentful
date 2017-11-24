Netgen Block Manager and Contentful integration installation instructions
=======================================================================

Installing Netgen Block Manager
-------------------------------

Follow the instruction on the Layouts documentation page to install the Block Manager:
http://docs.netgen.io/projects/layouts/en/latest/reference/install_instructions.html

As a minimum you need to:
- compose the "netgen/block-manager" package
- activate all needed bundles
- import database tables and data with the migration script
- install assets
- extend your view templates with ngbm.layoutTemplate (for fresh Symfony 2/3 installation its default/index.html.twig)
- add the "layout" twig block to your base pagelayout (for fresh Symfony 2/3 installation its base.html.twig)
- configure the block manager to use your base pagelayout (for fresh Symfony 2/3 installation its base.html.twig)

Use Composer to install the integration
---------------------------------------

Run the following command to install Contentful integration:
```
composer require netgen/block-manager-contentful
```

Activating integration bundle
-----------------------------

After completing standard Block Manager install instructions, you also need to
activate `NetgenContentfulBlockManagerBundle` together with its dependencies in app/AppKernel.php.
Make sure it is activated after all other Block Manager bundles.
```
...
new Contentful\ContentfulBundle\ContentfulBundle(),
new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
new Netgen\ContentfulBlockManagerBundle\NetgenContentfulBlockManagerBundle(),
...
```

Configure Contentful bundle
---------------------------

To work with Contentful resources, you need to configure a client for
every space you wish to use. For every client, you need to specify the
space ID and its token in app/config/config.yml. Space IDs and tokens you can get from the APIs
section from your Contentful instance: https://app.contentful.com/
```
contentful:
    delivery:
        clients:
            default:
                space: space1_identifier
                token: space1_token
                cache: true
            some_other_space:
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
the dynamic router in your configuration in app/config/config.yml:
```
cmf_routing:
    chain:
        routers_by_id:
            router.default: 200
            cmf_routing.dynamic_router: 100
    dynamic:
        default_controller: NetgenContentfulBlockManagerBundle:Contentful:view
        persistence:
            orm:
                enabled: true
```

For more information, see CMF Routing docs at
http://symfony.com/doc/master/cmf/bundles/routing/index.html

Enable translator
-----------------

By default, on fresh Symfony 2/3 installation, the translator is not enable, so you need to uncomment the line in app/config/config.yml:
```
framework:
    translator: { fallbacks: ['%locale%'] }
```

Import the schema
-----------------

Import the routing and local content schema to the database. Use the force, Luke.
```
php bin/console doctrine:schema:update --force
```


Configure authentication for Block Manager
------------------------------------------

It is highly recommended to secure the Block Manager interface and allow only
authenticated users in. For fresh Symfony2/3 installations the simplest way is to
defined an admin user in memory with the ROLE_NGBM_ADMIN role and enable the
HTTP Basic firewall. Your app/config/security.yml should like this:
```
security:
    providers:
        in_memory:
            memory:
                users:
                    admin:
                        password: admin
                        roles: 'ROLE_NGBM_ADMIN'
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    firewalls:
        main:
            http_basic: ~

    access_control:
        - { path: ^/bm, role: [ROLE_NGBM_ADMIN] }
        - { path: ^/cb, role: [ROLE_NGBM_ADMIN] }
```
