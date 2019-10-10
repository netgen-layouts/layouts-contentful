# Netgen Layouts & Contentful integration installation instructions

## Installing Netgen Layouts

Follow the instructions in Netgen Layouts documentation to
[install Netgen Layouts](https://docs.netgen.io/projects/layouts/en/latest/getting_started/install_existing_project.html).

As a minimum you need to:

* require `netgen/layouts-standard` package in Composer
* activate all needed bundles
* activate Netgen Layouts and Content Browser routes
* import Netgen Layouts database tables with Doctrine Migrations
* install assets
* extend your controller templates with `nglayouts.layoutTemplate`
* add the "layout" twig block to your main layout template
* configure Netgen Layouts to use your main layout template

## Use Composer to install the integration

After completing standard Netgen Layouts installation, run the following command
to install Contentful integration:

```
composer require netgen/layouts-contentful
```

## Activating integration bundle

Activate `NetgenLayoutsContentfulBundle` together with its dependencies in
your kernel. Make sure it is activated after all other Netgen Layouts bundles.
The full list of bundles is as follows:

```
Contentful\ContentfulBundle\ContentfulBundle
Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle
Netgen\Bundle\LayoutsContentfulBundle\NetgenLayoutsContentfulBundle
```

## Configure Contentful bundle

To work with Contentful resources, you need to configure a client for every
space you wish to use. For every client, you need to specify the space ID and
its token. You can get space IDs and tokens from the APIs section from your
Contentful instance at [app.contentful.com](https://app.contentful.com)

```
contentful:
    delivery:
        main:
            token: your_space_token
            space: your_space_identifier
```

For more information, see Contentful bundle
[official repo on Github](https://github.com/contentful/ContentfulBundle).

## Configure the CMF Routing component

The integration uses CMF routing component and its dynamic router to match
routes and generate URLs to resources from Contentful. You need to enable the
dynamic router in your configuration:

```
cmf_routing:
    chain:
        routers_by_id:
            router.default: 200
            cmf_routing.dynamic_router: 100
    dynamic:
        default_controller: netgen_layouts.contentful.controller.view
        persistence:
            orm:
                enabled: true
```

For more information, see [CMF Routing docs on symfony.com](https://symfony.com/doc/master/cmf/bundles/routing/index.html).

## Configure routing

Add routing configuration to your configuration:

```
netgen_layouts_contentful:
    resource: "@NetgenLayoutsContentfulBundle/Resources/config/routing.yml"
```

## Import the schema

Import the routing and local content schema to the database. Use the Force,
Luke.

```
php bin/console doctrine:schema:update --force
```

## Configure authentication for Netgen Layouts

It is highly recommended to secure the Netgen Layouts interface and allow only
authenticated users in. For fresh Symfony installations the simplest way is to
define an admin user in memory with the `ROLE_NGLAYOUTS_ADMIN` role and enable
the HTTP basic auth firewall. Add the following parts to relevant sections of
your `security.yml`:

```
security:
    providers:
        in_memory:
            memory:
                users:
                    admin:
                        password: admin
                        roles: ROLE_NGLAYOUTS_ADMIN

    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    firewalls:
        main:
            http_basic: ~

    access_control:
        - { path: ^/nglayouts/(api|app|admin), role: [ROLE_NGLAYOUTS_ADMIN] }
        - { path: ^/cb, role: [ROLE_NGLAYOUTS_ADMIN] }
```

## Verifying that Netgen Layouts works

Start the [Symfony CLI web server](https://symfony.com/download) with:

```
symfony server:ca:install # For HTTPS support, only needs to be ran once
symfony server:start
```

Open [https://127.0.0.1:8000/nglayouts/admin/layouts](https://127.0.0.1:8000/nglayouts/admin/layouts/),
give the admin credentials and you should be able to create new layouts.

## Optional: Run the sync command

To warmup the caching for spaces and content types as well as syncing Contentful
entries locally run this command:

```
php bin/console contentful:sync
```

There is a limit on 100 entries in one run, so if there are more than 100
entries you can run the command multiple times.

## Optional: Configure webhook

To make local cache refresh when an entry or a content type changes in
Contentful add the webhook configuration.

Go to Contentful space settings and create a webhook with:

* the full URL for webhook (https://your.domain/webhook)
* basic auth credentials if necessary
* additional `X-Space-Id` header with the related value
* checked Published, Unpublished and Delete events for Content types
* checked Published, Unpublished and Delete events for entries

## Optional: Implement custom sluggers

This bundle offer the possibility to implement custom sluggers to generate URLs
for full content based pages. Out of the box there are 2 sluggers implemented:

* `simple` slugger - takes the name of the Contentful entry and makes the slug.
  This one is used by default
* `with_space` slugger - adds the space name before the entry name so the URL
  will have the format `/[space_name_slug]/[entry_name_slug]`

To implement a custom slugger you need to implement the `EntrySluggerInterface`.
A `FilterSlugTrait` is provided so you can filter the slug as required by
Contentful.

```
final class MySlugger implements EntrySluggerInterface
{
    use FilterSlugTrait;

    public function getSlug(ContentfulEntry $contentfulEntry): string
    {
        return '/my_prefix/' . $this->filterSlug($contentfulEntry->getName());
    }
}
```

The function `filterSlug()` creates an URL friendly string from any given
string.

Then declare your class as service and tag it:

```
my_app.entry_slugger.with_my_prefix:
    class: MyApp\Routing\EntrySlugger\WithMyPrefix
    tags:
        - { name: netgen_layouts.contentful.entry_slugger, type: with_my_prefix }
```

Finally, you need to configure your app to use your slugger. You can declare it
as a default one and declare it for each content type using the content type ID:

```
netgen_layouts_contentful:
    entry_slug_type:
        default: with_my_prefix
        content_type:
            # Category content type
            5KMiN6YPvi42icqAUQMCQe: simple
            # Post content type
            2wKn6yEnZewu2SCCkus4as: with_my_prefix
```

And that should be it!

## Use it

Open again [https://127.0.0.1:8000/nglayouts/admin/layouts](https://127.0.0.1:8000/nglayouts/admin/layouts/)
and start creating Contentful specific layouts and map them to URL targets.
