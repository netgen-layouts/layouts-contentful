# Netgen Layouts & Contentful integration

## Installation instructions

### Installing Netgen Layouts

Follow the instructions in Netgen Layouts documentation to
[install Netgen Layouts](https://docs.netgen.io/projects/layouts/en/latest/getting_started/install_existing_project.html).

### Use Composer

After completing standard Netgen Layouts installation, run the following command
to install Contentful integration:

```bash
composer require netgen/layouts-contentful
```

Symfony Flex will automatically enable the bundle and import the routes.

### Configure Contentful bundle

To work with Contentful resources, you need to configure a client for every
space you wish to use. For every client, you need to specify the space ID and
its token. You can get space IDs and tokens from the APIs section from your
Contentful instance at [app.contentful.com](https://app.contentful.com)

```yaml
# config/packages/contentful.yaml
contentful:
    delivery:
        main:
            token: your_space_token
            space: your_space_identifier
```

For more information, see Contentful bundle
[official repo on Github](https://github.com/contentful/ContentfulBundle).

### Configure the CMF Routing component

The integration uses CMF routing component and its dynamic router to match
routes and generate URLs to resources from Contentful. You need to enable the
dynamic router in your configuration:

```yaml
# config/packages/cmf_routing.yaml
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

For more information, see [CMF Routing docs on symfony.com](https://symfony.com/bundles/CMFRoutingBundle/current/index.html).

### Import the schema

Import the routing and local content schema to the database.

```bash
php bin/console doctrine:schema:update
```

**Note:** Use the command with `--dump-sql` first to check that it will do only
modifications related to this bundle, and then use it with parameter `--force`
to do the actual changes.

### Configure authentication for Netgen Layouts

It is highly recommended to secure the Netgen Layouts interface and allow only
authenticated users in. For fresh Symfony installations the simplest way is to
define an admin user in memory with the `ROLE_NGLAYOUTS_ADMIN` role and enable
the HTTP basic auth firewall. 

```yaml
# config/packages/security.yaml
security:
    providers:
        users_in_memory:
            memory:
                users:
                    admin:
                        password: admin
                        roles: [ROLE_NGLAYOUTS_ADMIN]

    password_hashers:
        Symfony\Component\Security\Core\User\InMemoryUser: plaintext

    firewalls:
        main:
            provider: users_in_memory
            http_basic: ~

    access_control:
        - { path: ^/nglayouts/(api|(dev/)?app|admin), roles: ROLE_NGLAYOUTS_ADMIN }
        - { path: ^/cb, roles: ROLE_NGLAYOUTS_ADMIN }
```

### Verifying that Netgen Layouts works

Start the [Symfony CLI web server](https://symfony.com/download) with:

```bash
symfony server:start
```

Open [https://127.0.0.1:8000/nglayouts/admin/layouts](https://127.0.0.1:8000/nglayouts/admin/layouts/),
give the admin credentials and you should be able to create new layouts.

### Optional: Run the sync command

To warmup the caching for spaces and content types as well as syncing Contentful
entries locally run this command:

```bash
php bin/console contentful:sync
```

There is a limit on 100 entries in one run, so if there are more than 100
entries you can run the command multiple times.

### Optional: Configure webhook

To make local cache refresh when an entry or a content type changes in
Contentful add the webhook configuration.

Go to Contentful space settings and create a webhook with:

* the full URL for webhook (https://your.domain/webhook)
* basic auth credentials if necessary
* additional `X-Space-Id` header with the related value
* checked Published, Unpublished and Delete events for Content types
* checked Published, Unpublished and Delete events for entries

### Optional: Implement custom sluggers

This bundle offer the possibility to implement custom sluggers to generate URLs
for full content based pages. Out of the box there are 2 sluggers implemented:

* `simple` slugger - takes the name of the Contentful entry and makes the slug.
  This one is used by default
* `with_space` slugger - adds the space name before the entry name so the URL
  will have the format `/[space_name_slug]/[entry_name_slug]`

To implement a custom slugger you need to implement the `EntrySluggerInterface`.
A `FilterSlugTrait` is provided so you can filter the slug as required by
Contentful.

```php
<?php

declare(strict_types=1);

namespace App\Routing\EntrySlugger;

use Netgen\Layouts\Contentful\Attribute\AsEntrySlugger;
use Netgen\Layouts\Contentful\Routing\EntrySlugger\FilterSlugTrait;
use Netgen\Layouts\Contentful\Routing\EntrySluggerInterface;

#[AsEntrySlugger('with_my_prefix')]
final class WithMyPrefix implements EntrySluggerInterface
{
    use FilterSlugTrait;

    public function getSlug(ContentfulEntry $contentfulEntry): string
    {
        return '/my_prefix/' . $this->filterSlug($contentfulEntry->getName());
    }
}
```

`filterSlug()` method from the trait creates an URL friendly string from
any given string.

Finally, you need to configure your app to use your slugger. You can declare it
as a default one and declare it for each content type using the content type ID:

```yaml
# config/packages/netgen_layouts_contentful.yaml
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

### Use it

Open again [https://127.0.0.1:8000/nglayouts/admin/layouts](https://127.0.0.1:8000/nglayouts/admin/layouts/)
and start creating Contentful specific layouts and map them to URL targets.
