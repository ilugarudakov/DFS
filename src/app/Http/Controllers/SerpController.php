<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SerpController extends Controller
{
    public function show()
    {
        $result = null;
        $error  = null;

        $login = env('DATAFORSEO_LOGIN', 'your_login');
        $password = env('DATAFORSEO_PASSWORD', 'your_password');
        if ($login === 'your_login' || $password === 'your_password') {
            $error = 'Будь ласка, впишіть ваші логін та пароль від DataForSEO у файл .env';
        }

        return view('rank', compact('result', 'error'));
    }

    public function search(Request $request)
    {
        $result = null;
        $error  = null;

        $login = env('DATAFORSEO_LOGIN', 'your_login');
        $password = env('DATAFORSEO_PASSWORD', 'your_password');
        if ($login === 'your_login' || $password === 'your_password') {
            $error = 'Будь ласка, впишіть ваші логін та пароль від DataForSEO у файл .env';
            $request->flash();
            return view('rank', compact('result', 'error'));
        }

        $data = $request->validate([
            'keyword'  => 'required|string|max:700',
            'site'     => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'language' => 'required|string|max:50',
        ]);

        $target = $this->normalizeDomain($data['site']);

        $task = ['keyword' => $data['keyword']];
        $cleanLocation = preg_replace('/[\s_]/', '', $data['location']);
        if (ctype_digit($cleanLocation)) {
            $task['location_code'] = (int) $cleanLocation;
        } else {
            $task['location_name'] = $data['location'];
        }
        if (preg_match('/^[a-z]{2,3}(-[A-Z]{2})?$/', $data['language'])) {
            $task['language_code'] = $data['language'];
        } else {
            $task['language_name'] = $data['language'];
        }

        try {
            $response = Http::withBasicAuth($login, $password)
                ->timeout(30)
                ->post('https://api.dataforseo.com/v3/serp/google/organic/live/regular', [ $task ]);

            if ($response->failed()) {
                $error = 'Помилка запиту: '.$response->status().' '.$response->body();
            } else {
                $json = $response->json();

                $tasks = is_array($json['tasks'] ?? null) ? $json['tasks'] : [];
                $firstTask = $tasks[0] ?? [];
                $results = is_array($firstTask['result'] ?? null) ? $firstTask['result'] : [];
                $firstResult = $results[0] ?? [];
                $items = is_array($firstResult['items'] ?? null) ? $firstResult['items'] : [];

                $rank = null;
                foreach ($items as $item) {
                    if (($item['type'] ?? '') !== 'organic') continue;
                    $domain = $this->normalizeDomain($item['domain'] ?? ($item['url'] ?? ''));
                    if ($domain && $this->domainMatches($domain, $target)) {
                        $rank = $item['rank_group'] ?? $item['rank_absolute'] ?? null;
                        break;
                    }
                }

                $checkUrl = $firstResult['check_url'] ?? null;

                $result = [
                    'rank'     => $rank,
                    'found'    => $rank !== null,
                    'checkUrl' => $checkUrl,
                ];
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        // Make old() work after POST without redirect
        $request->flash();
        return view('rank', compact('result', 'error'));
    }

    private function normalizeDomain(string $input): ?string
    {
        $host = str_contains($input, '://') ? (parse_url($input, PHP_URL_HOST) ?? $input) : $input;
        $host = preg_replace('/^www\./i', '', (string)$host);
        return rtrim($host, '/') ?: null;
    }

    private function domainMatches(string $got, string $target): bool
    {
        if ($got === $target) return true;
        return str_ends_with('.'.$got, '.'.$target) || str_ends_with('.'.$target, '.'.$got);
    }
}
