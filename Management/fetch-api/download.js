$.ajax({
    url: '../application/fetch-files.php',
    method: 'GET',
    dataType: 'json',
    data: { id }, // Ensure that 'id' is defined before this script
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
            const url = file.url;

            // You may calculate file size using additional AJAX requests if needed

            table += `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap truncate">${label}</td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">Unknown</td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded sm:px-2 md:px-4" onclick="downloadFile('${label}', '${url}')">Download</button>
                        <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-4 sm:ml-2 md:ml-4" onclick="openModal('${label}', '${url}')">View</button>
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

function downloadFile(label, url) {
    const downloadLink = document.createElement('a');
    downloadLink.href = url;
    downloadLink.download = `${label}.pdf`;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

function openModal(label, url) {
    $('#modal-container').load(`pdf_viewer.html?label=${encodeURIComponent(label)}&url=${encodeURIComponent(url)}`, function () {
        $('#myModal').removeClass('hidden');
    });
}
