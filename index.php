<!DOCTYPE html>
<?php
include('includes/session.php');
include('includes/config.php');
include('includes/db.php');
include('includes/env.php');
?>
<html lang="<?= $_ENV['language'] ?>">
    <head>
        <title>tempo</title>
        <meta charset="utf-8" />
        <?php include('includes/head.php'); ?>
    </head>
    <body>
        <header id="page-header" class="page-header">
            <section>
                <?php include('includes/header.php'); ?>
            </section>
        </header>
        <main id="page-main" class="page-main">
            <section>
                <?php include('includes/main/timesheet.php'); ?>
            </section>
        </main>
        <footer id="page-footer" class="page-footer">
            <section>
                <?php include('includes/footer.php'); ?>
            </section>
        </footer>
        <?php include('includes/scripts.php'); ?>
    </body>
</html>
