document.addEventListener("DOMContentLoaded", function () {
    const width = 640;    // Size of the photo (640px = résolution standard, 1280px = HD)
    let height = 0;       // This will be calculated from the input stream
    let streaming = false;

    // Get Dom Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const photo = document.getElementById('photo');
    const startbutton = document.getElementById('start-button');
	const savebutton = document.getElementById('save-button');
    const uploadInput = document.getElementById('upload');
    let selectedFilter = 'none';
    let selectedSticker = null;
    let stickerForPlacement = null;
    let placedStickers = [];
    let uploadedImage = null;

    const stickerModal = document.getElementById('sticker-modal');
    const openModalBtn = document.getElementById('open-sticker-modal');
    const closeModalBtn = document.querySelector('.close-button');
    const editCanvas = document.getElementById('edit-canvas');
    const editContext = editCanvas ? editCanvas.getContext('2d') : null;
    const validateBtn = document.getElementById('validate-stickers');
    const cancelBtn = document.getElementById('cancel-stickers');

    if (openModalBtn) {
        openModalBtn.onclick = function() {
            if (!photo.src || photo.src === 'data:,') {
                alert('Prenez une photo d\'abord !');
                return;
            }
            
            stickerModal.style.display = 'block';
            loadImageInEditor();
        }
    }

    function loadImageInEditor() {
        if (!editCanvas || !editContext) return;
        
        const img = new Image();
        img.onload = function() {
            editCanvas.width = img.width;
            editCanvas.height = img.height;
            redrawEditCanvas(img);
        };
        img.src = photo.src;
    }

    function redrawEditCanvas(baseImage) {
        if (!editContext) return;
        
        editContext.clearRect(0, 0, editCanvas.width, editCanvas.height);
        
        editContext.drawImage(baseImage, 0, 0);
        
        placedStickers.forEach(sticker => {
            if (sticker.img.complete) {
                editContext.drawImage(
                    sticker.img,
                    sticker.x - sticker.size / 2,
                    sticker.y - sticker.size / 2,
                    sticker.size,
                    sticker.size
                );
            }
        });
    }

    if (editCanvas) {
        editCanvas.addEventListener('click', function(e) {
            if (!stickerForPlacement) {
                alert('Sélectionnez d\'abord un sticker dans la palette !');
                return;
            }
            
            const rect = editCanvas.getBoundingClientRect();
            const x = (e.clientX - rect.left) * (editCanvas.width / rect.width);
            const y = (e.clientY - rect.top) * (editCanvas.height / rect.height);
            
            const stickerSize = editCanvas.width / 4;
            
            placedStickers.push({
                img: stickerForPlacement,
                x: x,
                y: y,
                size: stickerSize
            });
            
            const img = new Image();
            img.onload = function() {
                redrawEditCanvas(img);
            };
            img.src = photo.src;
        });
    }

    if (closeModalBtn) {
        closeModalBtn.onclick = function() {
            stickerModal.style.display = 'none';
            placedStickers = [];
            stickerForPlacement = null;
        }
    }

    if (cancelBtn) {
        cancelBtn.onclick = function() {
            stickerModal.style.display = 'none';
            placedStickers = [];
            stickerForPlacement = null;
        }
    }

    if (validateBtn) {
        validateBtn.onclick = function() {
            if (editCanvas) {
                photo.src = editCanvas.toDataURL('image/png');
            }
            stickerModal.style.display = 'none';
            placedStickers = [];
            stickerForPlacement = null;
        }
    }

    window.onclick = function(event) {
        if (event.target == stickerModal) {
            stickerModal.style.display = 'none';
            placedStickers = [];
            stickerForPlacement = null;
        }
    }

    const filterList = document.getElementById('filters-list');
    if(filterList){
        filterList.addEventListener('click', (e) => {
            if(e.target && e.target.tagName === 'BUTTON') {
                video.className = '';
                selectedFilter = 'none';

                if (e.target.id === 'filter-grayscale') {
                    video.classList.add('grayscale');
                    selectedFilter = 'grayscale(1)';
                } else if (e.target.id === 'filter-sepia') {
                    video.classList.add('sepia');
                    selectedFilter = 'sepia(1)';
                } else if (e.target.id === 'filter-invert') {
                    video.classList.add('invert');
                    selectedFilter = 'invert(1)';
                }
            }
        });
    }

    const filtersToggle = document.getElementById('filters-toggle');
    const filtersMenu = document.getElementById('filters-menu');
    
    if (filtersToggle && filtersMenu) {
        filtersToggle.addEventListener('click', function(e) {
            e.stopPropagation(); 
            filtersMenu.classList.toggle('open');
        });
        
        document.addEventListener('click', function(e) {
            if (!filtersToggle.contains(e.target) && !filtersMenu.contains(e.target)) {
                filtersMenu.classList.remove('open');
            }
        });
    }

    document.querySelectorAll('.filters-menu .filter-overlay-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filters-menu .filter-overlay-btn').forEach(b => b.classList.remove('active'));
            
            this.classList.add('active');
            
            video.className = '';
            selectedFilter = 'none';

            if (this.id === 'filter-grayscale') {
                video.classList.add('grayscale');
                selectedFilter = 'grayscale(1)';
            } else if (this.id === 'filter-sepia') {
                video.classList.add('sepia');
                selectedFilter = 'sepia(1)';
            } else if (this.id === 'filter-invert') {
                video.classList.add('invert');
                selectedFilter = 'invert(1)';
            }
            
            if (uploadedImage) {
                applyFilterToUploadedImage();
            }
            
            if (filtersMenu) {
                filtersMenu.classList.remove('open');
            }
        });
    });

    function applyFilterToUploadedImage() {
        if (!uploadedImage) return;
        
        const context = canvas.getContext('2d');
        canvas.width = width;
        canvas.height = height || (width * uploadedImage.height / uploadedImage.width);
        
        context.filter = selectedFilter;
        context.drawImage(uploadedImage, 0, 0, canvas.width, canvas.height);
        
        const filteredData = canvas.toDataURL('image/png');
        photo.setAttribute('src', filteredData);
    }

    const stickersList = document.getElementById('stickers-list');
    if (stickersList) {
        stickersList.addEventListener('click', (e) => {
            if (e.target.classList.contains('sticker')) {
                document.querySelectorAll('.sticker').forEach(img => {
                    img.classList.remove('selected');
                    img.style.border = '2px solid transparent';
                });
                
                e.target.classList.add('selected');
                e.target.style.border = '2px solid #007bff';
                
                stickerForPlacement = e.target;
            }
        });
    }

	function clearphoto() {
		const context = canvas.getContext('2d');
		context.fillStyle = "#AAA";
		context.fillRect(0, 0, canvas.width, canvas.height);

		const data = canvas.toDataURL('image/png');
		photo.setAttribute('src', data);
		
		// Hide visibility options and disable save button
		const visibilityOptions = document.getElementById('visibility-options');
		const saveButton = document.getElementById('save-button');
		if (visibilityOptions) visibilityOptions.style.display = 'none';
		if (saveButton) saveButton.disabled = true;
	}
	
    let photoTaken = false; // Add this variable

    function takepicture() {
        const context = canvas.getContext('2d');
        if (width && height) {
            canvas.width = width;
            canvas.height = height;

            uploadedImage = null;

            context.filter = selectedFilter;
            context.drawImage(video, 0, 0, width, height);

            const data = canvas.toDataURL('image/png');
            photo.setAttribute('src', data);
            
            const visibilityOptions = document.getElementById('visibility-options');
            const saveButton = document.getElementById('save-button');
            visibilityOptions.style.display = 'block';
            saveButton.disabled = false;
        } else {
            clearphoto();
        }
    }

    uploadInput.addEventListener('change', function(ev){
        const file = ev.target.files[0];
        if(file){
            const reader = new FileReader();

            reader.onload = function(e) {
                const tempImg = new Image();
                tempImg.onload = function() {
                    uploadedImage = tempImg;
                    
                    const imgWidth = tempImg.width;
                    const imgHeight = tempImg.height;
                    
                    canvas.width = width;
                    canvas.height = height || (width * imgHeight / imgWidth);
                    
                    const context = canvas.getContext('2d');
                    
                    context.filter = selectedFilter;
                    
                    context.drawImage(tempImg, 0, 0, canvas.width, canvas.height);
                    
                    const filteredData = canvas.toDataURL('image/png');
                    photo.setAttribute('src', filteredData);

                    const visibilityOptions = document.getElementById('visibility-options');
                    const saveButton = document.getElementById('save-button');
                    visibilityOptions.style.display = 'block';
                    saveButton.disabled = false;
                    photoTaken = true;
                };
                tempImg.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }, false);

    function savePhoto() {
        const imageData = photo.src;
        if (!imageData || imageData === 'data:,') {
            alert('Please take a photo first!');
            return;
        }

        // Get visibility setting
        const visibilityRadio = document.querySelector('input[name="visibility"]:checked');
        const isPublic = visibilityRadio ? visibilityRadio.value === 'public' : false;
        
        savebutton.disabled = true;
        savebutton.textContent = '💾 Saving...';

        fetch('api/uploadimage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                image: imageData,
                is_public: isPublic
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const visibility = isPublic ? 'public' : 'private';
                alert(`📸 Photo saved successfully as ${visibility}!`);
                clearphoto();
            } else {
                alert('❌ Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('🔌 Server connection error');
        })
        .finally(() => {
            savebutton.textContent = '💾 Save Photo';
            savebutton.disabled = true;
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
        photoTaken = true; // Set flag when photo is taken
        ev.preventDefault();
    }, false);
	
    // Init again
    clearphoto();
});