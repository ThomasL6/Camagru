document.addEventListener('DOMContentLoaded', function() {
    
    // Function to toggle like on a photo
    window.toggleLike = function(photoId) {
        const likeBtn = document.querySelector(`[data-photo-id="${photoId}"]`);
        const likesCountSpan = likeBtn.querySelector('.likes-count');
        
        fetch('api/toggle_like.php', {
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
                // Update likes count
                likesCountSpan.textContent = data.likes_count;
                
                // Update button style
                if (data.liked) {
                    likeBtn.classList.add('liked');
                    likeBtn.innerHTML = `💖 <span class="likes-count">${data.likes_count}</span>`;
                } else {
                    likeBtn.classList.remove('liked');
                    likeBtn.innerHTML = `❤️ <span class="likes-count">${data.likes_count}</span>`;
                }
            } else {
                alert('Error: ' + (data.error || 'Failed to toggle like'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Connection error');
        });
    };

    // Enhanced lightbox function with user info
    window.openLightbox = function(imageSrc, username) {
        const lightbox = document.createElement('div');
        lightbox.className = 'lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-content">
                <div class="lightbox-header">
                    <span class="photo-author">📸 ${username}</span>
                    <span class="close-lightbox">&times;</span>
                </div>
                <img src="${imageSrc}" alt="Photo by ${username}">
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
        const escapeHandler = function(e) {
            if (e.key === 'Escape' && lightbox.parentNode) {
                document.body.removeChild(lightbox);
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
    };

    // Check which photos current user has liked
    checkUserLikes();
    
    function checkUserLikes() {
        const photoCards = document.querySelectorAll('.photo-card[data-photo-id]');
        const photoIds = Array.from(photoCards).map(card => card.dataset.photoId);
        
        if (photoIds.length === 0) return;
        
        fetch('api/get_user_likes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                photo_ids: photoIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.liked_photos.forEach(photoId => {
                    const likeBtn = document.querySelector(`[data-photo-id="${photoId}"]`);
                    if (likeBtn) {
                        likeBtn.classList.add('liked');
                        const likesCount = likeBtn.querySelector('.likes-count').textContent;
                        likeBtn.innerHTML = `💖 <span class="likes-count">${likesCount}</span>`;
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error checking user likes:', error);
        });
    }
});
