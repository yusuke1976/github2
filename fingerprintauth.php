<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>指紋認証</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        button { margin: 10px; padding: 10px 20px; font-size: 16px; }
        #fingerprintImage { width: 100px; height: 100px; margin: 20px auto; background-color: #ddd; }
    </style>
</head>
<body>
    <h1>指紋認証</h1>
    <div id="fingerprintImage"></div>
    <button id="registerButton">ユーザー登録</button>
    <button id="authenticateButton">認証</button>
    <p id="message"></p>

    <script>
        const registerButton = document.getElementById('registerButton');
        const authenticateButton = document.getElementById('authenticateButton');
        const messageElement = document.getElementById('message');
        const fingerprintImage = document.getElementById('fingerprintImage');

        function bufferToBase64url(buffer) {
            const bytes = new Uint8Array(buffer);
            let binary = '';
            for (let i = 0; i < bytes.byteLength; i++) {
                binary += String.fromCharCode(bytes[i]);
            }
            return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        }

        async function generateChallenge() {
            const array = new Uint8Array(32);
            window.crypto.getRandomValues(array);
            return array;
        }

        registerButton.addEventListener('click', async () => {
            try {
                const challenge = await generateChallenge();
                const publicKeyCredentialCreationOptions = {
                    challenge: challenge,
                    rp: {
                        name: "指紋認証",
                        id: document.location.hostname
                    },
                    user: {
                        id: new Uint8Array(16),
                        name: "testuser@example.com",
                        displayName: "テストユーザー"
                    },
                    pubKeyCredParams: [{alg: -7, type: "public-key"}],
                    authenticatorSelection: {
                        authenticatorAttachment: "platform",
                        userVerification: "required"
                    },
                    timeout: 60000
                };

                fingerprintImage.style.backgroundColor = "yellow";
                messageElement.textContent = "指紋を登録中...";

                const credential = await navigator.credentials.create({
                    publicKey: publicKeyCredentialCreationOptions
                });

                const credentialId = bufferToBase64url(credential.rawId);
                const credentialPublicKey = bufferToBase64url(credential.response.getPublicKey());

                // サーバーに認証情報を送信
                const response = await fetch('save_credential.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        credentialId: credentialId,
                        credentialPublicKey: credentialPublicKey
                    }),
                });

                if (response.ok) {
                    messageElement.textContent = "ユーザー登録が完了しました。";
                    fingerprintImage.style.backgroundColor = "green";
                } else {
                    throw new Error("サーバーでエラーが発生しました。");
                }
            } catch (error) {
                messageElement.textContent = "登録に失敗しました: " + error;
                fingerprintImage.style.backgroundColor = "red";
            }
        });

        authenticateButton.addEventListener('click', async () => {
            try {
                const challenge = await generateChallenge();

                // サーバーから認証情報を取得
                const response = await fetch('get_credential.php');
                const credential = await response.json();

                if (!credential.credentialId) {
                    throw new Error("先にユーザー登録を行ってください。");
                }

                const publicKeyCredentialRequestOptions = {
                    challenge: challenge,
                    allowCredentials: [{
                        id: Uint8Array.from(atob(credential.credentialId), c => c.charCodeAt(0)),
                        type: 'public-key'
                    }],
                    timeout: 60000,
                    userVerification: "required"
                };

                fingerprintImage.style.backgroundColor = "yellow";
                messageElement.textContent = "指紋を確認中...";

                const assertion = await navigator.credentials.get({
                    publicKey: publicKeyCredentialRequestOptions
                });

                messageElement.textContent = "認証に成功しました！";
                fingerprintImage.style.backgroundColor = "green";
            } catch (error) {
                messageElement.textContent = "認証に失敗しました: " + error;
                fingerprintImage.style.backgroundColor = "red";
            }
        });
    </script>
</body>
</html>