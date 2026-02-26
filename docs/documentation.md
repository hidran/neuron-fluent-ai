# Fluent AI - Technical Documentation

Fluent AI is an advanced language learning application that leverages specialized AI agents to help users master their pronunciation, intonation, and grammar.

## Core Features

- **Dynamic Text Generation**: AI-powered creation of reading passages based on custom topics, proficiency levels, and target languages.
- **Native Speaker Comparison**: Text-to-Speech (TTS) synthesis that allows users to hear the "perfect" pronunciation before attempting a reading.
- **Voice Analysis**: Real-time recording and analysis of user speech using multimodal AI models (Gemini).
- **Interactive Feedback**: Professional scoring (0-100%) for Pronunciation, Intonation, and Grammar with actionable coaching tips.
- **Practice Hub**: A comprehensive history dashboard to track evolution and revisit past sessions with side-by-side audio playback.

## Tech Stack

### Backend (Laravel 12)
- **Engine**: PHP 8.4 (Strict Types, Readonly Classes, Property Promotion)
- **AI Orchestration**: Neuron AI Framework
- **Models**: Gemini 2.5 Flash (Analysis), Gemini 2.5 Pro (Generation)
- **Storage**: Local/S3 for audio recordings and AI samples
- **Database**: SQLite/MySQL

### Frontend (Next.js 15)
- **Framework**: React 19 (App Router)
- **Styling**: Tailwind CSS v4 (CSS-first approach)
- **UI Components**: Radix UI Primitives
- **Icons**: Lucide React
- **Notifications**: Custom Toast System

---

## Configuration

### Backend Setup

1. **Environment Variables**:
   Copy `.env.example` to `.env` and configure the following:
   ```env
   # AI Providers
   GEMINI_API_KEY=your_gemini_key
   OPENAI_API_KEY=your_openai_key (for TTS)
   
   # Model Selection
   GEMINI_MODEL=gemini-2.5-pro
   GEMINI_PRONUNCIATION_MODEL=gemini-2.5-flash
   ```

2. **Installation**:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   php artisan storage:link
   ```

3. **Running the Server**:
   ```bash
   php artisan serve
   ```

### Frontend Setup

1. **Installation**:
   ```bash
   cd frontend
   npm install
   ```

2. **Configuration**:
   Ensure `frontend/next.config.ts` has the correct absolute path for the Turbopack root if running in a monorepo environment.

3. **Running the App**:
   ```bash
   npm run dev
   ```

---

## API Reference

### Reading Practice Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/reading-practice/categories` | Returns all available practice categories. |
| `POST` | `/api/reading-practice/generate-text` | Generates text and optional AI voice sample. |
| `POST` | `/api/reading-practice/analyze-recording` | Analyzes a user recording against the target text. |
| `POST` | `/api/reading-practice/save` | Persists the session, recording, and AI feedback. |

### Session Management Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/reading-sessions` | Returns a paginated list of all practice sessions. |
| `GET` | `/api/reading-sessions/{id}` | Returns detailed data for a specific session including recordings. |

---

## Architecture Principles (SOLID)

- **TextGeneratorInterface / AudioAnalyzerInterface**: Decouples the domain logic from specific AI providers.
- **ReadingPracticeService**: Orchestrates the workflow across different specialized services.
- **SessionSaveData**: Immutable PHP 8.4 DTO for safe data transport.
- **CSS-First Tailwind**: Removes configuration bloat and ensures theme consistency.
