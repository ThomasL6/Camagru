document.addEventListener('DOMContentLoaded', function() {
    
    // Function to show temporary messages (définie en premier)
    window.showTemporaryMessage = function(message, type = 'info') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `temp-message temp-message-${type}`;
        messageDiv.textContent = message;
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: ${type === 'success' ? '#4CAF50' : '#2196F3'};
            color: white;
            border-radius: 4px;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(messageDiv);
        
        // Fade in
        setTimeout(() => {
            messageDiv.style.opacity = '1';
        }, 100);
        
        // Fade out and remove
        setTimeout(() => {
            messageDiv.style.opacity = '0';
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    document.body.removeChild(messageDiv);
                }
            }, 300);
        }, 3000);
    };
    
    // Function to toggle like on a photo
    window.toggleLike = function(photoId, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        const likeBtn = document.querySelector(`[data-photo-id="${photoId}"] .like-btn`);
        const likesCountSpan = document.getElementById(`likes-${photoId}`);
        
        if (!likeBtn || !likesCountSpan) {
            console.error('Elements not found for photo ID:', photoId);
            return;
        }
        
        likeBtn.disabled = true;
        
        fetch('api/toggle_like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ photo_id: photoId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                likesCountSpan.textContent = data.likes_count;
                
                if (data.user_liked) {
                    likeBtn.classList.add('liked');
                    likeBtn.innerHTML = `💖 <span class="likes-count" id="likes-${photoId}">${data.likes_count}</span>`;
                } else {
                    likeBtn.classList.remove('liked');
                    likeBtn.innerHTML = `❤️ <span class="likes-count" id="likes-${photoId}">${data.likes_count}</span>`;
                }
            } else {
                console.error('Error toggling like:', data.message);
                alert(data.message || 'Erreur lors du like');
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            alert('Erreur de connexion');
        })
        .finally(() => {
            likeBtn.disabled = false;
        });
    };

    // Function to handle comment submission
    window.handleCommentSubmit = function(event, photoId) {
        console.log('handleCommentSubmit called with photoId:', photoId, 'key:', event.key);
        
        if (event.key === 'Enter') {
            event.preventDefault();
            
            const commentInput = event.target;
            const comment = commentInput.value.trim();
            
            console.log('Comment to submit:', comment);
            console.log('API URL will be:', 'api/add_comment.php');
            
            if (!comment) {
                alert('Veuillez saisir un commentaire');
                return;
            }
            
            // Désactiver l'input pendant l'envoi
            commentInput.disabled = true;
            
            console.log('Sending comment to API...');
            
            const requestData = {
                photo_id: photoId,
                comment: comment
            };
            console.log('Request data:', requestData);
            
            fetch('api/add_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text(); // Changer en text() d'abord pour voir la réponse brute
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed response data:', data);
                    if (data.success) {
                        // Vider l'input
                        commentInput.value = '';
                        
                        // Afficher une confirmation
                        window.showTemporaryMessage('Commentaire ajouté !', 'success');
                    } else {
                        console.error('Error adding comment:', data.message);
                        alert(data.message || 'Erreur lors de l\'ajout du commentaire');
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Raw response was:', text);
                    alert('Erreur de format de réponse du serveur');
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                alert('Erreur de connexion: ' + error.message);
            })
            .finally(() => {
                // Réactiver l'input
                commentInput.disabled = false;
                commentInput.focus();
            });
        }
    };

    // Enhanced lightbox function with likes and comments
    window.openLightbox = function(imageSrc, username, photoId) {
        const lightbox = document.createElement('div');
        lightbox.className = 'lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-content">
                <div class="lightbox-image">
                    <img src="${imageSrc}" alt="Photo by ${username}">
                </div>
                <div class="lightbox-sidebar">
                    <div class="lightbox-header">
                        <span class="photo-author">📸 ${username}</span>
                        <button class="close-lightbox">&times;</button>
                    </div>
                    <div class="lightbox-interactions">
                        <button class="lightbox-like-btn" onclick="toggleLightboxLike(${photoId})">
                            ❤️ <span class="lightbox-likes-count" id="lightbox-likes-${photoId}">0</span>
                        </button>
                        <div class="lightbox-comment-form">
                            <input type="text" class="lightbox-comment-input" placeholder="Ajouter un commentaire..." 
                                   onkeypress="handleLightboxComment(event, ${photoId})">
                            <button class="comment-submit-btn" onclick="submitLightboxComment(${photoId})">Envoyer</button>
                        </div>
                    </div>
                    <div class="lightbox-comments">
                        <h4 class="comments-title">Commentaires</h4>
                        <div class="comments-list" id="comments-list-${photoId}">
                            <div class="no-comments">Chargement des commentaires...</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(lightbox);
        
        // Load likes and comments
        loadLightboxData(photoId);
        
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

    // Load likes and comments for lightbox
    function loadLightboxData(photoId) {
        console.log('Loading lightbox data for photo ID:', photoId);
        
        // Load current likes count
        const likesSpan = document.getElementById(`likes-${photoId}`);
        const lightboxLikesSpan = document.getElementById(`lightbox-likes-${photoId}`);
        if (likesSpan && lightboxLikesSpan) {
            lightboxLikesSpan.textContent = likesSpan.textContent;
            console.log('Likes count loaded:', likesSpan.textContent);
        }

        // Load comments
        console.log('Fetching comments from:', `api/get_comments.php?photo_id=${photoId}`);
        fetch(`api/get_comments.php?photo_id=${photoId}`)
            .then(response => {
                console.log('Comments response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw comments response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed comments data:', data);
                    if (data.success) {
                        displayLightboxComments(photoId, data.comments);
                    } else {
                        console.error('Error loading comments:', data.message);
                        document.getElementById(`comments-list-${photoId}`).innerHTML = 
                            `<div class="no-comments">Erreur: ${data.message}</div>`;
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Raw response was:', text);
                    document.getElementById(`comments-list-${photoId}`).innerHTML = 
                        '<div class="no-comments">Erreur de format de réponse</div>';
                }
            })
            .catch(error => {
                console.error('Error loading comments:', error);
                document.getElementById(`comments-list-${photoId}`).innerHTML = 
                    '<div class="no-comments">Erreur lors du chargement des commentaires</div>';
            });
    }

    // Display comments in lightbox
    function displayLightboxComments(photoId, comments) {
        const commentsList = document.getElementById(`comments-list-${photoId}`);
        
        if (comments.length === 0) {
            commentsList.innerHTML = '<div class="no-comments">Aucun commentaire pour le moment</div>';
            return;
        }

        commentsList.innerHTML = comments.map(comment => `
            <div class="comment-item">
                <div class="comment-author">${escapeHtml(comment.username)}</div>
                <div class="comment-text">${escapeHtml(comment.comment_text)}</div>
                <div class="comment-date">${timeAgo(comment.created_at)}</div>
            </div>
        `).join('');
    }

    // Toggle like in lightbox
    window.toggleLightboxLike = function(photoId) {
        toggleLike(photoId, null); // Réutiliser la fonction existante
        
        // Mettre à jour le compteur dans la lightbox
        setTimeout(() => {
            const mainLikesSpan = document.getElementById(`likes-${photoId}`);
            const lightboxLikesSpan = document.getElementById(`lightbox-likes-${photoId}`);
            if (mainLikesSpan && lightboxLikesSpan) {
                lightboxLikesSpan.textContent = mainLikesSpan.textContent;
            }
        }, 100);
    };

    // Handle comment submission in lightbox
    window.handleLightboxComment = function(event, photoId) {
        if (event.key === 'Enter') {
            event.preventDefault();
            submitLightboxComment(photoId);
        }
    };

    // Submit comment from lightbox
    window.submitLightboxComment = function(photoId) {
        const input = document.querySelector('.lightbox-comment-input');
        const comment = input.value.trim();
        
        if (!comment) {
            alert('Veuillez saisir un commentaire');
            return;
        }
        
        input.disabled = true;
        
        fetch('api/add_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                photo_id: photoId,
                comment: comment
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                window.showTemporaryMessage('Commentaire ajouté !', 'success');
                // Recharger les commentaires
                loadLightboxData(photoId);
            } else {
                alert(data.message || 'Erreur lors de l\'ajout du commentaire');
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            alert('Erreur de connexion');
        })
        .finally(() => {
            input.disabled = false;
            input.focus();
        });
    };

    // Utility function for escaping HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Time ago function
    function timeAgo(datetime) {
        const time = Math.floor((new Date() - new Date(datetime)) / 1000);
        
        if (time < 60) return 'à l\'instant';
        if (time < 3600) return Math.floor(time/60) + 'm';
        if (time < 86400) return Math.floor(time/3600) + 'h';
        if (time < 2592000) return Math.floor(time/86400) + 'j';
        
        return new Date(datetime).toLocaleDateString('fr-FR');
    }

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
                    const likeBtn = document.querySelector(`[data-photo-id="${photoId}"] .like-btn`);
                    if (likeBtn) {
                        likeBtn.classList.add('liked');
                        const currentContent = likeBtn.innerHTML;
                        likeBtn.innerHTML = currentContent.replace('❤️', '💖');
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error checking user likes:', error);
        });
    }
});
