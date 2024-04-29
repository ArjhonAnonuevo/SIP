
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src = "../css/dist/jquery.min.js"></script>
    <style>
      .file-upload-card {
        padding-top: 3rem;
        max-width: 400px;
        margin: 0 auto;
      }
      .file-upload-card-header {
        background-color: seagreen;
        color: #fff;
        padding: 0.75rem;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
      }
      .file-upload-card-header h5 {
        margin-bottom: 0;
      }
      .file-upload {
        border: 2px dashed #ddd;
        border-radius: 4px;
        padding: 1rem;
        text-align: center;
      }
      .file-upload-text {
        margin-top: 0.5rem;
        font-size: 1rem;
        color: #999;
      }
      .file-upload-input {
        display: none;
      }
      .file-upload-label {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        background-color: #4a5568;
        color: #fff;
        border-radius: 4px;
        cursor: pointer;
      }
      .file-upload-label:hover {
        background-color: #718096;
      }
      .image-container {
        margin-top: 1rem;
        text-align: center;
      }
      .image-preview-container {
        width: 100%;
        height: 200px;
        border: 2px dashed #ddd;
        border-radius: 4px;
        display: flex;
        justify-content: center;
        align-items: center;
      }
      .image-preview {
        max-width: 100%;
        max-height: 100%;
      }
      .file-names-preview {
        font-size: 0.875rem;
        color: #999;
      }
      .upload-button {
        margin-top: 1rem;
      }
    </style>
    <title>Upload Form</title>
  </head>
  <body>
<div id = "interns_nav"></div>
    <div class="container mx-auto px-4">
  <div class="card file-upload-card">
    <div class="card-header file-upload-card-header">
      <h5 class="mb-0">Certifications Upload</h5>
    </div>
    <div class="card-body">
      <form action="submit.php?username=<?php echo urlencode($username); ?>" method="post" enctype="multipart/form-data">
        <div class="image-container">
          <div class="image-preview-container">
            <img class="image-preview" id="imagePreview" src="#" alt="" />
          </div>
          <div class="file-upload">
            <div class="file-upload-text">Drag and drop your files here or click to browse</div>
            <label for="formFileMultiple" class="file-upload-label">Browse Files</label>
            <input class="file-upload-input" type="file" id="formFileMultiple" name="files[]" accept="image/*, .pdf" multiple>
          </div>
          
          <div class="file-names-preview" id="fileNamesPreview"></div>
          <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>
          <button type="submit" class="bg-green-900 text-white font-bold py-2 px-4 rounded hover:bg-green-700">
           Submit
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
    <script>
     $(document).ready(function() {
        $("#interns_nav").load("../header/interns_nav.html");
    });
      const fileInput = document.getElementById('formFileMultiple');
      const imagePreview = document.getElementById('imagePreview');
      const fileNamesPreview = document.getElementById('fileNamesPreview');
      fileInput.addEventListener('change', function(event) {
        const files = event.target.files;
        fileNamesPreview.innerHTML = '';
        imagePreview.src = '';
        for (let i = 0; i < files.length; i++) {
          const file = files[i];
          const fileName = document.createElement('span');
          fileName.textContent = file.name;
          fileNamesPreview.appendChild(fileName);
          if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
              imagePreview.src = e.target.result;
            }
            reader.readAsDataURL(file);
          }
        }
      });
    </script>
  </body>
</html>