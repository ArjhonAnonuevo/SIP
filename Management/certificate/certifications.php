<!DOCTYPE html>
<html>
<head>
    <title>Applicants Information</title>
    <link href="../css/dist/tailwind.min.css" rel="stylesheet">
    <script src = "../css/dist/jquery.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div id = "interns-nav"></div>
<div class="md:ml-48 xl:ml-48 lg:48">
    <div class="container mx-auto flex flex-col justify-center mt-7">
        <div class="py-8">
            <h2 class="text-2xl font-bold font-serif">Upload Certificate</h2>
            <div class="search-container">
                <input type="text" id="searchInput" class="px-8 py-3" placeholder="Search...">
            </div>
            <div class="overflow-x-auto mt-4">
                <table class="table-auto w-full" id="dataTable">
                </table>
            </div>
        </div>
    </div>
</div>

<script>
 $(document).ready(function() {
        $("#interns-nav").load("../header/interns_nav.html");
    });
    document.addEventListener("DOMContentLoaded", function () {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var data = JSON.parse(this.responseText);
                updateTable(data);
            }
        };
        xhttp.open("GET", "fetch_data.php", true);
        xhttp.send();

        function updateTable(data) {
            var table = document.getElementById("dataTable");
            var tbody = document.createElement("tbody");

            if (data.length > 0) {
                data.forEach(function (row) {
                    var tr = document.createElement("tr");

                    var tdName = document.createElement("td");
                    tdName.className = "border px-8 py-4 font-poppins";
                    var link = document.createElement("a");
                    link.href = 'upload.php?username=' + encodeURIComponent(row['username']);
                    link.className = "text-blue-500";
                    link.textContent = row['name'];
                    tdName.appendChild(link);

                    var tdDepartment = document.createElement("td");
                    tdDepartment.className = "uppercase border px-8 py-4 font-poppins";
                    tdDepartment.textContent = row['department'];

                    var tdUsername = document.createElement("td");
                    tdUsername.className = "border px-8 py-4 font-poppins";
                    tdUsername.textContent = row['username'];

                    tr.appendChild(tdName);
                    tr.appendChild(tdDepartment);
                    tr.appendChild(tdUsername);

                    tbody.appendChild(tr);
                });
            } else {
                var tr = document.createElement("tr");
                var tdEmpty = document.createElement("td");
                tdEmpty.colSpan = 3;
                tdEmpty.className = "uppercase border px-8 py-4 font-poppins";
                tdEmpty.textContent = "No results found.";
                tr.appendChild(tdEmpty);
                tbody.appendChild(tr);
            }

            table.innerHTML = ""; 
            table.appendChild(tbody);
        }
    });
</script>
</body>
</html>
