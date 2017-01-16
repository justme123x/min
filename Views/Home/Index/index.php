<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<h1><?=$title?></h1>
<table border="0.5" cellpadding="0" cellspacing="0">
    <?php foreach($config_list as $config) : ?>
    <tr>
        <td><?=$config['config_id']?></td>
        <td><?=$config['config_field']?></td>
        <td><?=$config['config_name']?></td>
        <td><?=$config['config_value']?></td>
        <td><?=$config['config_comment']?></td>
    </tr>
    <?php endforeach;?>
</table>
</body>
</html>