      <?= $name ?>:<?= "\n" ?>
        description: '<?= $description ?? '' ?>'<?= "\n" ?>
<?php if ($hasAccess && $access) { ?>
        access: "<?= $access ?>"<?= "\n" ?>
<?php } ?>
<?php if (!$hasAccess || !$access) { ?>
        # access: "@=hasRole('ROLE_ADMIN')"<?= "\n" ?>
<?php } ?>
        builder: 'Relay::Mutation'<?= "\n" ?>
        builderConfig:<?= "\n" ?>
          inputType: <?= ucfirst($name).'Input' ?><?= "\n" ?>
          payloadType: <?= ucfirst($name).'Payload' ?><?= "\n" ?>
          mutateAndGetPayload: '@=mutation("App\\GraphQL\\Mutation\\<?= ucfirst($name).'Mutation' ?>", [value])'<?= "\n" ?>
