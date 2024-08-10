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
  renderer.setClearColor(0x000033); // 濃い青色の背景

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
  const velocities = new Float32Array(particleCount * 3);
  const colors = new Float32Array(particleCount * 3);

  for (let i = 0; i < particleCount; i++) {
    positions[i * 3] = Math.random() * 2000 - 1000;
    positions[i * 3 + 1] = Math.random() * 2000 - 1000;
    positions[i * 3 + 2] = Math.random() * 2000 - 1000;
    // 泡のサイズを大きくする
    sizes[i] = Math.random() * 20 + 10; // 以前は10 + 1だった

    // 各泡に個別の速度を設定
    velocities[i * 3] = (Math.random() - 0.5) * 0.5;
    velocities[i * 3 + 1] = Math.random() * 0.8 + 0.2;
    velocities[i * 3 + 2] = (Math.random() - 0.5) * 0.5;

    // 泡の色を青系統にする
    colors[i * 3] = 0.5 + Math.random() * 0.5; // 青
    colors[i * 3 + 1] = 0.7 + Math.random() * 0.3; // 緑（青みがかった色にするため）
    colors[i * 3 + 2] = 1; // 青
  }

  particles.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  particles.setAttribute('size', new THREE.BufferAttribute(sizes, 1));
  particles.setAttribute('color', new THREE.BufferAttribute(colors, 3));

  // 泡のマテリアルを作成
  const bubbleMaterial = new THREE.PointsMaterial({
    size: 10, // デフォルトサイズを大きくする（以前は5）
    transparent: true,
    blending: THREE.AdditiveBlending,
    depthWrite: false,
    vertexColors: true, // 頂点カラーを使用
    sizeAttenuation: true // サイズの減衰を有効にする
  });

  // 泡の粒子システムを作成
  const bubbleSystem = new THREE.Points(particles, bubbleMaterial);
  scene.add(bubbleSystem);

  // アニメーション関数
  function animate() {
    requestAnimationFrame(animate);

    const positions = bubbleSystem.geometry.attributes.position.array;

    for (let i = 0; i < particleCount; i++) {
      // 各軸方向に移動
      positions[i * 3] += velocities[i * 3];
      positions[i * 3 + 1] += velocities[i * 3 + 1];
      positions[i * 3 + 2] += velocities[i * 3 + 2];

      // 画面外に出たら反対側にワープ
      if (positions[i * 3] < -1000) positions[i * 3] = 1000;
      if (positions[i * 3] > 1000) positions[i * 3] = -1000;
      if (positions[i * 3 + 1] > 1000) positions[i * 3 + 1] = -1000;
      if (positions[i * 3 + 2] < -1000) positions[i * 3 + 2] = 1000;
      if (positions[i * 3 + 2] > 1000) positions[i * 3 + 2] = -1000;

      // ランダムな揺れを加える
      velocities[i * 3] += (Math.random() - 0.5) * 0.02;
      velocities[i * 3 + 2] += (Math.random() - 0.5) * 0.02;

      // 速度の上限を設定
      velocities[i * 3] = Math.max(Math.min(velocities[i * 3], 0.5), -0.5);
      velocities[i * 3 + 2] = Math.max(Math.min(velocities[i * 3 + 2], 0.5), -0.5);
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