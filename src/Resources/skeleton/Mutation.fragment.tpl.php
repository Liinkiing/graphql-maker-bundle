      <?= $mutationName ?>:<?= "\n" ?>
        description: '<?= $description ?? '' ?>'<?= "\n" ?>
<?php if ($hasAccess && $access) { ?>
        access: "<?= $access ?>"<?= "\n" ?>
<?php } ?>
        builder: 'Relay::Mutation'<?= "\n" ?>
        builderConfig:<?= "\n" ?>
          inputType: <?= $inputName ?><?= "\n" ?>
          payloadType: <?= $payloadName ?><?= "\n" ?>
          mutateAndGetPayload: '@=mutation("<?= $rootNamespace ?>\\Mutation\\<?= ucfirst($mutationName).'Mutation' ?>", [value])'<?= "\n" ?>
