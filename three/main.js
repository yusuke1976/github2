// ページの読み込みを待つ
window.addEventListener("load", init);

function init() {
  // サイズを指定
  const width = window.innerWidth;
  const height = window.innerHeight;

  // レンダラーを作成
  const renderer = new THREE.WebGLRenderer({
    canvas: document.querySelector("#myCanvas"),
  });
  renderer.setSize(width, height);
  renderer.setClearColor(0x0080ff); // 水色の背景

  // シーンを作成
  const scene = new THREE.Scene();

  // カメラを作成
  const camera = new THREE.PerspectiveCamera(45, width / height, 1, 10000);
  camera.position.set(0, 0, 1000);

  // 泡の粒子を作成
  const particles = new THREE.BufferGeometry();
  const particleCount = 1000;

  const positions = new Float32Array(particleCount * 3);
  const sizes = new Float32Array(particleCount);

  for (let i = 0; i < particleCount; i++) {
    positions[i * 3] = Math.random() * 2000 - 1000;
    positions[i * 3 + 1] = Math.random() * 2000 - 1000;
    positions[i * 3 + 2] = Math.random() * 2000 - 1000;
    sizes[i] = Math.random() * 10 + 1;
  }

  particles.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  particles.setAttribute('size', new THREE.BufferAttribute(sizes, 1));

  // 泡のマテリアルを作成
  const bubbleMaterial = new THREE.PointsMaterial({
    color: 0xffffff,
    size: 5,
    transparent: true,
    blending: THREE.AdditiveBlending,
    depthWrite: false,
  });

  // 泡の粒子システムを作成
  const bubbleSystem = new THREE.Points(particles, bubbleMaterial);
  scene.add(bubbleSystem);

  // アニメーション関数
  function animate() {
    requestAnimationFrame(animate);

    const positions = bubbleSystem.geometry.attributes.position.array;

    for (let i = 0; i < particleCount; i++) {
      // Y軸方向に移動（上昇）
      positions[i * 3 + 1] += 1;

      // 画面上部に達したら下部にリセット
      if (positions[i * 3 + 1] > 1000) {
        positions[i * 3 + 1] = -1000;
      }

      // X軸とZ軸方向にランダムな揺れを加える
      positions[i * 3] += (Math.random() - 0.5) * 2;
      positions[i * 3 + 2] += (Math.random() - 0.5) * 2;
    }

    bubbleSystem.geometry.attributes.position.needsUpdate = true;

    renderer.render(scene, camera);
  }

  animate();

  // ウィンドウのリサイズ処理
  window.addEventListener('resize', onWindowResize);

  function onWindowResize() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  }
}