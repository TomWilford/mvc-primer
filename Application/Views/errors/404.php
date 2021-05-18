<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">

    <title>MVC Primer | Just Like Pro PHP MVC</title>

    <link rel="stylesheet" href="/public/styles/style.css">
</head>
<body>
    <header>
        <h1>MVC Primer</h1>
        <p><i>Just Like Pro PHP MVC</i></p>
    </header>

    <div class="body">
    <h2>404 Error</h2>
    <p>The requested page could not be found.</p>
    <?php if (DEBUG) { ?>
        <pre><?php print_r($e) ?></pre>
    <?php } ?>
    </div>
</body>
</html>