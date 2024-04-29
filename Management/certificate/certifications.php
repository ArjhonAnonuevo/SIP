<!DOCTYPE html>
<html lang="en">

<head>
    <title>Applicants Information</title>
    <link href="../css/dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="../node_modules/toastr/build/toastr.min.css">
    <style>
        /* Animation for modal overlay */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 0.75;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 0.75;
            }

            to {
                opacity: 0;
            }
        }

        /* Animation for modal content */
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateY(0);
                opacity: 1;
            }

            to {
                transform: translateY(-50px);
                opacity: 0;
            }
        }
    </style>
</head>

<body class="overflow-hidden">
    <div id="interns-nav"></div>
    <div class="md:ml-48 xl:ml-48 lg:48">
        <div class="max-w-6xl mx-auto rounded-md">
            <div class="mx-auto md:max-w-7xl md:max-h-min shadow-lg p-6 mt-8 rounded-md max-w-screen-sm">
                <div class="container mx-auto flex flex-col justify-center mt-7">
                    <div class="py-8">
                        <div class="flex flex-col md:flex-row md:justify-between items-center mb-5">
                            <h2 class="text-2xl font-bold mb-6 md:mb-0 md:text-3xl font-kanit">Interns
                                Certificate</h2>
                        </div>
                        <div class="flex justify-end">
                            <button id="addCertificateBtn"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">Add
                                Certificate</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div class="relative">
                                <input type="text"
                                    class="border border-solid border-gray-300 rounded-md p-2 pl-8 w-full md:w-md"
                                    id="searchID" placeholder="Search...." oninput="filterTable()">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-5-5m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-y-auto max-h-96 mt-4">
                            <table class="min-w-full divide-y border-collapse divide-gray-200" id="dataTable">
                                <thead>
                                    <tr class="bg-customGreen text-white">
                                        <th
                                            class="px-4 py-2 md:w-1/6 text-left text-xs font-semibold uppercase tracking-wider font-rubik">
                                            Select</th>
                                        <th
                                            class="px-4 py-2 md:w-1/6 text-left text-xs font-semibold uppercase tracking-wider font-rubik">
                                            Name</th>
                                        <th
                                            class="px-4 py-2 md:w-1/6 text-left text-xs font-semibold uppercase tracking-wider font-rubik">
                                            Department</th>
                                        <th
                                            class="px-4 py-2 md:w-1/6 text-left text-xs font-semibold uppercase tracking-wider font-rubik">
                                            Username</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="modal" class="fixed z-50 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div id="modalOverlay" class="fixed inset-0 bg-gray-500 opacity-0"></div>
            <div id="modalContent"
                class="relative bg-white rounded-lg w-full max-w-md p-8 opacity-0"
                style="animation: slideIn 0.5s ease-in-out forwards;">
                <h3 class="text-lg font-semibold mb-4">Upload Certificate</h3>
                <label for="dropzone-file"
                    class="mx-auto cursor-pointer flex w-full max-w-lg flex-col items-center rounded-xl border-2 border-dashed border-blue-400 bg-white p-6 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <form id="certificate">
                        <h2 id="fileUploadLabel" class="mt-4 text-xl font-medium text-gray-700 tracking-wide">Attendance
                            File</h2>
                        <p id="fileUploadText" class="mt-2 text-gray-500 tracking-wide">Upload or drag & drop your PDF
                            file</p>
                        <input id="dropzone-file" name="attendanceFile" type="file" class="hidden" accept=".pdf"
                            onchange="displayFileName(this)">
                        <input type="hidden" name="user" id="user">
                    </form>
                </label>

                <div class="flex justify-end mt-4">
                    <button id="closeModalBtn"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                        onclick="closeModal()">Close</button>
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded ml-2"
                        onclick="uploadCertificate()">Upload</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../css/dist/jquery.min.js"></script>
    <script src="../node_modules/toastr/build/toastr.min.js"></script>
    <script>
        function closeModal() {
            $('#modalOverlay').css('animation', 'fadeOut 0.5s ease-in-out forwards');
            $('#modalContent').css('animation', 'slideOut 0.5s ease-in-out forwards');
            setTimeout(function () {
                $('#modal').addClass('hidden');
                $('body').removeClass('overflow-hidden');
            }, 500);
        }

        function displayFileName(input) {
            var fileName = input.files[0].name;
            $('#fileUploadText').text(fileName);
        }

        function clearCheckboxes() {
            $('input[type="checkbox"]').prop('checked', false);
        }

        function uploadCertificate() {
            var selectedUsernames = [];
            $('input[type="checkbox"]:checked').each(function () {
                var username = $(this).closest('tr').find('td:eq(3)').text();
                selectedUsernames.push(username);
            });

            $('#user').val(selectedUsernames.join(','));

            console.log("Selected Usernames: " + selectedUsernames.join(','));

            $.ajax({
                url: 'uploaded_certificate.php',
                type: 'POST',
                data: new FormData($('#certificate')[0]),
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    console.log("AJAX Success Response: ", response);
                    if (response.success) {
                        toastr.success(response.message, "", {
                            positionClass: "toast-bottom-right"
                        });
                        closeModal();
                        clearCheckboxes();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.log("AJAX Error Response: ", xhr.responseText);
                    toastr.error("Error uploading certificate: " + error);
                }
            });
        }

        function filterTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchID");
            filter = input.value.toUpperCase();
            table = document.getElementById("dataTable");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1]; 
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        $(document).ready(function () {
            $("#interns-nav").load("../header/admin_navs.html", function (responseTxt, statusTxt, xhr) {
                if (statusTxt == "error") {
                    console.log("Error loading navigation: " + xhr.status + ": " + xhr.statusText);
                }
            });

            $('#addCertificateBtn').click(function () {
                $('#modalOverlay').css('animation', 'fadeIn 0.5s ease-in-out forwards');
                $('#modalContent').css('animation', 'slideIn 0.5s ease-in-out forwards');
                $('#modal').removeClass('hidden');
                $('body').addClass('overflow-hidden');
            });

            $.ajax({
                url: "fetch_data.php",
                type: "GET",
                success: function (data) {
                    updateTable(data);
                },
                error: function (xhr, status, error) {
                    console.log("Error fetching data: " + error);
                }
            });

            function updateTable(data) {
                var table = $("#dataTable tbody");
                var tbody = "";

                if (data.length > 0) {
                    data.forEach(function (row) {
                        tbody += "<tr>";
                        tbody += "<td class='px-8 py-4 font-poppins text-center'><input type='checkbox' class=' border-gray-500 rounded-md text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-70'></td>";
                        tbody += "<td class='px-8 py-4 font-poppins text-blue-500'>" + row['name'] + "</td>";
                        tbody += "<td class='uppercase px-8 py-4 font-poppins'>" + row['department'] + "</td>";
                        tbody += "<td class='px-8 py-4 font-poppins'>" + row['username'] + "</td>";
                        tbody += "</tr>";
                    });
                } else {
                    tbody += "<tr><td colspan='4' class='uppercase border px-8 py-4 font-poppins'>No results found.</td></tr>";
                }

                table.html(tbody);
            }
        });
    </script>
</body>

</html>
