// ページの読み込みを待つ
window.addEventListener("load", init);

function init() {
  // サイズを指定
  const width = window.innerWidth;
  const height = window.innerHeight;
  let rot = 0;

  // レンダラーを作成
  const renderer = new THREE.WebGLRenderer({
    canvas: document.querySelector("#myCanvas"),
  });
  renderer.setSize(width, height);

  // シーンを作成
  const scene = new THREE.Scene();

  // カメラを作成
  const camera = new THREE.PerspectiveCamera(45, width / height);

  // 球体作成
  const geometry = new THREE.SphereGeometry(300, 30, 30);
  // マテリアルを作成
  const material = new THREE.MeshStandardMaterial({
    map: new THREE.TextureLoader().load("images/earthmap1k.jpg"),
    side: THREE.DoubleSide,
  });
  // 地球メッシュを作成
  const earth = new THREE.Mesh(geometry, material);
  // 3D空間にメッシュを追加
  scene.add(earth);

  // 平行光源
  const directionalLight = new THREE.DirectionalLight(0xffffff, 1.9);
  directionalLight.position.set(1, 1, 1);
  scene.add(directionalLight);

  // ポイント光源
  const pointLight = new THREE.PointLight(0xffffff, 2, 1000);
  scene.add(pointLight);
  const pointLightHelper = new THREE.PointLightHelper(pointLight, 30);
  scene.add(pointLightHelper);

  // タイトルテキストを作成
  const loader = new THREE.FontLoader();
  loader.load('fonts/helvetiker_regular.typeface.json', function(font) {
    const textGeometry = new THREE.TextGeometry('Bookshelf Of Worries', {
      font: font,
      size: 80,
      height: 5,
      curveSegments: 12,
      bevelEnabled: true,
      bevelThickness: 10,
      bevelSize: 8,
      bevelOffset: 0,
      bevelSegments: 5
    });
    const textMaterial = new THREE.MeshPhongMaterial({ color: 0xf8f8f8 });
    const textMesh = new THREE.Mesh(textGeometry, textMaterial);
    textMesh.position.set(-400, 300, -1000);
    scene.add(textMesh);
  });

  createStarField();

  function createStarField() {
    const vertices = [];
    for (let i = 0; i < 500; i++) {
      const x = 3000 * (Math.random() - 0.5);
      const y = 3000 * (Math.random() - 0.5);
      const z = 3000 * (Math.random() - 0.5);
      vertices.push(x, y, z);
    }
    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute(
      "position",
      new THREE.Float32BufferAttribute(vertices, 3)
    );
    const material = new THREE.PointsMaterial({
      size: 8,
      color: 0xffffff,
    });
    const stars = new THREE.Points(geometry, material);
    scene.add(stars);
  }

  document.addEventListener("mousemove", (e) => {
    mouseX = e.pageX;
  });

  function tick() {
    rot += 0.5;
    const radian = (rot * Math.PI) / 180;
    camera.position.x = 1000 * Math.sin(radian);
    camera.position.z = 2000 * Math.cos(radian);
    camera.lookAt(new THREE.Vector3(0, 0, -400));
    pointLight.position.set(
      500 * Math.sin(Date.now() / 500),
      500 * Math.sin(Date.now() / 1000),
      500 * Math.cos(Date.now() / 500)
    );
    renderer.render(scene, camera);
    requestAnimationFrame(tick);
  }

  tick();
  window.addEventListener("resize", onWindowResize);

  function onWindowResize() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  }
}