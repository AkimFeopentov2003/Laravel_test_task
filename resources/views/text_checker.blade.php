<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Проверка текста</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white p-6 rounded shadow-lg w-full max-w-2xl">
    <h1 class="text-xl font-bold mb-4">Проверка текста</h1>

    <textarea id="textInput" class="w-full border p-2" rows="4" required></textarea>
    <button id="checkButton" class="mt-2 bg-blue-500 text-white px-4 py-2">Проверить</button>

    <div id="result" class="mt-4 p-3 border bg-gray-50 hidden">
        <h2 class="font-bold">Результат: <span id="resultText"></span></h2>
        <p id="checkedText"></p>
        <p class="text-gray-500">Язык: <strong id="language"></strong></p>
    </div>

    <h2 class="mt-6 text-lg font-bold">История проверок</h2>
    <ul id="historyList" class="mt-2">
        @foreach ($history as $item)
            <li class="border p-2 bg-gray-50">
                <p> {!! $item->text !!}</p>
                <p class="text-sm text-gray-500">Язык: <strong>{{ $item->language }}</strong></p>
            </li>
        @endforeach
    </ul>
</div>

<script>
    let incorrectSymbols = [];
    let oldText = "";
    let incorrectIndexes = [];
    function checkText(bd) {
        // console.log(document.getElementById("textInput").value);
        fetch("{{ route('text.process') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ text: document.getElementById("textInput").value,
                                    bd: bd})
        })
            .then(response => response.json())
            .then(data => {
                document.getElementById('resultText').innerHTML = data.result;
                document.getElementById('textInput').innerHTML = data.oldText;
                document.getElementById("checkedText").innerHTML = data.checkedText;
                document.getElementById("language").textContent = data.language;
                document.getElementById("result").classList.remove("hidden");
                incorrectSymbols = data.incorrectSymbols;
                incorrectIndexes =  data.incorrectIndexes;
                oldText = data.oldText;
                // console.log(incorrectSymbols);
                // console.log(incorrectIndexes);
                // console.log(data.checkedText);
                if (data.history.length !== 0){
                    const historyList = document.getElementById("historyList");
                    const newItem = document.createElement("li");
                    newItem.classList.add("border", "p-2", "bg-gray-50");
                    newItem.innerHTML = `<p>${data.checkedText}</p><p class="text-sm text-gray-500">Язык: <strong>${data.language}</strong></p>`;
                    historyList.prepend(newItem);
                }
            })
            .catch(error => console.error("Ошибка:", error));
    }
    document.getElementById('checkButton').addEventListener('click', function () {
        if(document.getElementById('textInput').value.length !== 0){
            checkText(true);
        }
    });
    document.getElementById('textInput').addEventListener('input', function () {
        if(document.getElementById('textInput').value.length === oldText.length && oldText.length !== 0){
            const currentText = document.getElementById('textInput').value;
            const containsIncorrectSymbols = incorrectSymbols.some(symbol => currentText.includes(symbol));
            const matchesOldText = Array.from(currentText).every((char, index) => {
                return incorrectIndexes.includes(index) || char === oldText[index];
            });

            if (!containsIncorrectSymbols && matchesOldText) {
                checkText(false);
            }
        }
    });
</script>
</body>
</html>
