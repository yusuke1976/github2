<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebAuthn認証</title>
</head>
<body>
    <h1>WebAuthn認証テスト</h1>
    <button id="authenticateButton">認証を開始</button>
    <p id="result"></p>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const authenticateButton = document.getElementById('authenticateButton');
            const resultElement = document.getElementById('result');

            authenticateButton.addEventListener('click', async () => {
                try {
                    // クレデンシャル情報を取得
                    const response = await fetch('get_credential.php');
                    const data = await response.json();

                    if (!data.credentialId || !data.publicKey) {
                        resultElement.textContent = 'クレデンシャル情報が見つかりません。';
                        return;
                    }

                    // 認証オプションを作成
                    const assertionOptions = {
                        challenge: new Uint8Array(32),
                        allowCredentials: [{
                            id: Uint8Array.from(atob(data.credentialId), c => c.charCodeAt(0)),
                            type: 'public-key'
                        }],
                        userVerification: 'required'
                    };

                    // 認証を開始
                    const assertion = await navigator.credentials.get({ publicKey: assertionOptions });

                    // サーバーに検証リクエストを送信
                    const verificationResponse = await fetch('verify_assertion.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: data.credentialId,
                            rawId: btoa(String.fromCharCode.apply(null, new Uint8Array(assertion.rawId))),
                            response: {
                                clientDataJSON: btoa(String.fromCharCode.apply(null, new Uint8Array(assertion.response.clientDataJSON))),
                                authenticatorData: btoa(String.fromCharCode.apply(null, new Uint8Array(assertion.response.authenticatorData))),
                                signature: btoa(String.fromCharCode.apply(null, new Uint8Array(assertion.response.signature))),
                            },
                        }),
                    });

                    const verificationResult = await verificationResponse.json();

                    if (verificationResult.status === 'success') {
                        resultElement.textContent = '認証成功！';
                    } else {
                        resultElement.textContent = `認証失敗: ${verificationResult.message}`;
                    }
                } catch (error) {
                    console.error('認証エラー:', error);
                    resultElement.textContent = '認証に失敗しました。';
                }
            });
        });
    </script>
</body>
</html>