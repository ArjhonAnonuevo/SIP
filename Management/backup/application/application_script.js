function modalOpen(id) {
  document.getElementById(id).style.display = 'flex';
}

function modalClose(id) {
  document.getElementById(id).style.display = 'none';
}

function discardMessage() {
  document.getElementById('messageTextArea').value = '';
}

document.querySelectorAll('[name="applicant_checkbox[]"]').forEach(function (checkbox) {
  checkbox.addEventListener('change', function () {
    var recipientsTextarea = document.getElementById('recipients');
    if (this.checked) {
      recipientsTextarea.value += this.value + '\n';
    } else {
      var lines = recipientsTextarea.value.split('\n');
      var index = lines.indexOf(this.value);
      if (index > -1) {
        lines.splice(index, 1);
      }
      recipientsTextarea.value = lines.join('\n');
    }

    // Update hidden_email_input value with comma-separated email addresses
    var hiddenEmailInput = document.getElementById('hidden_email_input');
    hiddenEmailInput.value = recipientsTextarea.value.replace(/\n/g, ',');
  });
});

document.addEventListener('DOMContentLoaded', function () {
  var checkboxes = document.querySelectorAll('[name="applicant_checkbox[]"]');
  var hiddenInput = document.getElementById('hidden_email_input');

  checkboxes.forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
      // Get an array of values from all checked checkboxes
      var checkedValues = Array.from(checkboxes)
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.value);

      // Set the hidden input value to the concatenated values
      hiddenInput.value = checkedValues.join(',');
    });
  });
});

// Existing filterTable function
function filterTable() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("searchID");
  filter = input.value.toUpperCase();
  table = document.querySelector(".min-w-full");
  tr = table.getElementsByTagName("tr");

  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td");
    for (var j = 0; j < td.length; j++) {
      if (td[j]) {
        txtValue = td[j].textContent || td[j].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
          tr[i].style.display = "";
          break;
        } else {
          tr[i].style.display = "none";
        }
      }
    }
  }
}

// New sortTable function
function sortTable(n, asc = true) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.querySelector(".min-w-full");
  switching = true;
  dir = asc ? "asc" : "desc";

  while (switching) {
    switching = false;
    rows = table.rows;
    for (i = 1; i < (rows.length - 1); i++) {
      shouldSwitch = false;
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];

      if (dir == "asc") {
        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
          shouldSwitch = true;
          break;
        }
      } else if (dir == "desc") {
        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
          shouldSwitch = true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      switchcount++;
    } else {
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}

// Event listeners for filter and sort dropdowns
document.getElementById("filterBy1").addEventListener("change", function () {
  filterTable();
});

document.getElementById("filterBy2").addEventListener("change", function () {
  var columnIndex = this.selectedIndex - 1;
  sortTable(columnIndex, this.options[this.selectedIndex].getAttribute('data-order') !== 'desc');
});
