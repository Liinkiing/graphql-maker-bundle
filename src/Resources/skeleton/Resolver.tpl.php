<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class <?= $class_name ?> implements ResolverInterface
{

    // public function __construct()
    // {
    //     If you wanna inject some service dependencies, do it here
    // }

    public function __invoke(Argument $args)
    {
        // Add your logic on how to fetch your data (e.g database calls).
        // $args can be an object passed in your Query.types.yaml and contains arguments of your query.
    }

}
