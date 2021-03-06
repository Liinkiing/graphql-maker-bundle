      <?= $mutationName ?>:<?= "\n" ?>
        description: '<?= $description ?? '' ?>'<?= "\n" ?>
<?php if ($hasAccess && $access) { ?>
        access: "<?= $access ?>"<?= "\n" ?>
<?php } ?>
        builder: 'Relay::Mutation'<?= "\n" ?>
        builderConfig:<?= "\n" ?>
          inputType: <?= $inputName ?><?= "\n" ?>
          payloadType: <?= $payloadName ?><?= "\n" ?>
          mutateAndGetPayload: '@=mutation("<?= str_replace('\\', '\\\\', $rootNamespace) ?>\\Mutation\\<?= ucfirst($mutationName).'Mutation' ?>", [value])'<?= "\n" ?>
