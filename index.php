<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.css">
    <script src="https://unpkg.com/cropperjs"></script>
    <title>Image Cropper</title>
    <style>
    #image {
        max-width: 100%;
        display: none;
    }

    #output {
        margin-top: 20px;
        display: none;
    }

    /* Style to make the cropper round */
    .cropper-view-box,
    .cropper-face {
        border-radius: 50% !important;
    }
    </style>
</head>

<body>
    <h1>Upload and Crop Your Profile Image</h1>
    <form id="upload-form" method="POST" enctype="multipart/form-data">
        <input type="file" id="image-input" name="image" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>

    <div>
        <img id="image" src="" alt="Profile Image">
    </div>

    <div id="output">
        <h2>Framed Image:</h2>
        <img id="framed-image" src="" alt="Framed Image">
        <a id="download-link" href="" download style="display:none;">Download Framed Image</a>
    </div>

    <script>
    const imageInput = document.getElementById('image-input');
    const image = document.getElementById('image');
    const output = document.getElementById('output');
    const framedImage = document.getElementById('framed-image');
    const downloadLink = document.getElementById('download-link');
    let cropper;

    imageInput.addEventListener('change', (event) => {
        const files = event.target.files;
        const done = (url) => {
            image.src = url;
            image.style.display = 'block';
        };
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = (e) => {
                done(e.target.result);
            };
            reader.readAsDataURL(files[0]);
        }
    });

    image.addEventListener('load', () => {
        cropper = new Cropper(image, {
            aspectRatio: 1, // Enforces the cropper to be square, which will appear as a circle due to CSS
            viewMode: 1,
        });
    });

    document.getElementById('upload-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const canvas = cropper.getCroppedCanvas({
            width: 300, // Specify the desired output size
            height: 300,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        canvas.toBlob((blob) => {
            const formData = new FormData();
            formData.append('cropped_image', blob);

            fetch('upload.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    framedImage.src = data.imagePath;
                    downloadLink.href = data.imagePath;
                    downloadLink.style.display = 'inline';
                    output.style.display = 'block';
                })
                .catch(error => console.error('Fetch error:', error));
        });
    });
    </script>
</body>

</html>