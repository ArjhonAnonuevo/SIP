function updateTables() {
  var selectedMonth = document.getElementById("month").value;

  // Make an AJAX request to fetch tables for the selected month
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
          // Update the table container with the received HTML
          document.getElementById("tableContainer").innerHTML = this.responseText;
      }
  };
  xhttp.open("GET", "get_tables.php?month=" + selectedMonth, true);
  xhttp.send();
}

// Attach the updateTables function to the change event of the month dropdown
document.getElementById("month").addEventListener("change", updateTables);

// Initial call to load tables for the default selected month
updateTables();
function checkMonthSelection(selectElement) {
  if (selectElement.value === '') {
    // Display a warning message or prevent form submission
    alert('Please select a valid month');
    // If you want to prevent form submission, you can use the following line
    document.getElementById('accomplishmentForm').reset();
  }
}
var selectedMonth;

function checkMonthSelection(selectElement) {
    selectedMonth = selectElement.value;
    console.log("Selected Month: " + selectedMonth);
}
