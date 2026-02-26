# FluentAiPro Documentation

## What The App Does

FluentAiPro is a Laravel + Filament application for language reading practice.

Core workflow:

- Generate a reading passage by category, language, and difficulty (AI-generated text).
- Play an AI-generated spoken version of the passage using the selected voice.
- Record a learner attempt in the browser.
- Analyze pronunciation, intonation, and grammar with AI.
- Save multiple recordings for the same reading text/session.
- Review history of reading practices, recordings, and scores.
- Reload a previous reading session to record another attempt.

## Main User Areas

- `admin/login`: Filament admin login
- `admin/reading-practice`: Main reading practice page (generate, play AI reading, record, analyze)
- `admin/reading-practices`: History/list of saved reading sessions
- `admin/reading-categories`: Manage reading categories

## Tech Stack

- Laravel 12
- Filament 5
- Livewire 4
- NeuronAI (`neuron-core/neuron-ai`)
- Gemini API (text generation + pronunciation analysis)
- OpenAI TTS (AI reading playback)
- SQLite by default (can be changed)

## Data Model (High Level)

- `reading_categories`: Practice topics and difficulty levels
- `reading_sessions`: Generated texts and per-session metadata (language, voice, latest scores)
- `reading_recordings`: Multiple recordings per reading session, audio files, per-attempt AI feedback/scores

## AI Provider Usage (Current Flow)

- `GEMINI_MODEL`
  - Used for reading text generation.
- `GEMINI_PRONUNCIATION_MODEL`
  - Used for pronunciation analysis of recorded audio.
  - The app now uses only this configured model (no runtime fallback loop).
- `OPENAI_TTS_MODEL`
  - Used to generate AI reading playback audio from the selected voice.
- `OPENAI_MODEL`
  - Present in config, but not used by the current reading-practice flow.

## Local Setup (Recommended)

### Prerequisites

- PHP 8.4+
- Composer
- Node.js + npm
- SQLite (default) or another supported database

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Create environment file and app key

```bash
cp .env.example .env
php artisan key:generate --no-interaction
```

### 3. Configure database

Default `.env.example` uses SQLite.

For SQLite:

```bash
touch database/database.sqlite
```

Then ensure in `.env`:

```env
DB_CONNECTION=sqlite
```

If using MySQL/PostgreSQL, update the `DB_*` env vars accordingly.

### 4. Configure AI providers (required for core features)

Add these to `.env`:

```env
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-2.5-pro
GEMINI_PRONUNCIATION_MODEL=gemini-2.5-flash-lite

OPENAI_API_KEY=your_openai_api_key
OPENAI_TTS_MODEL=gpt-4o-mini-tts
```

Notes:

- `GEMINI_PRONUNCIATION_MODEL` may not exist in older `.env.example` copies, add it manually.
- `GEMINI_PRONUNCIATION_MODEL=gemini-2.5-flash-lite` is the recommended stable choice for the current pronunciation flow.
- `APP_URL` should match the URL you actually use locally (for example `http://127.0.0.1:8000`) to avoid URL mismatches in generated links/pagination.

### 5. Run migrations

```bash
php artisan migrate --no-interaction
```

### 6. Seed data

Seed a default user (from `DatabaseSeeder`) and reading categories:

```bash
php artisan db:seed --class=DatabaseSeeder --no-interaction
php artisan db:seed --class=ReadingCategorySeeder --no-interaction
```

Default seeded user:

- Email: `test@example.com`
- Password: `password`

### 7. Create storage symlink (required for audio playback)

```bash
php artisan storage:link --no-interaction
```

This is required so saved recordings and AI reading audio files on the `public` disk can be played in the browser.

### 8. Start the app

Option A (all-in-one dev script):

```bash
composer run dev
```

Option B (separate processes):

```bash
php artisan serve
npm run dev
```

If you are using built assets instead of Vite dev server:

```bash
npm run build
```

## Configuration Reference (Important Env Vars)

### Application / URL

- `APP_NAME`: UI branding and app name
- `APP_ENV`: Environment (`local`, `production`, etc.)
- `APP_DEBUG`: Error detail visibility
- `APP_URL`: Base URL for generated links and some API responses

### Database

- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

### Sessions / Cache / Queue (Laravel defaults)

- `SESSION_DRIVER`
- `CACHE_STORE`
- `QUEUE_CONNECTION`

### Files / Audio Storage

- `FILESYSTEM_DISK`

Notes:

- The reading-practice recording and TTS flow stores files on the `public` disk in current implementations.
- `php artisan storage:link` is still required even if `FILESYSTEM_DISK` is different.

### Gemini (Required for AI text + pronunciation)

- `GEMINI_API_KEY` (required)
- `GEMINI_MODEL` (text generation model)
- `GEMINI_PRONUNCIATION_MODEL` (pronunciation analysis model)

### OpenAI (Required for AI reading playback)

- `OPENAI_API_KEY` (required for "Play AI Reading")
- `OPENAI_TTS_MODEL` (TTS model for generated spoken reading)
- `OPENAI_MODEL` (configured but not used by the current reading-practice flow)

### Optional Services

- `INSPECTOR_INGESTION_KEY` (Inspector APM)
- `AWS_*` (if you switch storage or other AWS-backed services)
- `MAIL_*` (outbound email)
- `REDIS_*`, `MEMCACHED_HOST` (if using those drivers)

## How The Reading Practice Flow Works

### Filament Page (`admin/reading-practice`)

- Select category, language, and AI voice.
- Generate a reading text.
- (Optional) Click `Play AI Reading` to generate and play AI voice audio.
- Record a learner attempt in the browser.
- Click `Analyze Pronunciation`.
- The app saves the recording and stores AI feedback/scores.

### History (`admin/reading-practices`)

- Shows saved reading sessions.
- Includes recordings and scores.
- Supports loading a previous reading session back into the practice page for another attempt.

## API Endpoints (Current)

Reading practice:

- `GET /api/reading-practice/categories`
- `POST /api/reading-practice/generate-text`
- `POST /api/reading-practice/analyze-recording`
- `POST /api/reading-practice/save`

Reading session history:

- `GET /api/reading-sessions`
- `GET /api/reading-sessions/{reading_session}`

## Troubleshooting

### Audio does not play

- Run `php artisan storage:link --no-interaction`
- Confirm the file exists under `storage/app/public/`
- Hard refresh the browser

### UI changes are not visible

- Run `npm run dev` or `npm run build`
- Hard refresh the page

### Pronunciation analysis fails

- Confirm `GEMINI_API_KEY` is set
- Confirm `GEMINI_PRONUNCIATION_MODEL` is set in `.env`
- Run `php artisan config:clear --no-interaction` after changing env vars
- Check `storage/logs/laravel.log` for timeout or provider errors

### AI reading playback fails

- Confirm `OPENAI_API_KEY` is set
- Confirm `OPENAI_TTS_MODEL` is valid
- Run `php artisan config:clear --no-interaction`

## Notes For Production

- Set `APP_DEBUG=false`
- Set a production `APP_URL`
- Use a persistent database (not local SQLite unless intentionally chosen)
- Configure proper queue worker/process management if using queues
- Ensure `public/storage` symlink exists on the server
