<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\History;

class TextCheckController extends Controller
{
    public function index() {
        $history = History::latest()->limit(6)->get();
        return view('text_checker', compact('history'));
    }

    public function checkText(Request $request) {

        $text = $request->input('text');
        $bd = $request->input('bd');
        $lang = $this->detectLanguage($text);
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1251');
        }
        $incorrectSymbols = $this->findIncorrectSymbols($text, $lang);
        if(count($incorrectSymbols) !== 0){
            $checkedText = $this->highlightIncorrectSymbols($text, $incorrectSymbols);
        }
        else{
            $checkedText = $text;
        };
        $incorrectIndexes = $this->findIncorrectSymbolsIndexes($text, $incorrectSymbols);
        $history = [];
        if($bd){
            $history = History::create([
                'text' => $checkedText,
                'language' => $lang,
            ]);
        }
        $result = count($incorrectSymbols) === 0 ? "Проверка пройдена" : "Проверка не пройдена, пожалуйста исправте выделенные символы";

        return response()->json([
            'incorrectSymbols' => $incorrectSymbols,
            'incorrectIndexes' => $incorrectIndexes,
            'result' => $result,
            'checkedText' => $checkedText,
            'language' => $lang,
            "oldText" => $text,
            'history' => $history
        ], 200);
    }

    private function detectLanguage($text) {
        $cyrillic = preg_match_all('/[А-Яа-яЁё]/u', $text);
        $latin = preg_match_all('/[A-Za-z]/u', $text);
        return $cyrillic >= $latin ? 'ru' : 'en';
    }

    private function findIncorrectSymbols($text, $lang) {
        $incorrect = [];
        foreach (mb_str_split($text) as $char) {
            if (($lang === 'ru' && preg_match('/[A-Za-z]/u', $char)) ||
                ($lang === 'en' && preg_match('/[А-Яа-яЁё]/u', $char))) {
                $incorrect[] = $char;
            }
        }
        return $incorrect;
    }

    private function highlightIncorrectSymbols($text, $incorrectSymbols) {
        return preg_replace_callback('/[' . preg_quote(implode('', $incorrectSymbols), '/') . ']/u', function($matches) {
            return "<strong class='text-red-500'>" . $matches[0] . "</strong>";
        }, $text);
    }

    private function findIncorrectSymbolsIndexes($text, $incorrect) {
        $incorrectIndexes = [];
        foreach (mb_str_split($text) as $index => $char) {
            if (in_array($char, $incorrect)) {
                $incorrectIndexes[] = $index;
            }
        }

        return $incorrectIndexes;
    }
}
