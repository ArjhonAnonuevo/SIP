$(document).ready(function() {
    // Function to close modal
    function closeModal() {
        $('#composeEmailModal').addClass('hidden');
    }

    // Function to open modal
    function openModal(interviewType, primaryEmail, controlNumber) {
        $('#selectedInterviewValue').val(interviewType);
        $('#primaryEmail').val(primaryEmail);
        $('#controlNumber').val(controlNumber);
        $('#composeEmailModal').removeClass('hidden');

        // Show or hide additional divs based on selected interview type
        if (interviewType === 'interview1') {
            $('#additionalFields').show();
            $('#additionalFields input').attr('required', 'required');
            $('#interview2Fields').hide(); // Hide interview2 fields
            $('#interview2Fields input').removeAttr('required');
            // Clear input values for interview2
            $('#int2Date').val('');
            $('#int2Time').val('');
            $('#department').val('');
        } else if (interviewType === 'interview2') {
            $('#interview2Fields').show();
            $('#interview2Fields input').attr('required', 'required');
            $('#additionalFields').hide(); // Hide additional fields
            $('#additionalFields input').removeAttr('required');
            // Clear input values for interview1
            $('#date').val('');
            $('#startTime').val('');
            $('#endTime').val('');
            $('#url').val('');
            $('#m_id').val('');
            $('#passcode').val('');
        } else if (interviewType === 'interview3') {
            $('#interview2Fields').hide(); // Hide interview2 fields
            $('#additionalFields').hide(); // Hide additional fields
            $('#additionalFields input').removeAttr('required');
            $('#interview2Fields input').removeAttr('required');
            // Clear input values for both interview1 and interview2
            $('#date').val('');
            $('#startTime').val('');
            $('#endTime').val('');
            $('#url').val('');
            $('#m_id').val('');
            $('#passcode').val('');
            $('#int2Date').val('');
            $('#int2Time').val('');
            $('#department').val('');
        } else if (interviewType === 'rejected') {
            $('#interview2Fields').hide(); // Hide interview2 fields
            $('#additionalFields').hide(); // Hide additional fields
            $('#additionalFields input').removeAttr('required');
            $('#interview2Fields input').removeAttr('required');
            // Clear input values for both interview1 and interview2
            $('#date').val('');
            $('#startTime').val('');
            $('#endTime').val('');
            $('#url').val('');
            $('#m_id').val('');
            $('#passcode').val('');
            $('#int2Date').val('');
            $('#int2Time').val('');
            $('#department').val('');
        }

        // Disable the selected option
        $(`.composeEmailButton option[value='${interviewType}']`).prop('disabled', true);
    }

    function fetchTemplates() {
        $("#display-loader").load("../loaders.html");
    }

    fetchTemplates();

    // Update modal content when #selectedInterviewValue changes
    $('#selectedInterviewValue').on('input', function() {
        var selectedInterview = $(this).val();
        $('#selectedInterviewText').text(selectedInterview);
    });

    // Event delegation for elements with class .composeEmailButton
    $(document).on('change', '.composeEmailButton', function() {
        var selectedInterview = $(this).val();
        var primaryEmail = $(this).data('primary-email');
        var controlNumber = $(this).data('control-number');

        // Check if selected value is valid
        if (['interview1', 'interview2', 'interview3', 'rejected'].includes(selectedInterview)) {
            openModal(selectedInterview, primaryEmail, controlNumber);
        } else {
            closeModal();
        }
    });

    // Event listener for closing modal
    $('#closeModal').click(closeModal);

    // Event listener for file input change
    $('.file-input__field').change(function() {
        var files = $(this)[0].files;
        var fileNames = [];
        for (var i = 0; i < files.length; i++) {
            fileNames.push(files[i].name);
        }
        $('.file-name').text(fileNames.join(', '));
    });

    // Function to send form data via AJAX
function sendFormData(formData) {
    $.ajax({
        url: '../send-emails/send-emails.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#loader').show();
        },
        success: function(response) {
            $('#loader').hide(); // Hide loader on success
            toastr.success('Email sent successfully!', '', {
                positionClass: 'toast-bottom-right'
            });
            $('#composeEmailModal').fadeOut('slow');
            
            // Disable Interview 1 option
            if (formData.get('selectedInterviewValue') === 'interview1') {
                $('.composeEmailButton').find('option[value="interview1"]').prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error:", error);
            $('#loader').hide();
            toastr.error('Error sending email. Please try again later.', '', {
                positionClass: 'toast-bottom-right'
            });
        }
    });
}
    // Submit form data when form is submitted
    $('#submitEmail').submit(function(event) {
        event.preventDefault();

        var formData = new FormData($(this)[0]); // Serialize form data
        var selectedInterviewValue = $('#selectedInterviewValue').val();
        formData.append('selectedInterviewValue', selectedInterviewValue);
        var controlNumber = $('#controlNumber').val();
        formData.append('controlNumber', controlNumber);

        // Append additional fields specific to "interview1"
        if (selectedInterviewValue === 'interview1') {
            var startTime = $('#startTime').val();
            var endTime = $('#endTime').val();
            var date = $('#date').val();
            var url = $('#url').val();
            var m_id = $('#m_id').val();
            var passcode = $('#passcode').val();

            formData.append('startTime', startTime);
            formData.append('endTime', endTime);
            formData.append('date', date);
            formData.append('url', url);
            formData.append('m_id', m_id);
            formData.append('passcode', passcode);
        } else if (selectedInterviewValue === 'interview2') {
            var time = $('#int2Time').val();
            var date = $('#int2Date').val();
            var department = $('#department').val();

            formData.append('int2Time', time);
            formData.append('int2Date', date);
            formData.append('department', department);
        }
        sendFormData(formData);
    });
});
