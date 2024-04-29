$(document).ready(function () {
    function closeModal() {
        $('#composeEmailModal').addClass('hidden');
    }

    function openModal(interviewType, primaryEmail, controlNumber) {
        $('#selectedInterviewValue').val(interviewType);
        $('#primaryEmail').val(primaryEmail);
        $('#controlNumber').val(controlNumber);
        $('#composeEmailModal').removeClass('hidden');

        // Fetch interview template from the server
        $.ajax({
            url: '../send-emails/getInterviewType.php',
            method: 'GET',
            data: {
                interviewType: interviewType
            },
            dataType: 'json',
            success: function (response) {
                console.log('Response:', response);
                $('#message').val(response[0].template);

                // If interview type is interview1, show additional fields and hide interview2 fields
                if (interviewType === 'interview1') {
                    $('#additionalFields').show();
                    $('#additionalFields input').attr('required', 'required');
                    $('#interview2Fields').hide();
                    $('#interview2Fields input').removeAttr('required');
                    $('#int2Date').val('');
                    $('#int2Time').val('');
                    $('#department').val('');
                    $('#subject').val('Level 1 Interview');
                    $('#subjectDisplay').val('Level 1 Interview');
                }
                // If interview type is interview2, show interview2 fields and hide additional fields
                else if (interviewType === 'interview2') {
                    $('#interview2Fields').show();
                    $('#interview2Fields input').attr('required', 'required');
                    $('#additionalFields').hide();
                    $('#additionalFields input').removeAttr('required');
                    $('#date').val('');
                    $('#startTime').val('');
                    $('#endTime').val('');
                    $('#url').val('');
                    $('#m_id').val('');
                    $('#passcode').val('');
                    $('#subject').val('Level 2 Interview');
                    $('#subjectDisplay').val('Level 2 Interview');
                }
                // If interview type is interview3, hide both additional and interview2 fields
                else if (interviewType === 'interview3') {
                    $('#interview2Fields').hide();
                    $('#additionalFields').hide();
                    $('#additionalFields input').removeAttr('required');
                    $('#interview2Fields input').removeAttr('required');
                    // Reset input field values
                    $('#date').val('');
                    $('#startTime').val('');
                    $('#endTime').val('');
                    $('#url').val('');
                    $('#m_id').val('');
                    $('#passcode').val('');
                    $('#int2Date').val('');
                    $('#int2Time').val('');
                    $('#department').val('');
                    // Set subject fields
                    $('#subject').val('Orientation');
                    $('#subjectDisplay').val('Orientation');
                }

                // If interview type is rejected, hide both additional and interview2 fields
                else if (interviewType === 'rejected') {
                    $('#interview2Fields').hide();
                    $('#additionalFields').hide();
                    $('#additionalFields input').removeAttr('required');
                    $('#interview2Fields input').removeAttr('required');
                    $('#date').val('');
                    $('#startTime').val('');
                    $('#endTime').val('');
                    $('#url').val('');
                    $('#m_id').val('');
                    $('#passcode').val('');
                    $('#int2Date').val('');
                    $('#int2Time').val('');
                    $('#department').val('');
                    $('#subject').val('Rejected Application');
                    $('#subjectDisplay').val('Rejected Application');
                }
            },
            error: function (xhr, status, error) {
                console.error('Failed to fetch message from database');
                console.error('Error:', error);
            }
        });

        // Event listeners for Level 2 Interview inputs
        $('#int2Date, #int2Time, #department').on('change', function () {
            updateLevel2Message($('#int2Date').val(), $('#int2Time').val(), $('#department').val());
        });

        // Event listeners for Level 1 Interview inputs
        $('#date, #startTime, #endTime, #url, #m_id, #passcode').on('change', function () {
            updateLevel1Message($('#date').val(), $('#startTime').val(), $('#endTime').val(), $('#url').val(), $('#m_id').val(), $('#passcode').val());
        });

        // Function to format date
        function formatDate(dateString) {
            var formattedDate = new Date(dateString);
            var options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return formattedDate.toLocaleDateString('en-US', options);
        }

        // Function to update the Level 2 interview message with user input values
        function updateLevel2Message(date, time, department) {
            var formattedDate = formatDate(date);
            var message = "Good day!\n\n" +
                "We had a great time getting to know you during the Level 1 Interview. In relation, we want to invite you to the Level 2 Interview with the " + department + " on " + formattedDate + " at " + time + " AM." +
                "Kindly proceed first at the LRID, 7th Floor, 7907 Makati Avenue, Salcedo Village, Bel-air, Makati City 1209.\n\n" +
                "Please confirm your attendance on or before the scheduled date of the interview." +
                "If you have further queries or concerns, please contact us through this email or at (+632) 8818-5994.\n\n" +
                "Thank you and good luck.";

            // Update the textarea with the updated message
            $('#message').val(message);
        }

        // Function to update the Level 1 interview message with user input values
        function updateLevel1Message(date, startTime, endTime, url, m_id, passcode) {
            var formattedDate = formatDate(date);
            var message = "Good day!\n\n" +
                "We want to inform you that after reviewing your application and requirements, we are excited to move forward with the Level 1 Interview to be conducted by the SEC Internship Program (SIP) Management Team on " + formattedDate + " from " + startTime + " to " + endTime + " via Zoom Video Communications through the following details:\n\n" +
                "URL: " + url + "\n" +
                "Meeting ID: " + m_id + "\n" +
                "Passcode: " + passcode + "\n\n" +
                "Kindly confirm your attendance on or before the scheduled interview. If you have further queries or concerns, please contact us through this email or at (+632) 8818-5994.\n\n" +
                "Furthermore, kindly send us the accomplished Personal Data Sheet (PDS). Thank you, and we look forward to seeing you virtually.";

            // Update the textarea with the updated message
            $('#message').val(message);
        }

        $(`.composeEmailButton option[value='${interviewType}']`).prop('enabled', true);
    }

    function fetchTemplates() {
        $("#display-loader").load("../loaders.html");
    }

    fetchTemplates();

    $('#selectedInterviewValue').on('input', function () {
        var selectedInterview = $(this).val();
        $('#selectedInterviewText').text(selectedInterview);
    });

    $(document).on('change', '.composeEmailButton', function () {
        var selectedInterview = $(this).val();
        var primaryEmail = $(this).data('primary-email');
        var controlNumber = $(this).data('control-number');

        if (['interview1', 'interview2', 'interview3', 'rejected'].includes(selectedInterview)) {
            openModal(selectedInterview, primaryEmail, controlNumber);
        } else {
            closeModal();
        }
    });

    $('#startTime, #endTime').on('change', function () {
        var startTime = $('#startTime').val();
        var endTime = $('#endTime').val();

        if (startTime === endTime) {
            toastr.options = {
                "positionClass": "toast-top-center",
                "preventDuplicates": true,
                "timeOut": "5000"
            };
            toastr.error('Start time and end time cannot be the same. Please choose different times.');
            $(this).val('');
        }
    });

    $('#closeModal').click(closeModal);

    $('.file-input__field').change(function () {
        var files = $(this)[0].files;
        var fileNames = [];
        for (var i = 0; i < files.length; i++) {
            fileNames.push(files[i].name);
        }
        $('.file-name').text(fileNames.join(', '));
    });

    function sendFormData(formData) {
        $.ajax({
            url: '../send-emails/send-emails.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#loader').show();
            },
            success: function (response) {
                $('#loader').hide();
                toastr.success('Email sent successfully!', '', {
                    positionClass: 'toast-bottom-right'
                });
                $('#composeEmailModal').fadeOut('slow');

                if (formData.get('selectedInterviewValue') === 'interview1') {
                    $('.composeEmailButton').find('option[value="interview1"]').prop('disabled', true);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                $('#loader').hide();
                toastr.error('Error sending email. Please try again later.', '', {
                    positionClass: 'toast-bottom-right'
                });
            }
        });
    }
    $('#submitEmail').submit(function (event) {
        event.preventDefault();


        var formData = new FormData($(this)[0]);
        var selectedInterviewValue = $('#selectedInterviewValue').val();
        formData.append('selectedInterviewValue', selectedInterviewValue);
        var controlNumber = $('#controlNumber').val();
        formData.append('controlNumber', controlNumber);

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

    function isPastDate(dateString) {
        const today = new Date();
        const givenDate = new Date(dateString);
        today.setHours(0, 0, 0, 0);
        givenDate.setHours(0, 0, 0, 0);
        return givenDate.getTime() < today.getTime();
    }

    function isValidWeekday(dateString) {
        const selectedDate = new Date(dateString);
        const dayOfWeek = selectedDate.getDay();
        return dayOfWeek >= 1 && dayOfWeek <= 5;
    }

    document.getElementById('date').addEventListener('blur', function () {
        const selectedDate = this.value;
        if (selectedDate !== "") {
            if (isPastDate(selectedDate)) {
                if (typeof toastr !== 'undefined') {
                    toastr.options = {
                        "positionClass": "toast-top-center",
                        "preventDuplicates": true,
                        "timeOut": "5000"
                    };
                    toastr.warning('Please select a date in the future for this field.');
                } else {
                    console.log('Toastr library not loaded');
                }
                this.value = "";
            } else if (!isValidWeekday(selectedDate)) {
                if (typeof toastr !== 'undefined') {
                    toastr.options = {
                        "positionClass": "toast-top-center",
                        "preventDuplicates": true,
                        "timeOut": "5000"
                    };
                    toastr.warning('Please select a weekday (Monday to Friday) for this field.');
                } else {
                    console.log('Toastr library not loaded');
                }
                this.value = "";
            }
        }
    });

    document.getElementById('int2Date').addEventListener('blur', function () {
        const selectedDate = this.value;
        if (selectedDate !== "") {
            if (isPastDate(selectedDate)) {
                if (typeof toastr !== 'undefined') {
                    toastr.options = {
                        "positionClass": "toast-top-center",
                        "preventDuplicates": true,
                        "timeOut": "5000"
                    };
                    toastr.warning('Please select a date in the future for this field.');
                } else {
                    console.log('Toastr library not loaded');
                }
                this.value = "";
            } else if (!isValidWeekday(selectedDate)) {
                if (typeof toastr !== 'undefined') {
                    toastr.options = {
                        "positionClass": "toast-top-center",
                        "preventDuplicates": true,
                        "timeOut": "5000"
                    };
                    toastr.warning('Please select a weekday (Monday to Friday) for this field.');
                } else {
                    console.log('Toastr library not loaded');
                }
                this.value = "";
            }
        }
    });
    $("#startTime, #endTime").on("input", function () {
        var startTime = $("#startTime").val();
        var endTime = $("#endTime").val();
        updateMessage(startTime, endTime);
    });
    $("#startTime, #endTime").on("input", function () {
        var startTime = $("#startTime").val();
        var endTime = $("#endTime").val();
        var message = $("#int1message").val();
        message = message.replace("[startTime]", startTime);
        message = message.replace("[endTime]", endTime);
        $("#int1message").val(message);
    });
});