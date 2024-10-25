<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.css">
    <script src="https://unpkg.com/cropperjs"></script>
    <title>Image Cropper</title>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f4;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
    }

    h1 {
        color: #333;
        text-align: center;
        font-size: 2rem;
        margin-bottom: 20px;
    }

    .container {
        background-color: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        width: 100%;
        text-align: center;
    }

    #image {
        display: none;
        max-width: 100%;
        margin-top: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    #image-preview {
        margin-bottom: 20px;
    }

    #output img {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        margin-bottom: 10px;
    }

    form {
        margin-bottom: 20px;
    }

    #image-input {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        border: 1px solid #ccc;
        width: 100%;
        display: block;
        margin: 0 auto;
    }

    button {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-size: 16px;
        width: 100%;
        max-width: 300px;
        display: block;
        margin: 20px auto 0;
    }

    button:hover {
        background-color: #45a049;
    }

    #download-link {
        background-color: #007BFF;
        color: white;
        padding: 12px 25px;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        font-size: 16px;
        display: block;
        margin-top: 15px;
    }

    #download-link:hover {
        background-color: #0056b3;
    }

    /* Section Headers */
    .section-title {
        font-size: 1.2rem;
        margin-top: 20px;
        color: #333;
    }

    #output {
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
    <div class="container">
        <h1>Upload and Crop Your Profile Image</h1>

        <!-- Upload and Image Section -->
        <form id="upload-form" method="POST" enctype="multipart/form-data">
            <input type="file" id="image-input" name="image" accept="image/*" required>
            <div id="image-preview">
                <img id="image" src="" alt="Profile Image">
            </div>
            <button type="submit">Upload</button>
        </form>

        <!-- Output Section -->
        <div id="output">
            <h2 class="section-title">Your Cropped Image:</h2>
            <img id="framed-image" src="" alt="Framed Image">
            <a id="download-link" href="" download>Download Cropped Image</a>
        </div>
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