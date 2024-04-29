document.addEventListener("DOMContentLoaded", function() {
  // Get all form pages
  const formPages = document.querySelectorAll(".form-page");
  // Get all "Next" buttons
  const nextButtons = document.querySelectorAll("[data-next]");
  // Get all "Previous" buttons
  const prevButtons = document.querySelectorAll("[data-prev]");
  // Add click event listeners to "Next" buttons
  nextButtons.forEach((button) => {
    button.addEventListener("click", function(event) {
      event.preventDefault();
      const currentPageId = this.getAttribute("data-current");
      const nextPageId = this.getAttribute("data-next");
      showPage(currentPageId, nextPageId);
    });
  });
  prevButtons.forEach((button) => {
    button.addEventListener("click", function(event) {
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
});

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
  var exemptedWords = ['of', 'the', 'and', 'or', 'in', 'for' ,'de']; 

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

document.getElementById('school').addEventListener('input', function() {
  validateSchoolName();
  // If input is cleared, clear validation messages
  if (this.value.trim() === '') {
      clearValidationMessages();
  }
});


document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById('modal');
  const video = document.getElementById('video');
  const captureBtn = document.getElementById('captureBtn');
  const captureCountElement = document.getElementById('captureCount');
  let captureCount = 3
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

  // Function to capture image
  captureBtn.addEventListener('click', () => {
      const canvas = document.createElement('canvas');
      const context = canvas.getContext('2d');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      context.drawImage(video, 0, 0, canvas.width, canvas.height);
      const imageData = canvas.toDataURL('image/png');

      // Add the captured image data to the array
      capturedImages.push(imageData);

      captureCount--;
      captureCountElement.textContent = `Captures left: ${captureCount}`;

      if (captureCount <= 0) {
          closeModal();
      }
  });

  function closeModal() {
      modal.classList.add('hidden');
      video.srcObject.getTracks().forEach(track => {
          track.stop();
      });
      captureCount = 3; // Reset capture count
      captureCountElement.textContent = `Captures left: ${captureCount}`;

      console.log(capturedImages);
  }

  document.getElementById('openModal').addEventListener('click', openModal);
});

// Add event listener to form submission
document.getElementById('registrationForm').addEventListener('submit', function(event) {
  event.preventDefault(); 

  $.ajax({
    url: 'php_scripts/register_process.php',
    type: 'POST',
    data: $(this).serialize(), 
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        alert(response.message);
        window.location.href = "index.html"; // Redirect to index.html
      } else {
        alert(response.error); 
      }
    },
    error: function(xhr, status, error) {
      alert('An error occurred while processing your request.'); 
    }
  });
});