async function submitPrompt() {
    const prompt = document.getElementById("inputText").value;
    const outputElement = document.getElementById("outputText");
    try {
        const URL = "";
        const KEY = "";
        const systemPrompt = `
            あなたは悩み解決のための本を優しく紹介するガイドです。
            以下の制約条件を厳密に守ってロールプレイを行ってください。

            制約条件:
            ・ユーザーの悩みや困りごとに対して解決のヒントとなる本を紹介します。
            ・やさしくて安心できる雰囲気を持ち、親しみやすい言葉で対応します。
            ・丁寧で理解しやすい説明を心がけ、ユーザーがリラックスできるよう努めます。
            ・ユーザーの要望に応じて質問し、適切な本を提案します。
            ・専門的な知識を持ちつつ、ユーザーに寄り添う態度を大切にします。
            ・ユーザーの悩みをよく聞き、具体的なアドバイスと関連する本を紹介します。
            ・新入社員が仕事の量に悩む場合、『ゲーデルの不完全性定理』などの間接的なヒントとなる本を紹介します。
            ・ビジネス関連の悩みには、数学書や漫画など他分野の本を紹介します。
            ・少なくとも3冊は本を紹介します。
            ・プログラミングスクールの学生が卒業制作に悩んでいる場合、『ブルーピリオド』などを紹介します。
            ・インターネットで似た悩みを調査し、その悩みを解決した本を紹介します。
            ・紹介する本は、箇条書きで表示して。
            `
        
        const response = await fetch(URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${KEY}`,
            },
            body: JSON.stringify({
                model: "gpt-3.5-turbo",
                messages: [{ role: "system", content: systemPrompt },
                           { role: "user", content: prompt }],
            }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        outputElement.textContent = data.choices[0].message.content;
        outputElement.style.display = 'block';  // 回答を表示

        document.getElementById("inputText").value = "";

    } catch (e) {
        outputElement.textContent = "Error: " + e.message;
    }
}

const btn = document.getElementById("btn");
const formText = document.getElementById("formText");
const resetBtn = document.getElementById("resetBtn");
const bookItemRow = document.querySelector("#bookItem .row");
const outputText = document.getElementById("outputText");

btn.addEventListener('click', async() => {
    const textValue = formText.value;
    if (!textValue) return;

    const res = await fetch(`https://www.googleapis.com/books/v1/volumes?q=${textValue}`);
    const data = await res.json();

    bookItemRow.innerHTML = '';

    for(let i = 0; i < data.items.length; i++){
        try {
            const bookImg = data.items[i].volumeInfo.imageLinks?.thumbnail || 'path/to/default/book-image.jpg';
            const bookTitle = data.items[i].volumeInfo.title;
            const bookAuthor = data.items[i].volumeInfo.authors ? data.items[i].volumeInfo.authors.join(', ') : '著者不明';
            const bookContent = data.items[i].volumeInfo.description || '説明なし';
            const bookLink = data.items[i].volumeInfo.infoLink;
            
            const bookCard = `
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow book-card">
                        <div class="card-img-top-wrapper">
                            <img src="${bookImg}" class="card-img-top" alt="${bookTitle}">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${bookTitle}</h5>
                            <p class="card-text text-muted mb-2">${bookAuthor}</p>
                            <p class="card-text book-description">${bookContent.substring(0, 150)}${bookContent.length > 150 ? '...' : ''}</p>
                            <a href="${bookLink}" target="_blank" class="btn btn-outline-primary mt-auto">詳細を見る</a>
                        </div>
                    </div>
                </div>
            `;
            
            bookItemRow.insertAdjacentHTML('beforeend', bookCard);
        } catch(e) {
            console.error('Error creating book card:', e);
            continue;
        }
    }

    formText.value = '';
});

resetBtn.addEventListener('click', () => {
    formText.value = '';
    bookItemRow.innerHTML = '';
    outputText.textContent = '';
    outputText.style.display = 'none';  // 回答欄を非表示に
    document.getElementById("inputText").value = "";
    console.log('Reset button clicked. All content cleared.');
});

// スタイルを動的に追加
const style = document.createElement('style');
style.textContent = `
    #bookItem {
        max-width: 1100px;
        margin: 0 auto;
    }
    .book-card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        border: none;
        border-radius: 10px;
    }
    .book-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important;
    }
    .card-img-top-wrapper {
        height: 200px;
        overflow: hidden;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    .card-img-top {
        object-fit: cover;
        height: 100%;
        width: 100%;
        transition: transform 0.3s ease-in-out;
    }
    .book-card:hover .card-img-top {
        transform: scale(1.05);
    }
    .book-description {
        font-size: 0.9rem;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }
    .card-body {
        background-color: #f8f9fa;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    .btn-outline-primary {
        border-radius: 20px;
        transition: all 0.3s ease-in-out;
    }
    .btn-outline-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0,123,255,0.2);
    }
`;
document.head.appendChild(style);