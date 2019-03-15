      <?= $mutationName ?>:<?= "\n" ?>
        description: '<?= $description ?? '' ?>'<?= "\n" ?>
<?php if ($hasAccess && $access) { ?>
        access: "<?= $access ?>"<?= "\n" ?>
<?php } ?>
<?php if (!$hasAccess || !$access) { ?>
        # access: "@=hasRole('ROLE_ADMIN')"<?= "\n" ?>
<?php } ?>
        builder: 'Relay::Mutation'<?= "\n" ?>
        builderConfig:<?= "\n" ?>
          inputType: <?= $inputName ?><?= "\n" ?>
          payloadType: <?= $payloadName ?><?= "\n" ?>
          mutateAndGetPayload: '@=mutation("App\\GraphQL\\Mutation\\<?= ucfirst($mutationName).'Mutation' ?>", [value])'<?= "\n" ?>
