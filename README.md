# PHP Audio Transcription App

This PHP application allows you to transcribe audio files using an API. It takes an audio file as input, sends it to the transcription service, and retrieves the transcribed text.

## Features

- Upload audio files in various formats.
- Transcribe audio files to text.
- Simple and easy-to-use interface.

## Prerequisites

- PHP 7.4 or higher
- Composer (for managing dependencies)
- cURL extension enabled in PHP
- An API key from your chosen transcription service provider (e.g., AssemblyAI, Google Cloud Speech-to-Text, Azure Speech)

## Installation

1. **Clone the repository:**

    ```bash
    git clone https://github.com/Codingmasster/php-audio-transcription-app.git
    cd php-audio-transcription-app
    ```

3. **Configure the API key:**

    Open the `config.php` file and add your transcription service API key:

    ```php
    <?php
    define('API_KEY', 'your-api-key-here');
    ?>
    ```

4. **Set up your web server:**

    - If you're using Apache, make sure the `public` directory is the web root.
    - If you're using Nginx, configure the server block to point to the `public` directory.

## Usage

1. **Upload Audio File:**

    - Open the application in your web browser.
    - Use the provided form to upload an audio file.

2. **Transcription:**

    - Once the file is uploaded, the app will send the file to the transcription service.
    - The transcribed text will be displayed on the webpage.
