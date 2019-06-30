# GraphQL Maker Bundle
Bundle to easily create GraphQL types for [Overblog GraphQL Bundle](https://github.com/overblog/GraphQLBundle) by using the new [Symfony Maker component](https://github.com/symfony/maker-bundle)

## Installation

```bash
$ composer require liinkiing/graphql-maker-bundle
```

If you use **Symfony flex**, it will be automatically register under the `bundles.php` file. 
Otherwise, register the bundle manually

```php
// AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Liinkiing\GraphQLMakerBundle\GraphQLMakerBundle(),
        ];

        // ...
    }
}
```

## Configuration
By default, no configuration is needed. It uses **convention over configuration**, but if you wanna customize the behaviour,
you can add a config file `config/packages/dev/graphql_maker.yaml` :

```yaml
graphql_maker:
  root_namespace: App\GraphQL # Customize the root namespace where PHP mutations and resolver will be
  schemas: # You can also define, for any schemas if you use many, a custom out directory for types files
    public:
      out_dir: '%kernel.project_dir%/config/graphql/public/types'
    internal:
      out_dir: '%kernel.project_dir%/config/graphql/internal/types'
    preview:
      out_dir: '%kernel.project_dir%/config/graphql/preview/types'
```

## Usage
Currently, you can generate:
- type
- connection
- query
- mutation

```bash
$ bin/console make:graphql:type       [--schema]
$ bin/console make:graphql:connection [--schema]
$ bin/console make:graphql:query      [--schema]
$ bin/console make:graphql:mutation   [--schema]
$ bin/console make:graphql:resolver
```

Then, you will be asked some questions to generate what you asked, *à la Maker*
