Netgen Block Manager & Contentful integration installation instructions
=======================================================================

Installing Netgen Block Manager
-------------------------------

Follow the instructions in Netgen Layouts documentation to [install the Block Manager](http://docs.netgen.io/projects/layouts/en/latest/reference/install_instructions.html).

As a minimum you need to:

* require `netgen/block-manager` and `netgen/block-manager-standard` packages in Composer
* activate all needed bundles
* activate Block Manager and Content Browser routes
* import Block Manager database tables with Doctrine Migrations
* install assets
* extend your view templates with `ngbm.layoutTemplate` (for fresh Symfony installations, the template is `default/index.html.twig`)
* add the "layout" twig block to your base pagelayout (for fresh Symfony installations, the template is `base.html.twig`)
* configure the block manager to use your base pagelayout (for fresh Symfony installations, the template is `base.html.twig`)

Enable Translator component
---------------------------

By default, on fresh Symfony installations, the Translator component is not enabled, so you need to uncomment the line in `app/config/config.yml`:

```
framework:
    translator: { fallbacks: ['%locale%'] }
```

Configure authentication for Block Manager
------------------------------------------

It is highly recommended to secure the Block Manager interface and allow only
authenticated users in. For fresh Symfony installations the simplest way is to
defined an admin user in memory with the `ROLE_NGBM_ADMIN` role and enable the
HTTP basic auth firewall. Your `app/config/security.yml` should like this:

```
security:
    providers:
        in_memory:
            memory:
                users:
                    admin:
                        password: admin
                        roles: ROLE_NGBM_ADMIN

    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        main:
            anonymous: ~
            http_basic: ~

    access_control:
        - { path: ^/bm/(api|app|admin), role: [ROLE_NGBM_ADMIN] }
        - { path: ^/cb, role: [ROLE_NGBM_ADMIN] }
```

Verifying that Block Manager works
----------------------------------

Start the server with:

```
php bin/console server:run
```

Open [http://127.0.0.1:8000/bm/admin/layouts](http://127.0.0.1:8000/bm/admin/layouts/), give the admin credentials and you should be able to create new layouts.

Use Composer to install the integration
---------------------------------------

Run the following command to install Contentful integration:

```
composer require netgen/block-manager-contentful
```

Activating integration bundle
-----------------------------

After completing standard Block Manager install instructions, you also need to
activate `NetgenContentfulBlockManagerBundle` together with its dependencies in `app/AppKernel.php`.
Make sure it is activated after all other Block Manager bundles.

```
new Contentful\ContentfulBundle\ContentfulBundle(),
new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
new Netgen\Bundle\ContentfulBlockManagerBundle\NetgenContentfulBlockManagerBundle(),
```

Configure Contentful bundle
---------------------------

To work with Contentful resources, you need to configure a client for
every space you wish to use. For every client, you need to specify the
space ID and its token in `app/config/config.yml`. You can get space IDs
and tokens from the APIs section from your Contentful instance at
[app.contentful.com](https://app.contentful.com)

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

You can verify the configuration with this command:

```
php bin/console contentful:info
```

For more information, see Contentful bundle [official repo on Github](https://github.com/contentful/ContentfulBundle).

Configure the CMF Routing component
-----------------------------------

The integration uses CMF routing component and its dynamic router to match
routes and generate URLs to resources from Contentful. You need to enable
the dynamic router in your configuration in `app/config/config.yml`:

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

For more information, see [CMF Routing docs on symfony.com](http://symfony.com/doc/master/cmf/bundles/routing/index.html).

Configure routing
-----------------

Add routing configuration to `app/config/routing.yml`:

```
netgen_block_manager_contentful:
    resource: "@NetgenContentfulBlockManagerBundle/Resources/config/routing.yml"
```

Import the schema
-----------------

Import the routing and local content schema to the database. Use the Force, Luke.
```
php bin/console doctrine:schema:update --force
```

Optional - run the sync command
-------------------------------

To warmup the caching for spaces and content types as well as syncing Contentful entries locally run this command:

```
php bin/console contentful:sync
```

There is a limit on 100 entries in one run, so if there are more than 100 entries you can run the command multiple times.

Optional - configure webhook
----------------------------

To make local cache refresh when an entry or a content type changes in Contentful add the webhook configuration.

Go to Contentful space settings and create a webhook with:
* the full URL for webhook (http://your.domain/webhook)
* basic auth credentials if necessary
* additional `X-Space-Id` header with the related value
* checked Published, Unpublished and Delete events for Content Types
* checked Published, Unpublished and Delete events for Entries

Set user rights
---------------

In case your web server user differs from your user for managing files, make cache and logs writeable:

```
setfacl -R -m u:www-data:rwX -m g:www-data:rwX var/{cache,logs,sessions}
setfacl -dR -m u:www-data:rwX -m g:www-data:rwX var/{cache,logs,sessions}
```

Use it
------

Open again [http://127.0.0.1:8000/bm/admin/layouts](http://127.0.0.1:8000/bm/admin/layouts/) and start creating
Contentful specific layouts and mapping them to URL targets.
