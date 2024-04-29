const params = new URLSearchParams(window.location.search);
const id = params.get('control_number');

$.ajax({
    url: '../application/fetch-applicant.php',
    method: 'GET',
    dataType: 'json',
    data: { id: id }, // Corrected line
    success: function (data) {
        // Map the data fields to labels
        const fieldLabels = {
            'given_name': 'Given Name',
            'middle_name': 'Middle Name',
            'family_name': 'Family Name',
            'address': 'Address',
            'place_birth': 'Place of Birth',
            'birthday': 'Birthday',
            'age': 'Age',
            'gender': 'Gender',
            'contact': 'Contact',
            'landline': 'Landline',
            'secondary_email': 'Secondary Email'
        };

        Object.entries(data).forEach(([key, value]) => {
            const label = fieldLabels[key] || key.replace('_', ' ');
            $('#data-container').append(`
                <div class="col-span-1">
                    <label for="${key}" class="text-gray-700 font-rubik">${label}:</label>
                    <div class="mt-1 p-2 border rounded-md w-auto focus:outline-none focus:border-blue-300 font-poppins">${value}</div>
                </div>
            `);
        });
    },
    error: function (xhr, status, error) {
        console.error('Error fetching data:', xhr, status, error);
    }
});
