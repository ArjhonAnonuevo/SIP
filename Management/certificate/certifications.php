<!DOCTYPE html>
<html lang="en">
<head>
    <title>Applicants Information</title>
    <link href="../css/dist/output.css" rel="stylesheet">
    <script src="../css/dist/jquery.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div id="interns-nav"></div>
    <div class="md:ml-48 xl:ml-48 lg:48">
        <div class="mx-auto md:max-w-7xl md:max-h-min xl:bg-white md:bg-white shadow-md p-6 mt-8 rounded-md max-w-screen-sm">
            <div class="container mx-auto flex flex-col justify-center mt-7">
                <div class="py-8">
                    <h2 class="text-2xl font-bold mb-6 md:mb-0 md:text-3xl font-kanit xl:text-left text-center">Upload Certificate</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                <div class="relative">
                    <input type="text" class="border border-solid border-gray-300 rounded-md p-2 pl-8 w-full md:w-md" id="searchID" placeholder="Search...." oninput="filterTable()">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-5-5m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex gap-4 justify-end">
                    <select id="filterBy1" name="filterBy" class="mt-1 block w-full md:w-sm border border-solid border-gray-300 rounded-md p-2 text-gray-700 font-semibold">
                        <option value="name">Filter By</option>
                        <option value="category">Category</option>
                        <option value="date">Date</option>
                    </select>
                    <select id="filterBy2" name="filterBy" class="mt-1 block w-full md:w-sm border border-solid border-gray-300 rounded-md p-2 text-gray-700 font-semibold">
                        <option value="" data-order="asc">Sort By</option>
                        <option value="category" data-order="asc">Category</option>
                        <option value="date" data-order="asc">Date</option>
                    </select>
                </div>
            </div>
                    <div class="overflow-x-auto mt-4">
                        <table class="min-w-full divide-y border-collapse divide-gray-200" id="dataTable">
                            <thead>
                                <tr class = "bg-green-700 text-white">
                                    <th class="px-4 py-2 md:w-1/6 text-left text-xs font-semibold uppercase tracking-wider font-rubik">Name</th>
                                    <th class="px-4 py-2 md:w-1/6  text-left text-xs font-semibold uppercase tracking-wider font-rubik">Department</th>
                                    <th class="px-4 py-2 md:w-1/6   text-left text-xs font-semibold uppercase tracking-wider font-rubik">Username</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $("#interns-nav").load("../header/admin_navs.html");
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
                var tbody = "";

                if (data.length > 0) {
                    data.forEach(function (row) {
                        tbody += "<tr>";
                        tbody += "<td class=' px-8 py-4 font-poppins'><a href='upload.php?username=" + encodeURIComponent(row['username']) + "' class='text-blue-500'>" + row['name'] + "</a></td>";
                        tbody += "<td class='uppercase px-8 py-4 font-poppins'>" + row['department'] + "</td>";
                        tbody += "<td class='px-8 py-4 font-poppins'>" + row['username'] + "</td>";
                        tbody += "</tr>";
                    });
                } else {
                    tbody += "<tr><td colspan='3' class='uppercase border px-8 py-4 font-poppins'>No results found.</td></tr>";
                }

                table.getElementsByTagName('tbody')[0].innerHTML = tbody;
            }
        });
    </script>
</body>
</html>
