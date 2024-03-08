<?php
  session_start();
  include '../configuration/interns_config.php';

            // Call the getDatabaseConfig function
            $config = getDatabaseConfig();
            
            $dbhost = $config['dbhost'];
            $dbuser = $config['dbuser'];
            $dbpass = $config['dbpass'];
            $dbname = $config['dbname'];
  $username = isset($_GET["username"]) ? $_GET["username"] : (isset($_SESSION["username"]) ? $_SESSION["username"] : "Unknown");
  include "../header/interns_navs.php";
  $connection = new mysqli($dbhost, $db_ser, $dbpass, $dbname);
  if ($connection->connect_error) {
      die("Connection failed: " . $connection->connect_error);
  }
  $selectedMonth = isset($_GET["month"]) ? $_GET["month"] : date('m');
  $stmt = $connection->prepare("SELECT * FROM acomplisment_report WHERE user_id = ? AND MONTH(date) = ?");
  $stmt->bind_param("ss", $username, $selectedMonth);
  $stmt->execute();
  $result = $stmt->get_result();
  $lastDate = "";
  ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href=" ../css/dist/tailwind.min.css" rel="stylesheet">
    <title>Accomplishment Report</title>
  </head>
  <body>
    <div class="md:ml-48 xl:ml-48 lg:48">
      <div class="flex items-center justify-center w-full mt-5 sm:flex-col sm:items-start sm:justify-start md:flex-row md:items-center md:justify-center">
        <form action="submit.php" id="submitform" method="post" id="accomplishmentForm">
          <div class="max-w-md bg-gradient-to-r from-green-800 to-teal-900 rounded-lg shadow-md overflow-hidden md:max-w-7xl md:h-auto">
            <div class="md:flex">
              <div class="p-8 md:flex md:flex-row md:space-x-4 md:justify-between">

                <!-- First Input -->
                <div class="mt-4 w-full md:w-auto md:h-10 flex flex-col">
                  <label for="input1" class="mb-2 font-semibold text-gray-200 uppercase">Type</label>
                  <input id="input1" name="type" type="text" class="block w-full px-4 py-2 border rounded-lg focus:ring focus:border-blue-500 transition duration-500 ease-in-out transform focus:scale-105" placeholder="Enter text" required>
                </div>

                <!-- Second Input -->
                <div class="mt-4 w-full md:w-auto md:h-20 flex flex-col">
                  <label for="input2" class="mb-2 font-semibold text-gray-200 uppercase">Date</label>
                  <input id="input2" name="date" type="date" class="block w-full px-4 py-2 border rounded-lg focus:ring focus:border-blue-500 transition duration-500 ease-in-out transform focus:scale-105" placeholder="Enter text" required>
                </div>

                <!-- Third Input -->
                <div class="mt-4 w-full md:w-auto md:h-20 flex flex-col">
                  <label for="input3" class="mb-2 font-semibold text-gray-200 uppercase">Time</label>
                  <input id="input3" name="time" type="time" class="block w-full px-4 py-2 border rounded-lg focus:ring focus:border-blue-500 transition duration-500 ease-in-out transform focus:scale-105" placeholder="Enter text" required>
                </div>

                <!-- Fourth Input -->
                <div class="mt-4 w-full md:w-auto md:h-10 flex flex-col">
                  <label for="input4" class="mb-2 font-semibold text-gray-200 uppercase">Status</label>
                  <select id="input4" name="status" class="block w-full px-4 py-2 border rounded-lg focus:ring focus:border-blue-500 transition duration-500 ease-in-out transform focus:scale-105" required>
                    <option value="" disabled selected>Choose</option>
                    <option value="Work Done">Work Done</option>
                    <option value="Not Complete">Not complete</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="flex mb-4 float-right mr-10">
              <button type="submit" name="save" class="md:w-21 text-white uppercase bg-yellow-500 hover:bg-yellow-600 focus:ring-4 font-bold focus:outline-none focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center me-2 [dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring]-blue-800 md:mb-12 relative">Save</button>
            </div>
          </div>
        </form>
      </div>
      <div class="flex items-center justify-center mt-5 sm:flex-col sm:items-start sm:justify-start md:flex-row md:items-center md:justify-center">
        <section class="grid md:grid-cols-1 xl:grid-cols-2 gap-5">
          <div class="w-90 h-auto bg-gradient-to-r from-green-800 to-teal-900 rounded-xl shadow-md sm:max-w-lg md:w-lg pt-4 sm:pt-8 md:pt-12 relative flex flex-col justify-between items-center">
            <div class="md:flex md:flex-col xl:items-center">
              <div class="p-8 md:flex md:flex-col md:space-y-4 md:justify-between">
                <h3 class="flex justify-center text-lg font-bold text-gray-200 uppercase" style="font-family: 'Enriqueta';">Uploaded Files</h3>
              </div>
            </div>
            <button onclick="modalOpen('myModal')" type="button" class="md:w-21 text-white uppercase bg-yellow-500 hover:bg-yellow-600 focus:ring-4 font-bold focus:outline-none focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center me-2 [dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring]-blue-800 md:mb-12 relative">
              <span class="mr-2">Upload</span>
            </button>
            <div id="myModal" class="fixed inset-0  items-center justify-center z-50 bg-black bg-opacity-50 hidden" onclick="closeModal()">
              <div class="transition duration-500 ease-in-out border border-gray-300 rounded bg-blue-200 p-6 shadow-lg max-w-lg mx-auto">
                <div class="flex justify-between items-center pb-3">
                  <p class="text-3xl font-bold text-green-700 p-4 m-4">Upload Monthly Accomplishment Report</p>
                  <div class="cursor-pointer z-50" onclick="modalClose('myModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <line x1="18" y1="6" x2="6" y2="18" />
                      <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                  </div>
                </div>
                <div id="drop-area" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-400 shadow-lg md:text-white">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 10a2 2 0 01-2-2H7a2 2 0 01-2 2v10a2 2 0 012 2h10a2 2 0 012-2z" />
                  </svg>
                  <p class="mt-3 text-base md:text-white">Drag & Drop files here or click to upload</p>
                  <input class="" type="file" id="file-input" multiple class="hidden" />
                </div>
                <div class="footer md:mt-4">
                  <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Save
                  </button>
                  <button class="ml-3 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="modalClose('myModal')">
                    Cancel
                  </button>
                </div>
              </div>>
            </div>
          </div>
          <div class="max-w-lg bg-gradient-to-r from-green-800 to-teal-900 rounded-xl shadow-md sm:max-w-lg md:max-w-7xl pt-4 sm:pt-8 md:pt-12">
            <div class="md:flex">
              <div class="p-8 md:flex md:flex-col md:space-y-4 md:justify-between">
                <h3 class="flex justify-center text-lg font-bold uppercase text-gray-200" style="font-family: 'Enriqueta';">Daily Activities</h3>
                <form action="generate.php" method="post">
                  <select id="month" name="month" onchange="checkMonthSelection(this)" class="w-full px-4 py-2 border rounded-lg shadow-sm mb-3 sticky top-0 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" style='font-family: Maitree, sans-serif;'>
                    <option value="">Select Month</option>
                    <?php
                    $selected_month = date('m'); //current month
                    for ($i_month = 1; $i_month <= 12; $i_month++) {
                        $timestamp = mktime(0, 0, 0, $i_month, 1); // Create a timestamp for the first day of the month
                        $selected = $selected_month == $i_month ? ' selected' : '';
                        echo '<option value="' . $i_month . '"' . $selected . '>(' . $i_month . ') ' . date('F', $timestamp) . '</option>' . "\n";
                    }
                    ?>
                  </select>
                  <div class="overflow-y-auto h-56 w-md " id="tableContainer">
                  </div>
                  <div class="md:flex md:items-center md:justify-center sm:flex sm:justify-center sm:mt-4 sm:mb-4">
                    <button type="submit" name="generate_pdf" class="md:w-22 text-white uppercase font-bold bg-yellow-500 hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center me-2 [dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring]-blue-800 md:mb-12 relative">Download</button>
                </form>
              </div>
            </div>
          </div>
      </div>
    </div>
    <script src="script.js"></script>
    <script>
      function modalOpen(id) {
        document.getElementById(id).style.display = 'flex';
      }
      function modalClose(id) {
        document.getElementById(id).style.display = 'none';
      }
      document.getElementById('drop-area').addEventListener('click', function() {
        document.getElementById('file-input').click();
      });
      function updateFileNames() {
        var input = document.getElementById('file-input');
        var output = document.getElementById('file-names');
        var files = input.files;
        var fileNames = [];
        for (var i = 0; i < files.length; i++) {
          fileNames.push(files[i].name);
        }
        output.textContent = 'Selected files: ' + fileNames.join(', ');
      }
    </script>

    <!-- <?php
        // include "../tailwind/footers.html";?> -->
  </body>
</html>