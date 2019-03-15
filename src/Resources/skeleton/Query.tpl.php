      <?= $name ?>:<?= "\n" ?>
        description: '<?= $description ?? '' ?>'<?= "\n" ?>
<?php if ($type) { ?>
        type: '<?= $type ?><?= ($typeNullable === true) ? '' : '!' ?>'<?= "\n" ?>
<?php } ?>
        # If you want to use a Relay connection<?= "\n" ?>
        # argsBuilder: Relay::Connection<?= "\n" ?>
        # resolve: '@=resolver("App\\GraphQL\\Resolver\\Query\\<?= ucfirst($name).'Resolver' ?>", [args])'<?= "\n" ?>
        # args:<?= "\n" ?>
        # Put here your custom args for you query (if any)<?= "\n" ?>
          # first:<?= "\n" ?>
            # type: Int<?= "\n" ?>
            # defaultValue: 30<?= "\n" ?>
