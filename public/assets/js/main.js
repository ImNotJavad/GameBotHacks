// Clear Form Button Functionality
        const clearFormButton = document.getElementById('clearFormButton');
        if (clearFormButton) {
            clearFormButton.addEventListener('click', function() {
                const form = document.getElementById('gameSubmissionForm');
                if (form) {
                    // Reset the form to its initial state (good for checkboxes, etc.)
                    form.reset(); 
                    
                    // Explicitly clear the specific text fields
                    const fullGameUrlInput = document.getElementById('full_game_url');
                    if (fullGameUrlInput) {
                        fullGameUrlInput.value = '';
                    }
                    
                    const scoreInput = document.getElementById('score');
                    if (scoreInput) {
                        scoreInput.value = '';
                    }

                    // Also, ensure the "Show Full Server Response Log" checkbox is unchecked
                    const showLogCheckbox = document.getElementById('show_log');
                    if (showLogCheckbox) {
                        showLogCheckbox.checked = false;
                    }

                    // Hide any existing alert box
                    const alertBox = document.querySelector('.alert');
                    if(alertBox) {
                        alertBox.style.display = 'none';
                    }
                }
            });
        }

        // Enhanced Mouse Trail Effect
        const canvas = document.getElementById('mouseTrailCanvas');
        const ctx = canvas.getContext('2d');
        let canvasWidth = canvas.width = window.innerWidth; // Renamed to avoid conflict
        let canvasHeight = canvas.height = window.innerHeight; // Renamed
        window.addEventListener('resize', () => {
            canvasWidth = canvas.width = window.innerWidth;
            canvasHeight = canvas.height = window.innerHeight;
        });

        let particles = [];
        const particleSettings = {
            count: 20, 
            radius: 2.0, // Slightly smaller for a finer trail
            decay: 0.94, 
            speed: 2.5,
            trailLength: 2, // Number of particles to spawn per significant mouse move
            baseColor: [130, 202, 255] // RGB for Sky Blue from --primary-color
        };

        function Particle(x, y) {
            this.x = x;
            this.y = y;
            this.radius = particleSettings.radius * (Math.random() * 0.4 + 0.8); // Variation
            this.vx = (Math.random() - 0.5) * particleSettings.speed * 0.7;
            this.vy = (Math.random() - 0.5) * particleSettings.speed * 0.7;
            this.alpha = 0.7 + Math.random() * 0.3; // Start with some variation in alpha
            this.life = 1;
        }

        Particle.prototype.update = function() {
            this.x += this.vx;
            this.y += this.vy;
            this.vx *= 0.98; 
            this.vy *= 0.98;
            this.life -= (1 - particleSettings.decay);
            this.alpha = Math.max(0, this.life * 0.9); // Alpha fades with life
            this.radius = Math.max(0, this.radius * (0.93 + this.life * 0.05));
        };

        Particle.prototype.draw = function() {
            if (this.alpha <= 0.01 || this.radius <= 0.1) return;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2, false);
            
            const gradient = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.radius);
            gradient.addColorStop(0, `rgba(${particleSettings.baseColor[0]}, ${particleSettings.baseColor[1]}, ${particleSettings.baseColor[2]}, ${this.alpha * 0.7})`);
            gradient.addColorStop(0.7, `rgba(${particleSettings.baseColor[0]}, ${particleSettings.baseColor[1]}, ${particleSettings.baseColor[2]}, ${this.alpha * 0.2})`);
            gradient.addColorStop(1, `rgba(${particleSettings.baseColor[0]}, ${particleSettings.baseColor[1]}, ${particleSettings.baseColor[2]}, 0)`);
            
            ctx.fillStyle = gradient;
            ctx.fill();
        };
        
        let lastMouseX = -1000, lastMouseY = -1000;
        const minMoveDistance = 4; // Min pixels mouse must move to generate new particles
        let mouseBuffer = []; // Buffer mouse positions

        document.addEventListener('mousemove', (e) => {
            mouseBuffer.push({x: e.clientX, y: e.clientY});
            if(mouseBuffer.length > 3) { // Keep a short history
                mouseBuffer.shift();
            }
        }, { passive: true }); // Use passive listener for better scroll performance

        function spawnParticles() {
            if(mouseBuffer.length > 0) {
                // Use the latest mouse position from buffer for responsiveness
                const pos = mouseBuffer[mouseBuffer.length - 1]; 
                const dx = pos.x - lastMouseX;
                const dy = pos.y - lastMouseY;

                if (Math.sqrt(dx*dx + dy*dy) > minMoveDistance) {
                    lastMouseX = pos.x;
                    lastMouseY = pos.y;
                    for (let i = 0; i < particleSettings.trailLength; i++) { 
                         if (particles.length < particleSettings.count * 8) { 
                            particles.push(new Particle(pos.x, pos.y));
                        }
                    }
                }
            }
        }

        let lastFrameTime = 0;
        function animateParticles(currentTime) {
            if(!lastFrameTime) lastFrameTime = currentTime;
            // let deltaTime = (currentTime - lastFrameTime) / 1000; // Delta time in seconds
            // lastFrameTime = currentTime;

            ctx.clearRect(0, 0, canvasWidth, canvasHeight);
            spawnParticles(); 

            for (let i = particles.length - 1; i >= 0; i--) { // Iterate backwards for safe removal
                particles[i].update();
                particles[i].draw();
                if (particles[i].life <= 0.01) {
                    particles.splice(i, 1);
                }
            }
            requestAnimationFrame(animateParticles);
        }
        requestAnimationFrame(animateParticles); // Start the animation loop

        document.addEventListener('DOMContentLoaded', function() {
    // Clear Form Button Functionality
    const clearFormButton = document.getElementById('clearFormButton');
    if (clearFormButton) {
        clearFormButton.addEventListener('click', function() {
            const form = document.getElementById('gameSubmissionForm');
            if (form) {
                form.reset(); 
                const fullGameUrlInput = document.getElementById('full_game_url');
                if (fullGameUrlInput) fullGameUrlInput.value = '';
                const scoreInput = document.getElementById('score');
                if (scoreInput) scoreInput.value = '';
                const showLogCheckbox = document.getElementById('show_log');
                if (showLogCheckbox) showLogCheckbox.checked = false;
                const alertBox = document.querySelector('.alert');
                if(alertBox) alertBox.style.display = 'none';
            }
        });
    }

    // Enhanced Mouse Trail Effect
    const canvas = document.getElementById('mouseTrailCanvas');
    if (canvas) { // Check if canvas exists
        const ctx = canvas.getContext('2d');
        let canvasWidth = canvas.width = window.innerWidth;
        let canvasHeight = canvas.height = window.innerHeight;
        window.addEventListener('resize', () => {
            canvasWidth = canvas.width = window.innerWidth;
            canvasHeight = canvas.height = window.innerHeight;
        });

        // ... (rest of the particle JS code from your previous index.php) ...
        let particles = [];
        const particleSettings = { /* ... */ };
        function Particle(x, y) { /* ... */ }
        Particle.prototype.update = function() { /* ... */ };
        Particle.prototype.draw = function() { /* ... */ };
        let lastMouseX = -1000, lastMouseY = -1000;
        const minMoveDistance = 4;
        let mouseBuffer = [];
        document.addEventListener('mousemove', (e) => {
            mouseBuffer.push({x: e.clientX, y: e.clientY});
            if(mouseBuffer.length > 3) { mouseBuffer.shift(); }
        }, { passive: true });
        function spawnParticles() { /* ... */ }
        let lastFrameTime = 0;
        function animateParticles(currentTime) { /* ... */ requestAnimationFrame(animateParticles); }
        requestAnimationFrame(animateParticles);
    }
});