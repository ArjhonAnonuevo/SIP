<?php
  session_start();
  include '../configuration/interns_config.php';
  // Call the getDatabaseConfig function
  $config = getDatabaseConfig();
  
  $dbhost = $config['dbhost'];
  $dbuser = $config['dbuser'];
  $dbpass = $config['dbpass'];
  $dbname = $config['dbname'];
  
  $connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
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
  <link href="../css/dist/output.css" rel="stylesheet">
  <script src = "../css/dist/jquery.min.js"></script>
  <script src="https://cdn.tailwindcss.com/3.0.0"></script>
  <title>Accomplishment Report</title>
</head>
<body>
  <div class="md:ml-48 xl:ml-48 lg:48">
    <div id = "navs"></div>
    <div class="flex items-center justify-center w-full mt-5">
      <form action="submit.php" id="submitform" method="post" id="accomplishmentForm">
        <div class="w-ful bg-white rounded-lg shadow-md md:h-auto">
          <div class="md:flex">
            <div class="p-8 md:flex md:flex-row md:space-x-4 md:justify-between">

              <div class="mt-4 w-full md:w-auto md:h-10 flex flex-col">
                <label for="input1" class="mb-2 font-semibold text-gray-900 uppercase">Type</label>
                <input id="input1" name="type" type="text" class="block w-full px-4 py-2 border rounded-lg focus:ring focus:border-blue-500 transition duration-500 ease-in-out transform focus:scale-105" placeholder="Enter text" required>
              </div>

              <div class="mt-4 w-full md:w-auto md:h-20 flex flex-col">
                <label for="input2" class="mb-2 font-semibold text-gray-900 uppercase">Date</label>
                <input id="input2" name="date" type="date" class="block w-full px-4 py-2 border rounded-lg focus:ring focus:border-blue-500 transition duration-500 ease-in-out transform focus:scale-105" placeholder="Enter text" required>
              </div>

              <div class="mt-4 w-full md:w-auto md:h-20 flex flex-col">
                <label for="input3" class="mb-2 font-semibold text-gray-900 uppercase">Time</label>
                <input id="input3" name="time" type="time" class="block w-full px-4 py-2 border rounded-lg focus:ring focus:border-blue-500 transition duration-500 ease-in-out transform focus:scale-105" placeholder="Enter text" required>
              </div>

              <div class="mt-4 w-full md:w-auto md:h-10 flex flex-col">
                <label for="input4" class="mb-2 font-semibold text-gray-900 uppercase">Status</label>
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
    <h3 class="flex justify-center text-lg font-bold uppercase text-gray-900" style="font-family: 'Enriqueta';">Daily Activities</h3>
    <form action="generate.php" method="post">
      <div class=" max-w-3xl mx-auto p-8 ">
        <select id="month" name="month" onchange="checkMonthSelection(this)" class=" w-full px-4 py-2 border rounded-lg shadow-sm mb-3 sticky top-0 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" style='font-family: Maitree, sans-serif;'>
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
        <div class="h-auto w-md" id="tableContainer">
        </div>
        <div class="text-center mt-5">
          <button type="submit" name="generate_pdf" class="md:w-22 text-white uppercase font-bold bg-yellow-500 hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center me-2 [dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring]-blue-800 md:mb-12 relative">Download</button>
        </form>
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
  </body>
</html>