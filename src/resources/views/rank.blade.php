<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Google Organic Rank (DataForSEO)</title>
    <style>
        body {
            font-family: ui-sans-serif, system-ui, Arial, sans-serif;
            max-width: 780px;
            margin: 32px auto;
            padding: 0 16px;
        }

        label {
            display: block;
            margin: 12px 0 4px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        button {
            margin-top: 16px;
            padding: 10px 16px;
            border-radius: 8px;
            border: 0;
            background: #111827;
            color: #fff;
            cursor: pointer;
        }

        .card {
            margin-top: 20px;
            padding: 16px;
            border: 1px solid #eee;
            border-radius: 12px;
            background: #fafafa;
        }

        .muted {
            color: #6b7280;
            font-size: 14px;
        }

        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<h1>Позиція сайту в Google (DataForSEO)</h1>

<form method="POST" action="{{ route('search') }}">
    @csrf
    <label>Пошукове слово</label>
    <input name="keyword" value="{{ old('keyword') }}" required/>

    <label>Сайт (домен або URL)</label>
    <input name="site" value="{{ old('site') }}" required/>

    <label>Локація</label>
    <input name="location" value="{{ old('location') }}" required/>

    <label>Мова</label>
    <input name="language" value="{{ old('language') }}" required/>

    <button type="submit">Пошук</button>
</form>

@if ($errors->any())
    <div class="card">
        <strong>Помилка валідації:</strong>
        <ul>
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@isset($error)
    <div class="card"><strong>Помилка:</strong> {{ $error }}</div>
@endisset

@isset($result)
    <div class="card">
        @if ($result['found'])
            <h3>Знайдено! Ранг (organic): <code>{{ $result['rank'] }}</code></h3>
        @else
            <h3>Сайт не знайдено в органічній видачі.</h3>
            <div class="muted">Перевірте пошукове слово, локацію та мову.</div>
        @endif

        @if (!empty($result['checkUrl']))
            <p class="muted">Перевірити SERP у браузері:
                <a href="{{ $result['checkUrl'] }}" target="_blank" rel="noreferrer">відкрити</a>
            </p>
        @endif
    </div>
@endisset

</body>
</html>
