document.addEventListener("DOMContentLoaded", function () {
    const width = 320;    // Size of the photo ---
    let height = 0;       // This will be calculated from the input stream
    let streaming = false;

    // Get Dom Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const photo = document.getElementById('photo');
    const startbutton = document.getElementById('start-button');
	const savebutton = document.getElementById('save-button');

	function clearphoto() {
		const context = canvas.getContext('2d');
		context.fillStyle = "#AAA";
		context.fillRect(0, 0, canvas.width, canvas.height);

		const data = canvas.toDataURL('image/png');
		photo.setAttribute('src', data);
	}
	
    let photoTaken = false; // Add this variable

    function takepicture() {
        const context = canvas.getContext("2d");
        if (width && height) {
            canvas.width = width;
            canvas.height = height;
            context.drawImage(video, 0, 0, width, height);

            const data = canvas.toDataURL("image/png");
            photo.setAttribute("src", data);
            
            photoTaken = true; // Mark that a photo has been taken
            
            if (savebutton) {
                savebutton.disabled = false;
            }
        } else {
            clearphoto();
        }
    }

    function savePhoto() {
        if (!photoTaken) {
            alert('Please take a photo first!');
            return;
        }

        const imageData = photo.src;
        
        savebutton.disabled = true;
        savebutton.textContent = 'Saving...';

        fetch('api/uploadimage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                image: imageData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Photo saved successfully!');
                clearphoto();
                photoTaken = false; // Reset flag
                savebutton.disabled = true;
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Server connection error');
        })
        .finally(() => {
            savebutton.textContent = 'Save';
        });
    }

    // Event listeners
    if (savebutton) {
        savebutton.addEventListener('click', function (ev){
            savePhoto();
            ev.preventDefault();
        }, false);
    }
	

    // Init
    navigator.mediaDevices.getUserMedia({video: true, audio: false})
    .then((stream) => {
        video.srcObject = stream;
        video.play();
    })
    .catch((err) => {
        console.error(`An error occurred: ${err}`);
        alert('Unable to access camera. Please check permissions.');
    });

    // Size of event
    video.addEventListener('canplay', function(ev) {
        if (!streaming) {
            height = video.videoHeight / (video.videoWidth/width);
            
            if (isNaN(height)) {
                height = width / (4/3);
            }
            
            video.setAttribute('width', width);
            video.setAttribute('height', height);
            canvas.setAttribute('width', width);
            canvas.setAttribute('height', height);
            streaming = true;
        }
    }, false);

    //Capture
    startbutton.addEventListener('click', function (ev){
        takepicture();
        ev.preventDefault();
    }, false);
	
    // Init again
    clearphoto();
});