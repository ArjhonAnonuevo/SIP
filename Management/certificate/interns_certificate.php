
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/dist/tailwind.min.css" rel="stylesheet">
        <script src="../css/dist/jquery.min.js"></script>
    <title>Uploaded Files</title>
</head>
<body class="bg-gray-100">
<div id = "interns-nav"></div>
    <div class="container mx-auto mt-8">
        <div class="rounded-lg p-8">
            <h1 class="text-3xl md:text-4xl lg:text-3xl font-bold text-center text-transparent text-gray-700 bg-clip-text">
                Certificates
            </h1>

            <div class="mt-8">
            <div id = "display-certificate"></div>
            </div>
        </div>
    </div>
<script>
    $(document).ready(function() {
        $("#interns-nav").load("../header/interns_nav.html");
    });
     $(document).ready(function() {
        $("#display-certificate").load("display.php");
    });
</script>
</body>

</html>
