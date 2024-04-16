$(document).ready(function () {
    $('#adminNav').load('../header/admin_navs.html');
    function closeModal() {
        $('#composeEmailModal').addClass('hidden');
    }

    function openModal(interviewType, primaryEmail, controlNumber) {
        $('#selectedInterviewValue').val(interviewType);
        $('#primaryEmail').val(primaryEmail);
        $('#controlNumber').val(controlNumber);
        $('#composeEmailModal').removeClass('hidden');

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
        } else if (interviewType === 'interview2') {
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
        } else if (interviewType === 'interview3') {
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

            $('#subject').val('Orientation');
            $('#subjectDisplay').val('Orientation');
        } else if (interviewType === 'rejected') {
            $('#interview2Fields').hide();
            $('#additionalFields').hide();
            $('#additionalFields input').removeAttr('required');
            $('#interview2Fields input').removeAttr('required');

            $('#date').val('');
            $('#startTime').val('');
            $('#endTime').val('');
            $('#url').val('');
            $('#m_id').val('');
            $('#passcode').val(''); y
            $('#int2Date').val('');
            $('#int2Time').val('');
            $('#department').val('');

            $('#subject').val('Rejected Application');
            $('#subjectDisplay').val('Rejected Application');

        }
        $(`.composeEmailButton option[value='${interviewType}']`).prop('disabled', true);
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
                    toastr.warning('Please select a date in the future for this field.');
                } else {
                    console.log('Toastr library not loaded');
                }
                this.value = "";
            } else if (!isValidWeekday(selectedDate)) {
                if (typeof toastr !== 'undefined') {
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
                    toastr.warning('Please select a date in the future for this field.');
                } else {
                    console.log('Toastr library not loaded');
                }
                this.value = "";
            } else if (!isValidWeekday(selectedDate)) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Please select a weekday (Monday to Friday) for this field.');
                } else {
                    console.log('Toastr library not loaded');
                }
                this.value = "";
            }
        }
    });
});
