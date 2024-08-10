// Initialize Three.js scene
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer();

renderer.setSize(window.innerWidth, window.innerHeight);
document.getElementById('scene-container').appendChild(renderer.domElement);

// Create a blue background color
scene.background = new THREE.Color(0x0077be);

// Add ambient light
const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
scene.add(ambientLight);

// Add directional light
const directionalLight = new THREE.DirectionalLight(0xffffff, 0.5);
directionalLight.position.set(0, 1, 1);
scene.add(directionalLight);

// Create bubbles
const bubbles = [];
const bubbleGeometry = new THREE.SphereGeometry(0.1, 32, 32);
const bubbleMaterial = new THREE.MeshPhongMaterial({
    color: 0xffffff,
    transparent: true,
    opacity: 0.7,
});

for (let i = 0; i < 100; i++) {
    const bubble = new THREE.Mesh(bubbleGeometry, bubbleMaterial);
    bubble.position.set(
        Math.random() * 10 - 5,
        Math.random() * 10 - 5,
        Math.random() * 10 - 5
    );
    bubble.userData = {
        speed: Math.random() * 0.02 + 0.01,
    };
    scene.add(bubble);
    bubbles.push(bubble);
}

// Position camera
camera.position.z = 5;

// Animation loop
function animate() {
    requestAnimationFrame(animate);

    // Animate bubbles
    bubbles.forEach((bubble) => {
        bubble.position.y += bubble.userData.speed;
        if (bubble.position.y > 5) {
            bubble.position.y = -5;
            bubble.position.x = Math.random() * 10 - 5;
            bubble.position.z = Math.random() * 10 - 5;
        }
    });

    renderer.render(scene, camera);
}

// Handle window resize
window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
});

// Start animation
animate();