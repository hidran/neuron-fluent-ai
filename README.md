# Fluent AI 🎙️

Fluent AI is a high-performance, AI-driven language learning platform built with **Laravel 12 (PHP 8.4)** and **Next.js 15**. It provides real-time pronunciation coaching, dynamic lesson generation, and a professional practice dashboard.

## 🌟 Key Features

-   **Dynamic Lesson Engine**: Generates custom reading passages using Gemini 2.5 Pro.
-   **AI Native Speaker**: Real-time Text-to-Speech comparison for every lesson.
-   **Multimodal Analysis**: Advanced voice analysis that scores pronunciation, flow, and grammar.
-   **Practice Hub**: Comprehensive history dashboard with side-by-side audio recap.
-   **Modern UX**: Responsive, glassmorphic design built with Tailwind CSS v4.

## 🚀 Quick Start

### 1. Prerequisites
- PHP 8.4+
- Node.js 20+
- Gemini API Key
- OpenAI API Key (for TTS)

### 2. Backend Setup
```bash
composer install
cp .env.example .env # Configure your API keys
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

### 3. Frontend Setup
```bash
cd frontend
npm install
npm run dev
```

## 📖 Documentation

Detailed technical guides, configuration options, and API references are available in the [Documentation](./docs/documentation.md).

### API Highlights
- **Reading Practice**: `/api/reading-practice/*` (Generation, Analysis, Saving)
- **Practice Hub**: `/api/reading-sessions/*` (History, Session Recaps)

## 🛠️ Technology
- **Backend**: Laravel 12, Neuron AI Framework, PHP 8.4 SOLID principles.
- **Frontend**: Next.js 15, React 19, Tailwind CSS v4, Radix UI.

---
*Built with passion for language learners and powered by state-of-the-art AI.*
