<?php

namespace Liinkiing\GraphQLMakerBundle;

use Liinkiing\GraphQLMakerBundle\DependencyInjection\GraphQLMakerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Omar Jbara <omar.jbara2@gmail.com>
 */
class GraphQLMakerBundle extends Bundle
{

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (!$this->extension instanceof ExtensionInterface) {
            $this->extension = new GraphQLMakerExtension();
        }

        return $this->extension;
    }

}
