<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certifications Page</title>
</head>
<body>

<div id="certifications-container" class='grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3'>
</div>
<script>
    // AJAX to fetch and display certifications
    document.addEventListener("DOMContentLoaded", function () {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var certificationsContainer = document.getElementById("certifications-container");
                certificationsContainer.innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "fetch_certifications.php", true);
        xmlhttp.send();
    });
</script>

</body>
</html>
