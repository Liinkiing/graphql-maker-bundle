<?php


namespace Liinkiing\GraphQLMakerBundle\Utils;


final class Str
{
    public static function normalizeNamespace(string $value): string
    {
        return str_replace('\\\\', '\\', $value);
    }
}
