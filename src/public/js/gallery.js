document.addEventListener('DOMContentLoaded', function() {
    
    // Function to toggle photo visibility
    window.toggleVisibility = function(photoId, isPublic) {
        const action = isPublic ? 'publish' : 'make private';
        
        if (!confirm(`Are you sure you want to ${action} this photo?`)) {
            return;
        }

        fetch('api/toggle_visibility.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                photo_id: photoId,
                is_public: isPublic
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to update the display
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to update photo visibility'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Connection error');
        });
    };

    // Function to delete a photo
    window.deletePhoto = function(photoId) {
        if (!confirm('Are you sure you want to delete this photo? This action cannot be undone.')) {
            return;
        }

        fetch('api/delete_photo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                photo_id: photoId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove photo from DOM
                const photoCard = document.querySelector(`[data-photo-id="${photoId}"]`);
                if (photoCard) {
                    photoCard.remove();
                }
                
                // Check if no photos left
                const remainingPhotos = document.querySelectorAll('.photo-card');
                if (remainingPhotos.length === 0) {
                    location.reload(); // Reload to show "no photos" message
                }
                
                alert('Photo deleted successfully!');
            } else {
                alert('Error: ' + (data.error || 'Failed to delete photo'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Connection error');
        });
    };

    // Add click event to photos for lightbox effect
    const photos = document.querySelectorAll('.photo-container img');
    photos.forEach(photo => {
        photo.addEventListener('click', function() {
            openLightbox(this.src);
        });
    });

    // Simple lightbox function
    function openLightbox(imageSrc) {
        const lightbox = document.createElement('div');
        lightbox.className = 'lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-content">
                <span class="close-lightbox">&times;</span>
                <img src="${imageSrc}" alt="Photo">
            </div>
        `;
        
        document.body.appendChild(lightbox);
        
        // Close lightbox events
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox || e.target.classList.contains('close-lightbox')) {
                document.body.removeChild(lightbox);
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightbox.parentNode) {
                document.body.removeChild(lightbox);
            }
        });
    }
});