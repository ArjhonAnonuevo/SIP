
$.ajax({
    url: '../application/fetch-files.php',
    method: 'GET',
    dataType: 'json',
    data: { id },
    success: function (filesData) {
        let table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
        `;

        filesData.forEach(file => {
            const label = file.file_name;
            const base64Value = file.value; // Assuming 'value' is the Base64 string

            // Calculate file size in KB
            const fileSizeInBytes = Math.ceil((base64Value.length / 4) * 3);
            const fileSizeInKB = (fileSizeInBytes / 1024).toFixed(2);

            table += `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap truncate">${label}</td>
                    <td class="px-6 py-4 text-right whitespace-nowrap truncate">${fileSizeInKB} KB</td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded sm:px-2 md:px-4" data-action="download" data-label="${label}" data-base64="${base64Value}">Download</button>
                        <a href="view.php?file=${label}&id=${id}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-4 sm:ml-2 md:ml-4">View</a>
                    </td>
                </tr>
            `;
        });

        table += `
                </tbody>
            </table>
        `;

        $('#file-container').append(table);
    },
    error: function (xhr, status, error) {
        console.error('Error fetching files:', xhr, status, error);
    }
});


// Event delegation for the download button
$(document).on('click', '[data-action="download"]', function() {
    const label = $(this).data('label');
    const base64Value = $(this).data('base64');
    downloadFile(label, base64Value);
});

function downloadFile(label, base64Value) {
    if (base64Value) {
        // Use AJAX to send a POST request
        $.ajax({
            url: '../application/download.php',
            method: 'POST',
            data: {
                file: label,
                id: id, // Ensure 'id' is accessible in this scope
                content: base64Value
            },
            success: function (response) {
                // Trigger download using the received base64-encoded content
                const downloadLink = document.createElement('a');
                downloadLink.href = `data:application/pdf;base64,${response}`;
                downloadLink.download = `${label}.pdf`;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            },
            error: function (xhr, status, error) {
                console.error('Error downloading file:', xhr, status, error);
            }
        });
    } else {
        console.error(`Error: File content for ${label} is undefined.`);
    }
}
