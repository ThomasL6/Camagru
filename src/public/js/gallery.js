document.addEventListener('DOMContentLoaded', function() {
    
    let currentOffset = 20;
    let isLoading = false;
    let hasMorePhotos = true;
    
    const galleryGrid = document.querySelector('.gallery-grid');
    const loadingSpinner = document.getElementById('loading-spinner');
    const noMorePhotos = document.getElementById('no-more-photos');
    
    function isNearBottom() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        
        return (scrollTop + windowHeight) >= (documentHeight - 500);
    }
    
    function loadMorePhotos() {
        if (isLoading || !hasMorePhotos) return;
        
        isLoading = true;
        loadingSpinner.style.display = 'block';
        
        fetch(`gallery.php?ajax=load_more&offset=${currentOffset}&limit=20`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.photos.length > 0) {
                    data.photos.forEach(photo => {
                        const photoCard = createPhotoCard(photo);
                        galleryGrid.appendChild(photoCard);
                    });
                    
                    currentOffset += data.photos.length;
                    hasMorePhotos = data.hasMore;
                    
                    if (!data.hasMore) {
                        noMorePhotos.style.display = 'block';
                    }
                } else {
                    hasMorePhotos = false;
                    noMorePhotos.style.display = 'block';
                }
                
                isLoading = false;
                loadingSpinner.style.display = 'none';
            })
            .catch(error => {
                console.error('Error loading more photos:', error);
                isLoading = false;
                loadingSpinner.style.display = 'none';
            });
    }
    
    function createPhotoCard(photo) {
        const photoCard = document.createElement('div');
        photoCard.className = 'photo-card';
        photoCard.setAttribute('data-photo-id', photo.id);
        
        const photoDate = new Date(photo.created_at);
        const formattedDate = photoDate.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
        
        photoCard.innerHTML = `
            <div class="photo-container">
                <img src="uploads/images/${escapeHtml(photo.image_path)}" 
                    alt="Photo taken on ${formattedDate}"
                    loading="lazy">
            </div>
            <div class="photo-info">
                <span class="photo-date">${formattedDate}</span>
                <div class="photo-visibility">
                    ${photo.is_public ? 
                        '<span class="visibility-badge public">🌍 Public</span>' : 
                        '<span class="visibility-badge private">🔒 Private</span>'}
                </div>
                <div class="photo-actions">
                    ${photo.is_public ?
                        `<button class="btn btn-sm btn-warning" onclick="toggleVisibility(${photo.id}, 0)">🔒 Make Private</button>` :
                        `<button class="btn btn-sm btn-success" onclick="toggleVisibility(${photo.id}, 1)">🌍 Publish</button>`
                    }
                    <button class="btn btn-sm btn-danger" onclick="deletePhoto(${photo.id})">🗑️ Delete</button>
                </div>
            </div>
        `;
        
        return photoCard;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    window.addEventListener('scroll', function() {
        if (isNearBottom()) {
            loadMorePhotos();
        }
    });
    
    
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