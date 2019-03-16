<?php foreach ($fields as $field) { ?>
      <?= $field['name'] ?>:<?= "\n" ?>
        type: '<?= $field['type'] ?><?= ($field['nullable'] === true) ? '' : '!' ?>'<?= "\n" ?>
        description: <?= "'" . $field['description'] . "'" ?? '~' ?><?= "\n" ?>
<?php } ?>
