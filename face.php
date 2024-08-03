<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顔認証ログイン</title>
    <style>
        #videoElement {
            width: 640px;
            height: 480px;
            border: 1px solid black;
        }
        #captureButton, #registerButton {
            margin-top: 10px;
            padding: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h1>顔認証ログイン</h1>
    <video id="videoElement" autoplay muted playsinline></video>
    <br>
    <button id="captureButton" disabled>顔をキャプチャ</button>
    <button id="registerButton" disabled>登録</button>
    <p id="status">カメラを起動中...</p>
    <canvas id="canvasElement" style="display:none;"></canvas>

    <script>
        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('canvasElement');
        const status = document.getElementById('status');
        const captureButton = document.getElementById('captureButton');
        const registerButton = document.getElementById('registerButton');
        let capturedImage = null;

        async function startVideo() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                video.srcObject = stream;
                return new Promise((resolve) => {
                    video.onloadedmetadata = () => {
                        resolve(video);
                    };
                });
            } catch (err) {
                console.error('カメラの起動に失敗しました:', err);
                status.textContent = 'カメラの起動に失敗しました。カメラへのアクセスを許可してください。';
                throw err;
            }
        }

        async function init() {
            status.textContent = 'カメラを起動中...';
            await startVideo();
            status.textContent = 'カメラが起動しました。顔をキャプチャしてください。';
            captureButton.disabled = false;
        }

        captureButton.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            capturedImage = canvas.toDataURL('image/jpeg');
            status.textContent = '顔をキャプチャしました。登録ボタンを押してください。';
            registerButton.disabled = false;
        });

        registerButton.addEventListener('click', async () => {
            if (!capturedImage) {
                status.textContent = '顔がキャプチャされていません。';
                return;
            }

            const formData = new FormData();
            formData.append('image', capturedImage);

            try {
                const response = await fetch('register_face.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.text();
                status.textContent = result;
            } catch (error) {
                console.error('エラー:', error);
                status.textContent = '登録中にエラーが発生しました。';
            }
        });

        init().catch(console.error);
    </script>
</body>
</html>