$(document).ready(function () {
  $('#control-number').on('input', function () {
    var controlNumber = $(this).val();
    if (controlNumber !== '') {
      // AJAX request to fetch user data
      $.ajax({
        url: 'php_scripts/fetch_user.php',
        method: 'GET',
        dataType: 'json',
        data: { control_number: controlNumber },
        success: function (data) {
          // Populate the input fields with fetched data
          $('#fname').val(data.given_name);
          $('#mname').val(data.middle_name);
          $('#lname').val(data.family_name);
          $('#age').val(data.age);
          $('#birthday').val(data.birthday);
          $('#contact-number').val(data.contact);
          $('#school').val(data.school);
          $('#course').val(data.course);
          $('input[name="gender"][value="' + data.gender + '"]').prop('checked', true);
          $('#department').val(data.department);
          $('#hours-required').val(data.required_hours);
          $('#emergency-contact').val(data.emergency_contact);
          $('#email').val(data.primary_email);


        },
        error: function (xhr, status, error) {
          console.error('Error:', xhr, status, error);
        }
      });

      $.ajax({
        url: 'php_scripts/generate_account.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
          $('#username').val(data.new_username);
          $('#password').val('qwerty');
          $('#confirm-password').val('qwerty');
        },
        error: function (xhr, status, error) {
          console.error('Error:', xhr, status, error);
        }
      });
    } else {
      $('#fname').val('');
      $('#mname').val('');
      $('#lname').val('');
      $('#age').val('');
      $('#birthday').val('');
      $('#contact-number').val('');
      $('#email').val('');
      $('#school').val('');
      $('#course').val('');
      $('#username').val('');
      $('#password').val('');
      $('#confirm-password').val('');
    }
  });

  const modal = document.getElementById('modal');
  const video = document.getElementById('video');
  const captureBtn = document.getElementById('captureBtn');
  const closeBtn = document.getElementById('closeBtn'); // Adding reference to close button
  const captureCountElement = document.getElementById('captureCount');
  let captureCount = 3;
  let capturedImages = [];

  // Function to open camera modal
  function openModal() {
    modal.classList.remove('hidden');
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => {
        video.srcObject = stream;
      })
      .catch(err => {
        console.error('Error accessing camera:', err);
      });
  }

  // Function to close the camera modal
  function closeModal() {
    if (captureCount === 0) { // Check if captures are completed
      modal.classList.add('hidden');
      backdrop.classList.add('hidden');
      video.srcObject.getTracks().forEach(track => {
        track.stop();
      });
      captureCount = 3;
      captureCountElement.textContent = `Captures left: ${captureCount}`;
      console.log(capturedImages);
    }
  }

  // Function to capture image
  captureBtn.addEventListener('click', () => {
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    context.translate(canvas.width, 0);
    context.scale(-1, 1);

    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = canvas.toDataURL('image/png');

    // Add the captured image data to the array
    capturedImages.push(imageData);

    captureCount--;
    captureCountElement.textContent = `Captures left: ${captureCount}`;

    if (captureCount === 0) {
      // Disable close button until captures are completed
      closeBtn.disabled = true;
    }

    if (captureCount <= 0) {
      closeModal();
    }
  });

  document.getElementById('openModal').addEventListener('click', openModal);
  document.getElementById('closeBtn').addEventListener('click', closeModal);


  const formPages = document.querySelectorAll(".form-page");
  const nextButtons = document.querySelectorAll("[data-next]");
  const prevButtons = document.querySelectorAll("[data-prev]");
  nextButtons.forEach((button) => {
    button.addEventListener("click", function (event) {
      event.preventDefault();
      const currentPageId = this.getAttribute("data-current");
      const nextPageId = this.getAttribute("data-next");
      showPage(currentPageId, nextPageId);
    });
  });
  prevButtons.forEach((button) => {
    button.addEventListener("click", function (event) {
      event.preventDefault();
      const currentPageId = this.getAttribute("data-current");
      const prevPageId = this.getAttribute("data-prev");
      showPage(currentPageId, prevPageId);
    });
  });

  // Function to show a specific form page
  function showPage(currentPageId, nextPageId) {
    const currentPage = document.getElementById(currentPageId);
    const nextPage = document.getElementById(nextPageId);
    if (currentPage && nextPage) {
      currentPage.classList.add("hidden");
      nextPage.classList.remove("hidden");
    }
  }

  // Show the initial form page
  showPage("page-1", "page-1");

  function calculateAge() {
    var birthday = new Date(document.getElementById("birthday").value);
    var today = new Date();
    var age = today.getFullYear() - birthday.getFullYear();
    var monthDiff = today.getMonth() - birthday.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
      age--;
    }
    document.getElementById("age").value = age;
  }

  function validateSchoolName() {
    var schoolInput = document.getElementById('school').value.trim();
    var schoolWords = schoolInput.split(' ');

    // Exempted words
    var exemptedWords = ['of', 'the', 'and', 'or', 'in', 'for', 'de'];

    // Check if all words start with capital letters and are not acronyms (except for exempted words)
    var isValid = schoolWords.every(word => {
      if (exemptedWords.includes(word.toLowerCase())) {
        return true; // Skip validation for exempted words
      }
      return /^[A-Z][a-z]*$/.test(word);
    });

    // Display validation message
    var validationMessage = document.getElementById('schoolValidationMessage');
    var validSchoolMessage = document.getElementById('validSchoolMessage');
    if (!isValid || schoolWords.length < 2) {
      validationMessage.classList.remove('hidden');
      validSchoolMessage.classList.add('hidden');
      return false;
    } else {
      validationMessage.classList.add('hidden');
      validSchoolMessage.classList.remove('hidden');
      return true;
    }
  }

  // Function to clear validation messages
  function clearValidationMessages() {
    document.getElementById('schoolValidationMessage').classList.add('hidden');
    document.getElementById('validSchoolMessage').classList.add('hidden');
  }

  // Add event listener to validate school name on input change
  document.getElementById('school').addEventListener('input', function () {
    validateSchoolName();
    // If input is cleared, clear validation messages
    if (this.value.trim() === '') {
      clearValidationMessages();
    }
  });

  // Add event listener to form submission
  document.getElementById('registrationForm').addEventListener('submit', function (event) {
    event.preventDefault();

    // Serialize form data
    var formData = $(this).serializeArray();

    // Add capturedImages array to formData
    formData.push({ name: 'capturedImages', value: JSON.stringify(capturedImages) });

    $.ajax({
      url: 'php_scripts/register_process.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: response.message,
            text: 'Your password is: qwerty',
            onClose: function () {
              window.location.href = "index.html";
            }
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: response.error
          });
        }
      },
      error: function (xhr, status, error) {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'An error occurred while processing your request.'
        });
      }
    });
});
});