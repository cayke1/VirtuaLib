<h2>
    <?php echo $title; ?>
</h2>

<ul>
    <?php foreach ($users as $item) { ?>
        <li>
            <?php echo $item['name']; ?> - <?php echo $item['email']; ?>
        </li>
    <?php } ?>
</ul>